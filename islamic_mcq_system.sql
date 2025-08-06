-- Islamic MCQ System Database Setup
-- Drop existing MCQ tables if they exist
DROP TABLE IF EXISTS mcq_user_answers;
DROP TABLE IF EXISTS mcq_options;
DROP TABLE IF EXISTS mcq_questions;
DROP TABLE IF EXISTS mcq_categories;

-- Create MCQ categories table
CREATE TABLE mcq_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create MCQ questions table
CREATE TABLE mcq_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    question_text TEXT NOT NULL,
    explanation TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    points INT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES mcq_categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create MCQ options table
CREATE TABLE mcq_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES mcq_questions(id) ON DELETE CASCADE
);

-- Create user answers table
CREATE TABLE mcq_user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT NOT NULL,
    is_correct BOOLEAN,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES mcq_questions(id),
    FOREIGN KEY (selected_option_id) REFERENCES mcq_options(id),
    UNIQUE KEY unique_user_question (user_id, question_id)
);

-- Add indexes for better performance
CREATE INDEX idx_mcq_questions_category ON mcq_questions(category_id);
CREATE INDEX idx_mcq_questions_difficulty ON mcq_questions(difficulty);
CREATE INDEX idx_mcq_options_question ON mcq_options(question_id);
CREATE INDEX idx_mcq_user_answers_user ON mcq_user_answers(user_id);
CREATE INDEX idx_mcq_user_answers_question ON mcq_user_answers(question_id);

-- Insert Islamic categories
INSERT INTO mcq_categories (name, description) VALUES
('Aqeedah', 'Questions about Islamic beliefs and creed'),
('Fiqh', 'Questions about Islamic jurisprudence and rulings'),
('Hadith', 'Questions about Prophet Muhammad (PBUH) sayings and traditions'),
('Tafsir', 'Questions about Quran interpretation and commentary'),
('Seerah', 'Questions about the life of Prophet Muhammad (PBUH)'),
('Islamic History', 'Questions about Islamic history and civilization'),
('Arabic Language', 'Questions about Arabic grammar and vocabulary');
