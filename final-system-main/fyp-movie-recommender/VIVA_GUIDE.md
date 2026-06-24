# Final Year Project Viva Preparation Guide: MoodAI Recommendation System

This guide is designed to help you master the technical and architectural details of your project, **MoodAI**. It covers everything from high-level overviews to specific code implementations, ensuring you can answer examiner questions with confidence and precision.

---

## 1. Project Overview

### What this project does in simple terms
MoodAI is an intelligent movie recommendation system that suggests films based on the user's **current emotional state** rather than just their past watch history. It uses three different AI modalities—**Facial Recognition, Voice Analysis, and Natural Language Processing (Text)**—to detect how a user is feeling and then maps that mood to specific movie genres (e.g., "Sad" maps to "Drama").

### The Complete Tech Stack
*   **Frontend:** HTML5, CSS3, JavaScript (ES6+), **Bootstrap 5.3.3** (UI Framework), **AOS** (Animate On Scroll).
*   **Backend (Web):** **PHP 8.x** (Handles user sessions, database interactions, and API bridging).
*   **Backend (AI):** **Python 3.10+** with **Flask** (A micro-framework used to host the AI detection services).
*   **Database:** **MySQL** (Relational database for users, mood history, and movie caching).
*   **AI Libraries:**
    *   **DeepFace:** For facial emotion detection (using the SSD backend).
    *   **Faster-Whisper:** For high-speed, offline voice-to-text transcription.
    *   **Librosa:** For audio feature extraction (pitch, energy, tone).
    *   **TextBlob & NLTK:** For sentiment analysis and natural language processing.
*   **External APIs:** **The Movie Database (TMDB) API v3**.

### Overall Architecture
The system follows a **Modular Micro-service Architecture**:
1.  **Client (Browser):** Captures user input (camera for face, microphone for voice, or keyboard for text).
2.  **PHP API Bridge (`api/detect_mood_api.php`):** Receives the raw data and forwards it to the Python AI service using **cURL**.
3.  **Python AI Service (`app.py`):** Processes the data using machine learning models and returns a detected mood (e.g., "Happy").
4.  **Recommendation Engine (`recommendation.php`):** Maps the mood to a Genre ID and fetches movies from the **TMDB API**.
5.  **Database:** Stores user profiles, historical mood data, and a "fallback" cache of movies.

---

## 2. Database Structure

### Database Connection
*   **File Path:** `final-system-main/fyp-movie-recommender/php_backend/database/connection.php`
*   **Logic:** Uses **PDO (PHP Data Objects)** for secure, prepared SQL statements to prevent SQL Injection.
*   **Code Snippet:**
    ```php
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    ```

### Full Schema (`mood_recommender_db`)

1.  **`users`**: Stores authentication data.
    *   `user_id` (INT, PK), `username`, `email`, `password` (hashed).
2.  **`user_mood_history`**: Tracks every detection for analytics.
    *   `id`, `user_id` (FK), `mood`, `input_type` (text/voice/face), `detected_at`.
3.  **`mood_definitions`**: The 5 core system moods.
    *   `id`, `mood_name` (Happy, Sad, Angry, Excited, Neutral).
4.  **`mood_genre_mapping`**: Links moods to TMDB genres.
    *   `id`, `mood_id` (FK), `genre_id` (TMDB ID), `genre_name`, `weight` (priority).
5.  **`user_favorites`**: Movies saved by the user.
    *   `id`, `user_id` (FK), `tmdb_movie_id`, `movie_title`, `movie_poster`, `vote_average`, `overview`, `mood_tag`.
6.  **`cached_movies`**: The fallback system.
    *   `tmdb_id`, `title`, `overview`, `poster_path`, `vote_average`, `genre_id`, `original_language`.
7.  **`system_settings`**: Key-value pairs for global config.
    *   `setting_key` (e.g., `tmdb_api_key`), `setting_value`.

---

## 3. API Integration Points

### 1. TMDB API (External)
*   **Function:** `fetch_movies_from_tmdb()` in `includes/tmdb_helper.php`.
*   **Endpoint:** `https://api.themoviedb.org/3/discover/movie`.
*   **Data Sent:** `api_key`, `with_genres` (ID), `sort_by`, `language`.
*   **Data Received:** A JSON object containing an array of movie results (title, overview, poster path, rating).

### 2. PHP to Python Bridge (Internal)
*   **Location:** `api/detect_mood_api.php`.
*   **Mechanism:** **cURL** POST requests.
*   **Logic:** PHP sends the user's audio file, image (base64), or text to `http://127.0.0.1:5000/detect/[type]`.

