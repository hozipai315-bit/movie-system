import nltk

# Ensure necessary NLTK data is downloaded
REQUIRED_NLTK_DATA = [
    'punkt',
    'punkt_tab',
    'wordnet',
    'omw-1.4',
    'brown',
    'averaged_perceptron_tagger_eng',
    'conll2000',
    'movie_reviews'
]

for data in REQUIRED_NLTK_DATA:
    try:
        nltk.data.find(data)
    except (LookupError, AttributeError):
        print(f"Downloading NLTK data: {data}...")
        nltk.download(data, quiet=True)

from textblob import TextBlob, Word

# --- CONFIGURATION: INTENSIFIERS & NEGATIONS ---
INTENSIFIERS = {
    "very": 1.5, "so": 1.5, "extremely": 2.0, "really": 1.5, "absolutely": 2.0,
    "completely": 1.5, "totally": 1.5, "super": 1.5, "highly": 1.5, "mega": 1.5
}
DOWNPLAYERS = {
    "kinda": 0.5, "kind of": 0.5, "sorta": 0.5, "sort of": 0.5,
    "barely": 0.5, "slightly": 0.5, "little": 0.8, "bit": 0.8
}
NEGATIONS = {"not", "no", "never", "nothing", "neither", "nor", "cannot", "can't", "won't", "don't", "doesn't", "didn't", "isn't", "aren't"}

# --- CONFIGURATION: KEYWORDS (Base forms preferred) ---
HAPPY_WORDS = {
    "happy": 1.2, "happiness": 1.2, "joy": 1.2, "joyful": 1.2, "cheerful": 1.2,
    "delighted": 1.2, "pleased": 1.0, "glad": 1.0, "content": 1.0, "satisfied": 1.0,
    "relieved": 1.0, "comfortable": 0.8, "peaceful": 0.8, "calm": 0.8, "relax": 0.8,
    "good": 0.6, "great": 1.0, "nice": 0.6, "excellent": 1.2, "amazing": 1.5,
    "okay": 0.4, "ok": 0.4, "fine": 0.4, "optimum": 1.0, "bliss": 2.0,
    "perfect": 1.5, "wonderful": 1.2, "pleasant": 0.8, "positive": 1.0,
    "love": 1.5, "like": 0.5, "enjoy": 1.0, "success": 1.5, "win": 1.5,
    "achievement": 1.2, "accomplish": 1.2, "proud": 1.2, "fun": 1.0,
    "funny": 1.0, "cool": 0.8, "awesome": 1.5, "fantastic": 1.5,
    "mood": 0.1
}
SAD_WORDS = {
    "sad": 1.5, "disappointment": 1.2, "disappointed": 1.2, "upset": 1.2, "miserable": 2.0,
    "heartbroken": 2.0, "cry": 1.5, "unhappy": 1.2, "gloomy": 1.0, "somber": 1.0,
    "depressed": 2.0, "down": 1.2, "melancholy": 1.5, "lonely": 1.5, "grief": 2.0,
    "sorrow": 1.8, "despondent": 1.8, "hopeless": 1.5, "blue": 1.0, "tragic": 1.5,
    "mourn": 1.8, "heartache": 1.8, "regret": 1.5, "pain": 1.5, "distress": 1.5,
    "dejected": 1.8, "downcast": 1.5, "weary": 1.2, "tear": 1.5, "grieve": 2.0,
    "desolate": 1.8, "anguish": 2.0, "gutted": 1.5, "dismal": 1.5, "bleak": 1.5,
    "low": 1.2, "forlorn": 2.0, "lament": 1.8, "discouraged": 1.5, "disheartened": 1.5,
    "despair": 2.0, "hopelessness": 2.0, "misery": 2.0, "anguished": 2.0, "unhappy": 1.5,
    "worthless": 2.0, "useless": 1.5
}
ANGRY_WORDS = {
    "angry": 1.5, "mad": 1.5, "furious": 2.0, "rage": 2.0, "outraged": 2.0,
    "irritate": 1.0, "annoy": 1.0, "frustrate": 1.2, "pissed": 1.8, "enraged": 2.0,
    "livid": 2.0, "hostile": 1.5, "hate": 2.0, "despise": 1.8, "loathe": 1.8,
    "disgust": 1.5, "abhorrent": 1.8, "repulsive": 1.8, "stupid": 1.2, "idiot": 1.5,
    "dumb": 1.2, "useless": 1.2, "pathetic": 1.2, "ridiculous": 1.0, "nonsense": 1.0,
    "trash": 1.2, "garbage": 1.2, "crap": 1.2, "bullshit": 1.8, "shitty": 1.8,
    "worst": 1.5, "awful": 1.5, "terrible": 1.5, "horrible": 1.5, "unacceptable": 1.2,
    "fail": 1.2, "buggy": 1.0, "slow": 1.0, "stress": 1.2, "disaster": 1.5,
    "nightmare": 1.5, "ruin": 1.5, "destroy": 1.5, "waste": 1.2, "damn": 1.2,
    "dammit": 1.2, "hell": 1.2, "bloody": 1.2, "bad": 0.5
}
EXCITED_WORDS = {
    "wow": 1.5, "amazing": 1.5, "awesome": 1.5, "excited": 1.2, "love": 1.2,
    "omg": 1.8, "yay": 1.5, "thrill": 1.5, "eager": 1.0, "ecstatic": 2.0,
    "overjoyed": 1.8, "elated": 1.8, "pumped": 1.5, "stoked": 1.5, "gleeful": 1.5,
    "jubilant": 1.8, "delighted": 1.5, "hyped": 1.5, "cheery": 1.2, "fire": 1.5,
    "electric": 1.5, "vibrant": 1.2, "amped": 1.5, "delirious": 1.8, "exhilarate": 2.0,
    "bounce": 1.0, "joyous": 1.8, "ecstasy": 2.0, "revved": 1.5, "sparkle": 1.2,
    "giddy": 1.5, "euphoric": 2.0, "animate": 1.2
}

