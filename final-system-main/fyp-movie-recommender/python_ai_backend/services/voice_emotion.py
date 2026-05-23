import speech_recognition as sr
from pydub import AudioSegment
import os
import sys
import librosa
import numpy as np

# Add parent directory to path to allow importing sibling services
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from services.text_emotion import detect_mood_level2

def detect_voice_mood(audio_file_path):
    """
    Detects mood from an audio file using a Hybrid Approach:
    1. Text Content (via SpeechRecognition) -> What was said.
    2. Audio Features (via Librosa) -> How it was said (Tone/Pitch/Energy).

    Returns: Final Mood, Transcribed Text, Score Details
    """
    recognizer = sr.Recognizer()

    # Define a temporary wav path
    wav_path = audio_file_path + ".wav"

    try:
        # 1. Convert to WAV (SpeechRecognition needs WAV)
        audio = AudioSegment.from_file(audio_file_path)
        audio.export(wav_path, format="wav")

        # --- A. Text Analysis ---
        transcribed_text = ""
        text_mood = "Neutral"
        text_scores = {}

        try:
            with sr.AudioFile(wav_path) as source:
                 audio_data = recognizer.record(source)
                 transcribed_text = recognizer.recognize_google(audio_data)

            # Analyze Text Mood
            polarity, subjectivity, text_mood, text_scores = detect_mood_level2(transcribed_text)

        except sr.UnknownValueError:
            transcribed_text = "[Unintelligible]"
        except sr.RequestError as e:
            transcribed_text = f"[Speech API Error: {e}]"

        # --- B. Audio Feature Analysis (Tone) ---
        y, sr_librosa = librosa.load(wav_path)

        # Energy
        rms = librosa.feature.rms(y=y)
        energy_mean = np.mean(rms)

        # Pitch/Brightness
        spectral_centroid = librosa.feature.spectral_centroid(y=y, sr=sr_librosa)
        centroid_mean = np.mean(spectral_centroid)

        tone_mood = "Neutral"
        tone_intensity = 0.0

        if energy_mean > 0.025:
            if centroid_mean > 2000:
                tone_mood = "Excited"
            else:
                tone_mood = "Angry"
            tone_intensity = min(1.0, energy_mean * 15)
        elif energy_mean < 0.020:
            tone_mood = "Sad"
            tone_intensity = min(1.0, (0.020 - energy_mean) * 60)
        else:
            tone_mood = "Neutral"

        # --- C. Hybrid Decision Logic ---
        final_mood = text_mood

        # Tone Override Logic (Text analysis is primary, Tone only overrides if text is Neutral)
        if text_mood == "Neutral" and tone_mood != "Neutral":
            final_mood = tone_mood

        # Combined Scores
        combined_scores = text_scores.copy()
        if tone_mood in combined_scores:
            combined_scores[tone_mood] += tone_intensity
        else:
            combined_scores[tone_mood] = tone_intensity

        return final_mood, transcribed_text, combined_scores

    except Exception as e:
        print(f"Error in detect_voice_mood: {e}")
        return "Neutral", f"Error processing audio: {str(e)}", {}

    finally:
        if os.path.exists(wav_path):
            try:
                os.remove(wav_path)
            except:
                pass