### 3. API Credentials
*   **Storage:** The **TMDB API Key** is stored in the `system_settings` database table and loaded dynamically in `includes/config.php`. This allows you to update the key via the Admin Panel without touching the code.

---

## 4. AI Modules Explanation

### A. Face Detection (`facial_emotion.py`)
*   **Library:** `DeepFace`.
*   **Backend:** Uses the **SSD (Single Shot MultiBox Detector)** for faster and more accurate face localization.
*   **Logic:** It analyzes the geometry of facial features (eyes, mouth, eyebrows). DeepFace outputs probabilities for 7 emotions; our system maps these to our 5 core moods (e.g., "Disgust" maps to "Angry").

### B. Text Detection (`text_emotion.py`)
*   **Library:** `TextBlob`.
*   **Logic:** Uses a **Rule-Based & Statistical approach**.
    *   It checks for **Keywords** (e.g., "miserable" = Sad).
    *   It handles **Negations** (e.g., "not happy" correctly lowers the Happy score).
    *   It handles **Intensifiers** (e.g., "extremely happy" doubles the score).
    *   It calculates **Polarity** (-1 to 1) and **Subjectivity**.

### C. Voice Detection (`voice_emotion.py`) — *The Hybrid System*
*   **Library:** `Faster-Whisper` (Transcriber) + `Librosa` (Acoustics).
*   **Logic (70% Text / 30% Tone):**
    1.  **Transcription:** It converts speech to text using Whisper and runs the Text AI module (70% weight).
    2.  **Acoustics:** It analyzes the **RMS Energy** (loudness) and **Spectral Centroid** (brightness/pitch) of the audio.
    3.  **Result:** If you say "I am fine" in a very loud, high-pitched voice, the acoustic analysis might detect "Excited" even if the words are "Neutral." The final mood is a weighted vote between the two.

---

## 5. Code Flow Walkthrough

1.  **User Opens Dashboard:** User selects "Voice Mood."
2.  **Detection (`mood_voice.php`):** JavaScript records audio, sends it to `api/detect_mood_api.php`.
3.  **AI Processing (`app.py`):** PHP uses cURL to send audio to the Python `/detect/voice` endpoint. Python returns `{"mood": "Happy"}`.
4.  **Database Logging:** PHP saves the detection in the `user_mood_history` table.
5.  **Analyzing Page:** User is redirected to `analyzing.php` for visual feedback, then to `recommendation.php`.
6.  **Mapping (`mood_mapper.php`):** The system calls `get_genre_id_for_mood('Happy')`. It checks:
    *   First: The Admin DB mapping.
    *   Second: The Python API.
    *   Third: The `mood_genre_fallback.json` file.
7.  **Movie Fetching (`tmdb_helper.php`):** The system calls the TMDB API with the Genre ID (e.g., 35 for Comedy).
8.  **Display:** Movies are rendered in a Bootstrap grid.
9.  **Saving:** User clicks "Save to Favorites," triggered by `api/save_favorite.php`.

### The Intelligent Fallback Mechanism
If the TMDB API is down (no internet) or slow:
*   `recommendation.php` detects the failure.
*   It queries the `cached_movies` table for movies previously saved with that Genre ID.
*   The UI displays "Source: **Local Intelligence**" instead of "Live Cloud."

---

## 6. Admin Panel Details

*   **Dashboard:** Shows real-time charts (using **Chart.js**) of the most common user moods and input methods.
*   **Movie Data Engine (`admin/movies.php`):** This is where you link Moods to Genres. You can set a **Weight (Strength %)**. If "Happy" is linked to "Comedy" at 100%, it will always show Comedies.
*   **System Settings:** Allows you to change the `Site Name` and `TMDB API Key` globally without editing PHP files.

---

## 7. Quick Start: How to Launch

1.  **Automatic:** Run `run_project.bat`. It will start the Python server and check if XAMPP is active.
2.  **Manual (Python):** Open a terminal in `/python_ai_backend` and run `python app.py`.
3.  **Manual (Web):** Ensure Apache and MySQL are running in XAMPP. Access via `http://localhost/fyp-movie-recommender/php_backend/`.

---

## 8. Likely Viva Questions & Answers

**Q1: Why did you use two backends (PHP and Python)?**
*   **A:** Separation of concerns. PHP is excellent for web management, session handling, and database operations. Python is the industry standard for AI/ML due to libraries like DeepFace and Whisper. We connect them via a RESTful API (Flask).

