-- Database: mood_recommender_db

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Mood History Table (Tracks all detections)
CREATE TABLE IF NOT EXISTS `user_mood_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mood` varchar(20) NOT NULL,
  `input_type` enum('text','voice','face') NOT NULL,
  `detected_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Mood Definitions Table
CREATE TABLE IF NOT EXISTS `mood_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mood_name` varchar(50) NOT NULL UNIQUE,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Mood-to-Genre Mapping Table
CREATE TABLE IF NOT EXISTS `mood_genre_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mood_id` int(11) NOT NULL,
  `mood_name` varchar(50) NOT NULL,
  `genre_id` int(11) NOT NULL,
  `genre_name` varchar(50) NOT NULL,
  `weight` int(11) DEFAULT 100,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mood_genre_unique` (`mood_id`, `genre_id`),
  FOREIGN KEY (`mood_id`) REFERENCES `mood_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. System Settings Table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Initial Moods
INSERT IGNORE INTO `mood_definitions` (`mood_name`) VALUES
('Happy'), ('Sad'), ('Angry'), ('Excited'), ('Neutral');

-- Insert Initial Mappings
INSERT IGNORE INTO `mood_genre_mapping` (`mood_id`, `mood_name`, `genre_id`, `genre_name`, `weight`) VALUES
(1, 'Happy', 35, 'Comedy', 100),
(2, 'Sad', 18, 'Drama', 100),
(3, 'Angry', 28, 'Action', 100),
(4, 'Excited', 10751, 'Family', 100),
(5, 'Neutral', 10752, 'War', 100);

-- Insert Default Settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'MoodAI'),
('tmdb_api_key', ''); -- Enter your TMDB API key here

-- 4. Cached Movies Table (Fallback System)
CREATE TABLE IF NOT EXISTS `cached_movies` (
  `tmdb_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `overview` text DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `vote_average` decimal(3,1) DEFAULT NULL,
  `genre_id` int(11) NOT NULL,
  `original_language` varchar(10) DEFAULT NULL,
  `cached_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tmdb_id`, `genre_id`) -- Composite key to allow same movie in different genres
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Favorites Table (Saved movies)
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tmdb_movie_id` int(11) NOT NULL,
  `movie_title` varchar(255) NOT NULL,
  `movie_poster` varchar(255) DEFAULT NULL,
  `mood_tag` varchar(20) DEFAULT NULL, -- The mood associated with this recommendation
  `saved_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
