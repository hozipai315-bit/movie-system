# mood_to_genre.py
# Maps detected moods to TMDB Genre IDs
import os
import json

try:
    from services.db_service import query_mapping_from_db, query_all_mappings_from_db
except ImportError:
    # Fallback for direct script execution or testing
    import sys
    sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    from services.db_service import query_mapping_from_db, query_all_mappings_from_db

# SECONDARY FALLBACK MAP
# Centralized fallback mapping from JSON
def load_fallback_map():
    fallback_path = os.path.join(os.path.dirname(__file__), '..', '..', 'database', 'mood_genre_fallback.json')
    default_map = {
        'Happy': 35, 'Sad': 18, 'Angry': 28, 'Excited': 10751,
        'Neutral': 10752, 'Default': 35
    }
    if os.path.exists(fallback_path):
        try:
            with open(fallback_path, 'r') as f:
                return json.load(f)
        except:
            pass
    return default_map

MOOD_GENRE_MAP = load_fallback_map()

def get_genre_for_mood(mood):
    """
    Returns the TMDB genre ID associated with a mood.
    Priority: 1. Database (Source of Truth) 2. Local Fallback Map
    """
    mood_key = mood.capitalize() if mood else 'Default'

    # 1. ATTEMPT DATABASE QUERY
    db_genre = query_mapping_from_db(mood_key)
    if db_genre:
        return db_genre

    # 2. LOCAL FALLBACK
    return MOOD_GENRE_MAP.get(mood_key, MOOD_GENRE_MAP['Default'])

def get_all_mappings():
    """
    Returns all mood-to-genre mappings.
    Priority: 1. Database 2. Local Fallback Map
    """
    db_mappings = query_all_mappings_from_db()
    if db_mappings:
        return db_mappings
    return MOOD_GENRE_MAP