**Q2: How do you handle the high computational load of AI?**
*   **A:** We use **Faster-Whisper** with `int8` quantization to run on the CPU without needing a powerful GPU. We also **pre-load** models when `app.py` starts so the first user doesn't experience a delay.

**Q3: How secure is your database?**
*   **A:** I use **PDO with Prepared Statements** for all queries, which prevents SQL Injection. User passwords are never stored in plain text.

**Q4: What happens if two people are in the camera frame?**
*   **A:** In `facial_emotion.py`, I wrote logic to calculate the **area** (width * height) of all detected faces and select the **largest one**, assuming that is the primary user.

**Q5: Why did you include a "Local Cache" for movies?**
*   **A:** Reliability. If the external TMDB API fails or the internet is disconnected, the system remains functional by serving movies it previously stored in the `cached_movies` table.

**Q6: What is the "Spectral Centroid" in your voice analysis?**
*   **A:** It’s a measure of the "brightness" of a sound. High spectral centroids indicate brighter, higher-pitched sounds (associated with Excitement or Anger), while lower values indicate darker, bassier sounds (associated with Sadness).

**Q7: How did you map moods to genres?**
*   **A:** I created a flexible **Mood-to-Genre Mapping Table**. This allows an administrator to change the logic (e.g., mapping "Angry" to "Action" or "Documentary") through the UI without changing a single line of code.

**Q8: What is your project's "Core Innovation"?**
*   **A:** Traditional recommenders use **historical data** (what you liked yesterday). This system uses **real-time biometric data** (how you feel right now) through a **triple-modality hybrid system**, making the suggestions truly adaptive.

**Q9: How do you ensure the system is responsive on mobile devices?**
*   **A:** I used **Bootstrap 5.3.3** responsive grid classes (e.g., `col-12 col-md-6`) and custom CSS media queries to ensure the layout adjusts to screen widths like 320px and 375px.

**Q10: What is the benefit of using `faster-whisper` over standard `whisper`?**
*   **A:** It is up to 4x faster and uses significantly less memory while maintaining the same accuracy, making it ideal for real-time web applications running on standard servers.

**Q11: How do you handle TMDB API rate limits or failures?**
*   **A:** I implemented a 5-second timeout in `tmdb_helper.php` and an automatic fallback to the local `cached_movies` table to ensure a smooth user experience even when the API is unstable.

**Q12: Why did you choose the 'SSD' backend for facial detection?**
*   **A:** SSD offers a great balance between speed and accuracy compared to the default OpenCV Haar Cascades, which often fail when the face is slightly tilted.

**Q13: How does the Admin Weight system work?**
*   **A:** If multiple genres are mapped to one mood, the system picks the one with the highest `weight` value. This allows the admin to fine-tune recommendations based on user feedback.

**Q14: Can the system detect moods in languages other than English?**
*   **A:** Currently, the text analysis is optimized for English, but the **Voice Acoustic analysis** (energy/pitch) is language-agnostic and can detect emotions based on tone in any language.

**Q15: How are movie posters handled if TMDB doesn't provide one?**
*   **A:** The `format_movie_data` function in `tmdb_helper.php` checks if `poster_path` is empty and provides a local fallback image (`assets/img/no_poster.jpg`).

**Q16: How do you handle user sessions and authentication?**
*   **A:** I use standard PHP `session_start()` to manage user state. When a user logs in or enters as a guest, their `user_id` and `is_guest` status are stored in the `$_SESSION` superglobal, allowing the system to personalize recommendations and restrict database writes for guests.

**Q17: What is the purpose of the `mood_genre_fallback.json` file?**
*   **A:** It serves as a tertiary "Hard-Coded" fallback. If both the database and the Python service are unavailable, the `mood_mapper.php` script reads this file to ensure the system can still map a detected mood to a valid TMDB genre ID.

**Q18: How does the system handle "Neutral" moods?**
*   **A:** In text analysis, if polarity and subjectivity are near zero, the mood is Neutral. In voice, if energy levels are average and no intense keywords are found, it defaults to Neutral. This is mapped to "War" or "Documentary" genres to provide a grounded viewing experience.

**Q19: How did you implement the "Animate On Scroll" (AOS) effect?**
*   **A:** I integrated the AOS JavaScript library. Elements are tagged with `data-aos="fade-up"` or similar attributes. The library calculates the viewport position and applies CSS transitions as the user scrolls, creating a premium, modern feel.

**Q20: If you were to scale this project, what would be your first step?**
*   **A:** I would implement a **Redis Cache** for the TMDB API responses to reduce network latency and move the AI processing to a dedicated GPU-enabled cluster using **Docker** containers to handle thousands of concurrent mood detections.
