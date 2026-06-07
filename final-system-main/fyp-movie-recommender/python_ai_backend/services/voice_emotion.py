try:
    from faster_whisper import WhisperModel
except ImportError:
    WhisperModel = None

from pydub import AudioSegment
import os
import sys
import librosa
import numpy as np

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from services.text_emotion import detect_mood_level2

model = None
if WhisperModel:
    try:
        model = WhisperModel("tiny", device="cpu", compute_type="int8")
        print("Whisper model loaded successfully")
    except Exception as e:
        print(f"Error loading Whisper model: {e}")

def detect_voice_mood(audio_file_path):
    wav_path = audio_file_path + ".wav"

    try:
        print(f"[DEBUG] Audio file: {audio_file_path}")
        print(f"[DEBUG] File exists: {os.path.exists(audio_file_path)}")
        print(f"[DEBUG] File size: {os.path.getsize(audio_file_path) if os.path.exists(audio_file_path) else 'NOT FOUND'} bytes")

        # Step 1: Convert to WAV — normalize to 16kHz mono for Whisper
        audio = AudioSegment.from_file(audio_file_path)
        audio = audio.set_frame_rate(16000).set_channels(1)
        print(f"[DEBUG] Audio duration: {len(audio)}ms, channels: {audio.channels}, frame_rate: {audio.frame_rate}")
        audio.export(wav_path, format="wav")
        print(f"[DEBUG] WAV exported to: {wav_path}")

        # Step 2: Transcription via Faster-Whisper
        transcribed_text = ""
        text_scores = {}

        if model:
            print("[DEBUG] Starting Whisper transcription...")
            segments, info = model.transcribe(
                wav_path,
                beam_size=5,
                language="en",
                vad_filter=True
            )
            transcribed_text = " ".join([segment.text for segment in segments]).strip()
            print(f"[DEBUG] Transcribed text: '{transcribed_text}'")
            print(f"[DEBUG] Detected language: {info.language}, probability: {info.language_probability:.2f}")
        else:
            transcribed_text = "[Model Error]"
            print("[DEBUG] Whisper model is None")

        if not transcribed_text:
            print("[DEBUG] Transcription empty")
            transcribed_text = "[Unintelligible]"
            text_scores = {"Happy": 0.0, "Sad": 0.0, "Angry": 0.0, "Excited": 0.0}
        else:
            _, _, text_mood, text_scores = detect_mood_level2(transcribed_text)
            print(f"[DEBUG] Text mood: {text_mood}, scores: {text_scores}")

        # Step 3: Audio Feature Analysis via Librosa
        y, sr_librosa = librosa.load(wav_path, sr=16000, mono=True)
        print(f"[DEBUG] Librosa loaded: {len(y)} samples, sr={sr_librosa}, max_amplitude={np.max(np.abs(y)):.4f}")

        rms = librosa.feature.rms(y=y)[0]
        energy_mean = np.mean(rms)
        energy_std  = np.std(rms)
        peak_energy = np.percentile(rms, 90)
        median_energy = np.percentile(rms, 50)

        spectral_centroid = librosa.feature.spectral_centroid(y=y, sr=sr_librosa)
        centroid_mean = np.mean(spectral_centroid)

        print(f"[DEBUG] Energy — mean: {energy_mean:.6f}, std: {energy_std:.6f}, peak: {peak_energy:.6f}, median: {median_energy:.6f}")
        print(f"[DEBUG] Spectral centroid: {centroid_mean:.2f}")

        tone_mood = "Neutral"
        tone_intensity = 0.0

        if peak_energy > (energy_mean + 0.5 * energy_std):
            tone_mood = "Excited" if centroid_mean > 2000 else "Angry"
            tone_intensity = min(1.0, (peak_energy / (energy_mean + 1e-6)) * 0.4)
        elif median_energy < (energy_mean - 0.3 * energy_std):
            tone_mood = "Sad"
            tone_intensity = min(1.0, (peak_energy / (energy_mean + 1e-6)) * 0.4)

        print(f"[DEBUG] Tone mood: {tone_mood}, intensity: {tone_intensity:.4f}")

        # Step 4: Weighted Hybrid (70% text, 30% tone)
        mood_votes = {"Happy": 0.0, "Sad": 0.0, "Angry": 0.0, "Excited": 0.0, "Neutral": 0.0}
        total_text_energy = sum(max(0, s) for s in text_scores.values())

        if total_text_energy >= 0.3:
            for m, s in text_scores.items():
                if m in mood_votes:
                    mood_votes[m] += (max(0, s) / total_text_energy) * 0.70
        else:
            mood_votes["Neutral"] += 0.70

        if tone_mood != "Neutral":
            mood_votes[tone_mood] += tone_intensity * 0.30

        print(f"[DEBUG] Mood votes: {mood_votes}")

        final_mood = "Neutral" if all(v == 0.0 for v in mood_votes.values()) else max(mood_votes, key=mood_votes.get)
        print(f"[DEBUG] Final mood: {final_mood}")

        return final_mood, transcribed_text, mood_votes

    except Exception as e:
        print(f"[ERROR] in detect_voice_mood: {e}")
        import traceback
        traceback.print_exc()
        return "Neutral", f"Error processing audio: {str(e)}", {}

    finally:
        if os.path.exists(wav_path):
            try:
                os.remove(wav_path)
            except:
                pass
