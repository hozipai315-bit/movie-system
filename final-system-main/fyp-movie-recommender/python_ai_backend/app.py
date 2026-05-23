from flask import Flask, request, jsonify
from flask.json.provider import DefaultJSONProvider
from flask_cors import CORS
import sys
import os
import numpy as np

# Custom JSON Provider to handle numpy types
class CustomJSONProvider(DefaultJSONProvider):
    def default(self, obj):
        if isinstance(obj, np.integer):
            return int(obj)
        if isinstance(obj, np.floating):
            return float(obj)
        if isinstance(obj, np.ndarray):
            return obj.tolist()
        return super().default(obj)

# Add services to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from services.text_emotion import detect_mood_level2
from services.voice_emotion import detect_voice_mood
from services.facial_emotion import detect_face_mood, preload_facial_models
from services.mood_to_genre import get_genre_for_mood

app = Flask(__name__)
app.json = CustomJSONProvider(app)
# Enable CORS to allow requests from PHP
CORS(app)

@app.route('/')
def home():
    print("Health check endpoint hit")
    return jsonify({"status": "active", "service": "MoodAI Python Backend"})

@app.route('/genre', methods=['GET'])
def get_genre():
    mood = request.args.get('mood', 'Default')
    genre_id = get_genre_for_mood(mood)
    return jsonify({"mood": mood, "genre_id": genre_id})

@app.route('/detect/text', methods=['POST'])
def analyze_text():
    print("Received POST request: /detect/text")
    try:
        data = request.json
        if not data or 'text' not in data:
            return jsonify({"error": "No text provided"}), 400

        text = data['text']
        print(f"Analyzing text: {text[:30]}...")

        polarity, subjectivity, mood, scores = detect_mood_level2(text)
        mood = mood.capitalize()
        print(f"Result: {mood}")

        return jsonify({
            "mood": mood,
            "confidence": 1.0,
            "details": scores,
            "analysis": {
                "polarity": polarity,
                "subjectivity": subjectivity
            }
        })

    except Exception as e:
        print(f"Error in /detect/text: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/detect/voice', methods=['POST'])
def analyze_voice():
    print("Received POST request: /detect/voice")
    try:
        if 'file' not in request.files:
            return jsonify({"error": "No audio file provided"}), 400

        audio_file = request.files['file']
        temp_path = os.path.join("temp_audio_" + audio_file.filename)
        audio_file.save(temp_path)

        mood, text, scores = detect_voice_mood(temp_path)
        mood = mood.capitalize()
        print(f"Voice Result: {mood}")

        if os.path.exists(temp_path):
            os.remove(temp_path)

        return jsonify({
            "mood": mood,
            "confidence": 0.85,
            "transcription": text,
            "details": scores
        })

    except Exception as e:
        print(f"Error in /detect/voice: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/detect/face', methods=['POST'])
def analyze_face():
    print("Received POST request: /detect/face")
    try:
        if 'file' not in request.files:
            return jsonify({"error": "No face image provided"}), 400

        face_file = request.files['file']
        file_content = face_file.read()
        import base64
        base64_img = base64.b64encode(file_content).decode('utf-8')

        mood, scores = detect_face_mood(base64_img)
        mood = mood.capitalize()
        print(f"Face Result: {mood}")

        return jsonify({
            "mood": mood,
            "confidence": 0.90,
            "details": scores
        })

    except Exception as e:
        print(f"Error in /detect/face: {str(e)}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # Pre-load models before starting the server
    preload_facial_models()
    app.run(host='0.0.0.0', port=5000, debug=True)
