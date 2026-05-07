-- Migration : création de la table lessons
-- À exécuter UNE SEULE FOIS dans phpMyAdmin, puis supprimer ce fichier.

CREATE TABLE lessons (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    course_id  INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    TEXT NOT NULL,
    position   INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
