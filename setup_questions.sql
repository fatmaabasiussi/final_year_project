-- Drop existing tables if they exist
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS questions;

-- Create questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    category ENUM('aqeedah', 'fiqh', 'hadith', 'tafsir', 'seerah', 'other') NOT NULL,
    course_id INT,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    answer TEXT,
    answered_by INT,
    answered_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (answered_by) REFERENCES users(id)
);

-- Create answers table
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    scholar_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id),
    FOREIGN KEY (scholar_id) REFERENCES users(id)
);

-- Add indexes for better performance
CREATE INDEX idx_questions_user ON questions(user_id);
CREATE INDEX idx_questions_status ON questions(status);
CREATE INDEX idx_questions_course ON questions(course_id);
CREATE INDEX idx_questions_category ON questions(category);
CREATE INDEX idx_answers_question ON answers(question_id);
CREATE INDEX idx_answers_scholar ON answers(scholar_id); 