from deepface import DeepFace
import cv2
import numpy as np
import base64

def preload_facial_models():
    """
    Pre-loads DeepFace models to improve response time on first request.
    Uses a dummy analysis to ensure all necessary models (emotion and detector) are cached.
    """
    print("Pre-loading Facial Emotion models...")
    try:
        # Create a tiny blank image
        blank_img = np.zeros((224, 224, 3), dtype=np.uint8)
        # Call analyze with enforce_detection=False to load models without needing a real face
        DeepFace.analyze(blank_img, actions=['emotion'], enforce_detection=False)
        print("Facial Emotion models loaded successfully.")
    except Exception as e:
        print(f"Warning: Could not pre-load facial models: {e}")

def detect_face_mood(image_data):
    """
    Detects emotion from a Base64 encoded image using DeepFace.
    Returns the dominant mood and a dictionary of scores.
    """
    try:
        # 1. Decode Base64 string to image
        if ',' in image_data:
            image_data = image_data.split(',')[1] # Remove header if present

        img_bytes = base64.b64decode(image_data)
        nparr = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            raise ValueError("Could not decode image")

        # 2. Analyze using DeepFace
        # We use 'ssd' backend for better accuracy than 'opencv' default.
        # We enforce detection to ensure we actually have a face.
        try:
            results = DeepFace.analyze(
                img_path=img,
                actions=['emotion'],
                enforce_detection=True,
                detector_backend='ssd'
            )
        except ValueError:
             # Fallback: maybe face wasn't detected cleanly or backend issue
             return "Neutral", {"error": "No face detected"}

        # DeepFace returns a list of result objects (one for each face found)
        # We want to pick the largest face (the user), effectively resizing logic.

        selected_result = None
        max_area = 0

        for res in results:
            # Each result has a 'region' dict: {'x':, 'y':, 'w':, 'h':}
            region = res['region']
            area = region['w'] * region['h']

            if area > max_area:
                max_area = area
                selected_result = res

        if not selected_result:
            # Should not happen if results is not empty, but safety check
            selected_result = results[0]

        dominant_emotion = selected_result['dominant_emotion']
        emotion_scores = selected_result['emotion'] # Dict like {'angry': 0.1, ...}

        # 3. Simplify/Map to our Standard Moods if necessary
        # Standard: Happy, Sad, Angry, Excited, Neutral
        # DeepFace: angry, disgust, fear, happy, sad, surprise, neutral

        # Mapping logic
        mood_map = {
            "happy": "Happy",
            "sad": "Sad",
            "angry": "Angry",
            "disgust": "Angry", # Map disgust to Angry
            "fear": "Neutral",  # Map fear to Neutral
            "surprise": "Excited",
            "neutral": "Neutral"
        }

        final_mood = mood_map.get(dominant_emotion, "Neutral")

        return final_mood, emotion_scores

    except Exception as e:
        print(f"Error in detect_face_mood: {e}")
        return "neutral", {}
