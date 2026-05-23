import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

# Get DB config from environment variables
DB_CONFIG = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'database': os.getenv('DB_NAME', 'mood_recommender_db'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASS', '')
}

def get_db_connection():
    """
    Creates and returns a connection to the shared MySQL database.
    """
    try:
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password']
        )
        if connection.is_connected():
            return connection
    except Error as e:
        # Avoid flooding logs if DB is not available
        print(f"Database connection error: {e}")
        return None

def query_mapping_from_db(mood_name):
    """
    Queries the mood_genre_mapping table for the highest weighted genre.
    """
    connection = get_db_connection()
    if not connection:
        return None

    cursor = None
    try:
        cursor = connection.cursor(dictionary=True)
        query = "SELECT genre_id FROM mood_genre_mapping WHERE mood_name = %s ORDER BY weight DESC LIMIT 1"
        cursor.execute(query, (mood_name.capitalize(),))
        result = cursor.fetchone()
        return result['genre_id'] if result else None
    except Error as e:
        print(f"Error querying database: {e}")
        return None
    finally:
        if cursor:
            cursor.close()
        if connection.is_connected() if connection else False:
            connection.close()

def query_all_mappings_from_db():
    """
    Queries all mood-to-genre mappings from the database.
    """
    connection = get_db_connection()
    if not connection:
        return None

    cursor = None
    try:
        cursor = connection.cursor(dictionary=True)
        query = "SELECT mood_name, genre_id FROM mood_genre_mapping ORDER BY weight DESC"
        cursor.execute(query)
        results = cursor.fetchall()

        # Build a dictionary, later entries for the same mood will be ignored due to DESC weight
        mapping = {}
        for row in results:
            if row['mood_name'] not in mapping:
                mapping[row['mood_name']] = row['genre_id']
        return mapping
    except Error as e:
        print(f"Error querying all mappings from database: {e}")
        return None
    finally:
        if cursor:
            cursor.close()
        if connection.is_connected() if connection else False:
            connection.close()
