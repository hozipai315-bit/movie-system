from faster_whisper import WhisperModel
from pydub import AudioSegment
import os
import sys
import librosa
import numpy as np

# Add parent directory to path to allow importing sibling services
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from services.text_emotion import detect_mood_level2

# Initialize Faster-Whisper Model (Tiny on CPU with INT8)
# We initialize it once at module level for efficiency
try:
    # Use tiny model, CPU, and int8 as requested
    model = WhisperModel("tiny", device="cpu", compute_type="int8")
except Exception as e:
    print(f"Error loading Whisper model: {e}")
    model = None

def detect_voice_mood(audio_file_path):
    """
    Detects mood from an audio file using a Hybrid Approach:
    1. Text Content (via Faster-Whisper) -> What was said.
    2. Audio Features (via Librosa) -> How it was said (Tone/Pitch/Energy).

    Returns: Final Mood, Transcribed Text, Score Details
    """
    # Define a temporary wav path
    wav_path = audio_file_path + ".wav"

    try:
        # 1. Convert to WAV (Ensure compatibility for analysis)
        audio = AudioSegment.from_file(audio_file_path)
        audio.export(wav_path, format="wav")

        # --- A. Text Analysis (Transcription) ---
        transcribed_text = ""
        text_scores = {}

        if model:
            # Transcribe with faster-whisper
            segments, info = model.transcribe(wav_path, beam_size=5)
            transcribed_text = " ".join([segment.text for segment in segments]).strip()
        else:
            transcribed_text = "[Model Error]"

        if not transcribed_text:
            transcribed_text = "[Unintelligible]"
            text_scores = {"Happy": 0.0, "Sad": 0.0, "Angry": 0.0, "Excited": 0.0}
        else:
            # Analyze Text Mood using existing text_emotion service
            _, _, _, text_scores = detect_mood_level2(transcribed_text)

        # --- B. Audio Feature Analysis (Tone) ---
        y, sr_librosa = librosa.load(wav_path)

        # RMS Energy calculation
        rms = librosa.feature.rms(y=y)[0]
        energy_mean = np.mean(rms)
        energy_std = np.std(rms)
        peak_energy = np.percentile(rms, 90)
        median_energy = np.percentile(rms, 50)

        # Pitch/Brightness (Spectral Centroid)
        spectral_centroid = librosa.feature.spectral_centroid(y=y, sr=sr_librosa)
        centroid_mean = np.mean(spectral_centroid)

        tone_mood = "Neutral"
        tone_intensity = 0.0

        # Relative threshold logic
        if peak_energy > (energy_mean + 0.5 * energy_std):
            # High energy -> Angry or Excited
            if centroid_mean > 2000:
                tone_mood = "Excited"
            else:
                tone_mood = "Angry"
            tone_intensity = min(1.0, (peak_energy / (energy_mean + 1e-6)) * 0.4)
        elif median_energy < (energy_mean - 0.3 * energy_std):
            # Consistently quiet -> Sad
            tone_mood = "Sad"
            tone_intensity = min(1.0, (peak_energy / (energy_mean + 1e-6)) * 0.4)
        else:
            tone_mood = "Neutral"

        # --- C. Hybrid Decision Logic (70% Text, 30% Tone) ---
        mood_votes = {"Happy": 0.0, "Sad": 0.0, "Angry": 0.0, "Excited": 0.0, "Neutral": 0.0}

        total_text_energy = sum(max(0, s) for s in text_scores.values())

        # Text contribution (70%)
        if total_text_energy >= 0.3:
            for m, s in text_scores.items():
                if m in mood_votes:
                    mood_votes[m] += (max(0, s) / total_text_energy) * 0.70
        else:
            # If text analysis returns no strong scores, assign 70% to Neutral
            mood_votes["Neutral"] += 0.70

        # Tone contribution (30%)
        if tone_mood != "Neutral":
            mood_votes[tone_mood] += tone_intensity * 0.30

        # Final Mood Selection
        if all(v == 0.0 for v in mood_votes.values()):
            final_mood = "Neutral"
        else:
            final_mood = max(mood_votes, key=mood_votes.get)

        return final_mood, transcribed_text, mood_votes

    except Exception as e:
        print(f"Error in detect_voice_mood: {e}")
        return "Neutral", f"Error processing audio: {str(e)}", {}

    finally:
        if os.path.exists(wav_path):
            try:
                os.remove(wav_path)
            except:
                pass