# --- CONFIGURATION: IDIOMS (Priority Phrases) ---
IDIOMS = {
    "fed up": ("Angry", 1.8),
    "can't stand": ("Angry", 1.8),
    "had enough": ("Angry", 1.5),
    "sick of": ("Angry", 1.5),
    "tired of": ("Angry", 1.5),
    "over the moon": ("Excited", 2.0),
    "cloud nine": ("Excited", 2.0),
    "thrilled to bits": ("Excited", 2.0),
    "fired up": ("Excited", 1.8),
    "can't wait": ("Excited", 1.8),
    "looking forward": ("Excited", 1.5),
    "good mood": ("Happy", 1.5),
    "bad mood": ("Sad", 1.5),
    "feeling down": ("Sad", 1.5),
    "feel down": ("Sad", 1.5),
    "bummed out": ("Sad", 1.5),
    "broken hearted": ("Sad", 2.0),
    "give up": ("Sad", 1.5)
}

def detect_mood_level2(text):
    # 1. Preprocessing & Initial Stats
    blob = TextBlob(text)
    text_lower = text.lower()

    polarity, subjectivity = blob.sentiment

    # Global Intensity Check
    has_exclamation = "!" in text
    is_all_caps = text.isupper() and len(text) > 4

    global_intensity = 1.0
    if has_exclamation: global_intensity += 0.2
    if is_all_caps: global_intensity += 0.3

    mood_scores = {"Happy": 0.0, "Sad": 0.0, "Angry": 0.0, "Excited": 0.0}

    # 2. Idiom Matching (Priority)
    for idiom, (mood, weight) in IDIOMS.items():
        if idiom in text_lower and mood in mood_scores:
            # Check for negation before the idiom
            # Find the position of the idiom
            idx = text_lower.find(idiom)
            prefix = text_lower[max(0, idx-15):idx].split()

            is_negated = False
            for neg in NEGATIONS:
                if neg in prefix:
                    is_negated = True
                    break

            if is_negated:
                mood_scores[mood] -= (weight * 0.8)
                if mood == "Happy":
                    mood_scores["Sad"] += (weight * 0.4)
            else:
                mood_scores[mood] += weight * global_intensity

    # 3. Word-Level Analysis with Context
    words = blob.words

    for i, original_word in enumerate(words):
        word_lower = original_word.lower()
        try:
            lemma = Word(word_lower).lemmatize()
            if lemma == word_lower:
                lemma = Word(word_lower).lemmatize("v")
        except:
            lemma = word_lower

        monitor_window = words[max(0, i-2):i]
        monitor_window_lower = [w.lower() for w in monitor_window]

        local_multiplier = 1.0
        is_negated = False

        for neg in NEGATIONS:
            if neg in monitor_window_lower:
                is_negated = True
                break

        for intensifier, value in INTENSIFIERS.items():
            if intensifier in monitor_window_lower:
                local_multiplier *= value

        for downplayer, value in DOWNPLAYERS.items():
            if downplayer in monitor_window_lower:
                local_multiplier *= value

        # 4. Scoring against Dictionaries
        all_moods = {
            "Happy": HAPPY_WORDS,
            "Sad": SAD_WORDS,
            "Angry": ANGRY_WORDS,
            "Excited": EXCITED_WORDS
        }

        for mood_name, mood_dict in all_moods.items():
            score = 0
            if lemma in mood_dict:
                score = mood_dict[lemma]
            elif word_lower in mood_dict:
                score = mood_dict[word_lower]

            if score > 0:
                final_score = score * local_multiplier * global_intensity

                if is_negated:
                    mood_scores[mood_name] -= (score * 0.8)
                    # If happy is negated, lean towards Sad/Neutral
                    if mood_name == "Happy":
                        mood_scores["Sad"] += (score * 0.4)
                else:
                    mood_scores[mood_name] += final_score

    # 5. Polarity Adjustment (Fine-tuning)
    # Caution: TextBlob polarity often misses negations like "not good"
    # We only apply it if the subjectivity is high and it doesn't contradict our word analysis
    if subjectivity > 0.4:
        if polarity > 0.3 and mood_scores["Sad"] < 0.5:
            mood_scores["Happy"] += polarity
            mood_scores["Excited"] += polarity
        elif polarity < -0.2:
            mood_scores["Sad"] += abs(polarity)
            mood_scores["Angry"] += abs(polarity)

    # 6. Final Decision
    detected_mood = "Neutral"
    max_score = 0.0

    total_energy = sum(max(0, s) for s in mood_scores.values())

    if total_energy < 0.3 and -0.1 < polarity < 0.1:
        detected_mood = "Neutral"
    else:
        # Find winner among positive scores
        winner = None
        current_max = 0.0
        for m, s in mood_scores.items():
            if s > current_max:
                current_max = s
                winner = m

        if winner:
            detected_mood = winner
        else:
            detected_mood = "Neutral"

    return polarity, subjectivity, detected_mood, mood_scores

# -------- TEST --------
def run_tests():
    test_cases = [
        "I am enjoying this.",
        "I played a game and it was fun.",
        "I am very happy!",
        "I am sort of happy.",
        "I am not happy.",
        "I am fed up with this.",
        "I can't wait for the movie!",
        "This is not a bad result."
    ]

    print(f"{'TEXT':<40} | {'MOOD':<10} | {'SCORES'}")
    print("-" * 100)
    for text in test_cases:
        p, s, mood, scores = detect_mood_level2(text)
        formatted_scores = {m: f"{v:.1f}" for m,v in scores.items()}
        print(f"{text:<40} | {mood:<10} | {formatted_scores}")

if __name__ == "__main__":
    run_tests()
