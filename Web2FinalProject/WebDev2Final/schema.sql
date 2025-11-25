-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  age INT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  distance_km INT DEFAULT 0,
  bio VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes
CREATE TABLE IF NOT EXISTS likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  liker_id INT NOT NULL,
  liked_id INT NOT NULL,
  liked_at DATETIME NOT NULL,
  UNIQUE KEY ux_like_pair (liker_id, liked_id),
  KEY idx_like_liker (liker_id),
  KEY idx_like_liked (liked_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dislikes
CREATE TABLE IF NOT EXISTS dislikes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  disliker_id INT NOT NULL,
  disliked_id INT NOT NULL,
  disliked_at DATETIME NOT NULL,
  UNIQUE KEY ux_dislike_pair (disliker_id, disliked_id),
  KEY idx_dislike_disliker (disliker_id),
  KEY idx_dislike_disliked (disliked_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Matches (unordered pair: smaller id first)
CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user1_id INT NOT NULL,
  user2_id INT NOT NULL,
  matched_at DATETIME NOT NULL,
  UNIQUE KEY ux_match_pair (user1_id, user2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo seed (optional)
INSERT INTO users (name, age, photo, distance_km, bio) VALUES
('Amy', 25, 'img/1.jpg', 2, 'Love Dogs'),
('Susan', 27, 'img/2.jpg', 3, 'Looking for library dates'),
('Shizuka', 24, 'img/3.jpg', 5, 'Chili-pot date'),
('Diana', 26, 'img/4.jpg', 7, 'Just someone fun'),
('Eve', 22, 'img/5.jpg', 8, 'Surprise me'),
('Alexandra', 29, 'img/6.jpg', 4, 'Too lazy to go out'),
('Grace', 23, 'img/8.jpg', 6, ':)'),
('Hank', 30, 'img/7.jpg', 9, 'Focusing on myself');
