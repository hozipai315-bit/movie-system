import wave
import struct
import math
import os
import sys
import numpy as np
from unittest.mock import MagicMock, patch

# Ensure we can import from the services directory
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))

def generate_test_wav(path, frequency=440, duration=3, amplitude=0.3):
    sample_rate = 16000
    samples = int(sample_rate * duration)
    with wave.open(path, 'w') as f:
        f.setnchannels(1)
        f.setsampwidth(2)
        f.setframerate(sample_rate)
        for i in range(samples):
            value = int(amplitude * 32767 * math.sin(2 * math.pi * frequency * i / sample_rate))
            f.writeframes(struct.pack('<h', value))

def run_voice_tests():
    from services.voice_emotion import detect_voice_mood

    test_dir = "test_audio_tmp"
    if not os.path.exists(test_dir):
        os.makedirs(test_dir)

    print("Running Voice Emotion System Tests...")
    print("-" * 50)

    # Test 1: Silent/Quiet WAV -> should return Sad or Neutral (tone-wise)
    quiet_path = os.path.join(test_dir, "quiet.wav")
    generate_test_wav(quiet_path, frequency=100, duration=2, amplitude=0.01)

    # Test 2: High energy loud WAV -> should return Angry or Excited (tone-wise)
    loud_path = os.path.join(test_dir, "loud.wav")
    generate_test_wav(loud_path, frequency=800, duration=2, amplitude=0.8)

    # Mock WhisperModel's transcribe method to control text output
    test_cases = [
        {
            "name": "Quiet Audio (Neutral/Sad Tone) + No Text",
            "path": quiet_path,
            "transcription": "",
            "expected_mood": "Neutral"
        },
        {
            "name": "Loud Audio (Excited/Angry Tone) + Happy Text",
            "path": loud_path,
            "transcription": "I am very happy today",
            "expected_mood": "Happy"
        },
        {
            "name": "Loud Audio (Excited/Angry Tone) + No Text",
            "path": loud_path,
            "transcription": "",
            "expected_mood": "Neutral" # instruction: text < 0.3 energy -> 0.7 Neutral. 0.7 wins over 0.3*intensity.
        }
    ]

    for case in test_cases:
        mock_segment = MagicMock()
        mock_segment.text = case['transcription']

        with patch('services.voice_emotion.model.transcribe') as mock_transcribe:
            mock_transcribe.return_value = ([mock_segment], None)

            final_mood, transcribed_text, mood_votes = detect_voice_mood(case['path'])

            status = "PASS" if final_mood == case['expected_mood'] else f"FAIL (Expected {case['expected_mood']})"
            print(f"Test: {case['name']}")
            print(f"  Result: {final_mood} (Votes: {mood_votes})")
            print(f"  Status: {status}")
            print("-" * 50)

    # Cleanup
    for f in os.listdir(test_dir):
        os.remove(os.path.join(test_dir, f))
    os.rmdir(test_dir)

if __name__ == "__main__":
    # Mock WhisperModel during import to prevent loading real model during unit tests
    with patch('faster_whisper.WhisperModel'):
        run_voice_tests()
