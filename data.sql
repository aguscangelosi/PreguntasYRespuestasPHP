DROP DATABASE IF EXISTS test;
CREATE DATABASE test;
-- Seleccion de la base de datos creada
USE test;

CREATE TABLE rol
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(50) NOT NULL
);

CREATE TABLE user
(
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(100) NOT NULL,
    sex             CHAR(1) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    rol_id          INT,
    email           VARCHAR(100) NOT NULL,
    birthday        DATE,
    name            VARCHAR(60) NOT NULL,
    profile_picture VARCHAR(255),
    register_date   DATE,
    pais            VARCHAR(100),
    ciudad          VARCHAR(100),
    FOREIGN KEY (rol_id) REFERENCES rol (id),
    hasAccess       BOOLEAN NOT NULL,
    preguntas_respondidas INT DEFAULT 0,
    preguntas_correctas INT DEFAULT 0
);

INSERT INTO rol (description)
VALUES ('admin'), ('user'), ('editor');

INSERT INTO user (username, sex, password, rol_id, email, birthday, name, profile_picture, register_date, pais, ciudad, hasAccess)
VALUES
    ('jdoe', 'M', '12345', 2, 'jdoe@example.com', '1990-05-15', 'John Doe', 'profile1.jpg', '2024-01-10', 'USA', 'New York', false),
    ('asmith', 'F', '12345', 2, 'asmith@example.com', '1985-07-22', 'Alice Smith', 'profile2.jpg', '2024-02-18', 'Canada', 'Toronto', false),
    ('bwilliams', 'M', '12345', 2, 'bwilliams@example.com', '1992-11-10', 'Bob Williams', 'profile3.jpg', '2024-03-12', 'UK', 'London', false),
    ('cmiller', 'X', '12345', 2, 'cmiller@example.com', '2000-03-30', 'Charlie Miller', 'profile4.jpg', '2024-04-05', 'Australia', 'Sydney', false),
    ('djohnson', 'F', '12345', 2, 'djohnson@example.com', '1998-09-12', 'Diana Johnson', 'profile5.jpg', '2024-05-20', 'Argentina', 'Buenos Aires', false),
    ('Usuario100.', 'M', '$2y$10$SLGa9bq7dEWBpRViic0Zc.AhBpyRv9kIb1NVgQCWJL.moBMbmUZM6', 2, 'mm25AA@example.com', '1998-09-12', 'Facu1', 'profile5.jpg', '2024-05-20', 'Chile', 'Santiago', true),
    ('Editor100.', 'F', '$2y$10$mc83bpYFDPIy0AQtMHgsyuO.y1WyZ1ku5V7Y20X9j/W06dODW1Bjm', 3, 'mmm25AA@example.com', '1998-09-12', 'Facu2', 'profile6.jpg', '2024-06-20', 'Brazil', 'São Paulo', true),
    ('Admin100.', 'M', '$2y$10$ksupjXLXDjkuev9o8GypfOhYZ06BWh5UM/I6p/5d.6OZ3MqiWV76u', 1, 'mmmm25AA@example.com', '1998-09-12', 'Facu3', 'profile7.jpg', '2024-07-20', 'Spain', 'Madrid', true);

INSERT INTO user (username, sex, password, rol_id, email, birthday, name, profile_picture, register_date, pais, ciudad, hasAccess)
VALUES
    ('user1', 'M', '12345', 2, 'user1@example.com', '2005-04-10', 'Lucas Pérez', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-01', 'Mexico', 'Cancún', false),
    ('user2', 'F', '12345', 2, 'user2@example.com', '2010-09-25', 'Lucía García', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-02', 'USA', 'Los Angeles', false),
    ('user3', 'M', '12345', 2, 'user3@example.com', '1980-06-15', 'Carlos Santos', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-03', 'Colombia', 'Bogotá', false),
    ('user4', 'F', '12345', 2, 'user4@example.com', '1965-03-12', 'María Rodríguez', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-04', 'Chile', 'Valparaíso', false),
    ('user5', 'X', '12345', 2, 'user5@example.com', '2008-11-18', 'Alex Martínez', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-05', 'Peru', 'Lima', false),
    ('user6', 'M', '12345', 2, 'user6@example.com', '1975-07-08', 'Diego López', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-06', 'Argentina', 'Rosario', false),
    ('user7', 'F', '12345', 2, 'user7@example.com', '1995-01-30', 'Sofía Torres', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-07', 'Spain', 'Barcelona', false),
    ('user8', 'M', '12345', 2, 'user8@example.com', '1940-02-05', 'Antonio Fernández', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-08', 'Italy', 'Rome', false),
    ('user9', 'F', '12345', 2, 'user9@example.com', '2002-12-20', 'Valentina Gómez', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-09', 'France', 'Paris', false),
    ('user10', 'X', '12345', 2, 'user10@example.com', '2015-08-14', 'Sammy Brown', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-10', 'Germany', 'Berlin', false),
    ('user11', 'M', '12345', 2, 'user11@example.com', '1990-11-30', 'Jorge Ortega', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-11', 'Ecuador', 'Quito', false),
    ('user12', 'F', '12345', 2, 'user12@example.com', '2000-05-22', 'Ana Morales', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-12', 'Cuba', 'Havana', false),
    ('user13', 'M', '12345', 2, 'user13@example.com', '1988-09-01', 'Ricardo Núñez', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-13', 'Uruguay', 'Montevideo', false),
    ('user14', 'F', '12345', 2, 'user14@example.com', '1970-12-24', 'Mónica Herrera', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-14', 'Venezuela', 'Caracas', false),
    ('user15', 'M', '12345', 2, 'user15@example.com', '2007-10-17', 'Pedro Díaz', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-15', 'Panama', 'Panama City', false),
    ('user16', 'F', '12345', 2, 'user16@example.com', '1983-03-11', 'Camila Ruiz', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-16', 'Mexico', 'Guadalajara', false),
    ('user17', 'X', '12345', 2, 'user17@example.com', '2009-01-20', 'Jordan White', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-17', 'Canada', 'Vancouver', false),
    ('user18', 'M', '12345', 2, 'user18@example.com', '1955-06-29', 'Luis Vega', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-18', 'Bolivia', 'La Paz', false),
    ('user19', 'F', '12345', 2, 'user19@example.com', '1993-04-07', 'Sara Silva', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-19', 'Brazil', 'Rio de Janeiro', false),
    ('user20', 'M', '$2y$10$SLGa9bq7dEWBpRViic0Zc.AhBpyRv9kIb1NVgQCWJL.moBMbmUZM6', 2, 'user20@example.com', '1960-02-15', 'Francisco Ríos', '/PreguntasYRespuestasPHP/img/profile/default.png', '2024-01-20', 'Portugal', 'Lisbon', true);


CREATE TABLE category (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          nombre_categoria VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE status (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nombre_estado VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE question (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          enunciado TEXT NOT NULL,
                          dificultad VARCHAR(50) NOT NULL,  -- Puedes cambiar a un ID si normalizas la dificultad
                          categoria_id INT,
                          estado_id INT,
                          activo BOOLEAN DEFAULT TRUE,
                          veces_presentada INT DEFAULT 0,
                          veces_correctas INT DEFAULT 0,
                          FOREIGN KEY (categoria_id) REFERENCES category(id),
                          FOREIGN KEY (estado_id) REFERENCES status(id)
);

CREATE TABLE answer (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        texto_respuesta TEXT NOT NULL,
                        categoria_id INT,
                        FOREIGN KEY (categoria_id) REFERENCES category(id)
);

CREATE TABLE question_answer (
                                 pregunta_id INT,
                                 respuesta_id INT,
                                 es_correcta BOOLEAN NOT NULL,
                                 PRIMARY KEY (pregunta_id, respuesta_id),
                                 FOREIGN KEY (pregunta_id) REFERENCES question(id),
                                 FOREIGN KEY (respuesta_id) REFERENCES answer(id)
);

CREATE TABLE game (
                      id INT AUTO_INCREMENT PRIMARY KEY,
                      fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      fecha_fin TIMESTAMP NULL,
                      estado VARCHAR(20) NOT NULL,  -- 'en curso', 'finalizada', etc.
                      categoria_id INT,
                      FOREIGN KEY (categoria_id) REFERENCES category(id)
);

-- Tabla intermedia para registrar usuarios en una partida y sus puntajes
CREATE TABLE user_game (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           user_id INT,
                           partida_id INT,
                           puntaje INT DEFAULT 0,
                           ultima_pregunta_id INT NULL,
                           estado_pregunta ENUM('respondida', 'pendiente') DEFAULT 'pendiente',
                           fecha_respuesta DATETIME NULL,
                           FOREIGN KEY (user_id) REFERENCES user(id),
                           FOREIGN KEY (partida_id) REFERENCES game(id),
                           FOREIGN KEY (ultima_pregunta_id) REFERENCES question(id)
);


CREATE TABLE game_question (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               partida_id INT,
                               pregunta_id INT,
                               es_correcta BOOLEAN, -- Indica si la pregunta fue respondida correctamente en esa partida
                               FOREIGN KEY (partida_id) REFERENCES game(id),
                               FOREIGN KEY (pregunta_id) REFERENCES question(id)
);

CREATE TABLE user_question (
                               user_id INT NOT NULL,
                               question_id INT NOT NULL,
                               PRIMARY KEY (user_id, question_id),
                               FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                               FOREIGN KEY (question_id) REFERENCES question(id) ON DELETE CASCADE
);

CREATE TABLE question_report (
                                 report_id INT AUTO_INCREMENT PRIMARY KEY,
                                 question_id INT NOT NULL,
                                 description TEXT,
                                 report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                 FOREIGN KEY (question_id) REFERENCES question(id)
);
CREATE TABLE statistics_admin (
                                      id INT AUTO_INCREMENT PRIMARY KEY,
                                      question_id INT,
                                      user_id INT,
                                      FOREIGN KEY (question_id) REFERENCES question(id),
                                      FOREIGN KEY (user_id) REFERENCES user(id)

);

-- Inserción de categorías de ejemplo
INSERT INTO category (nombre_categoria) VALUES
                                            ('Deportes'), ('Historia'), ('Ciencia'), ('Anime');

-- Inserción de status de ejemplo
INSERT INTO status (nombre_estado) VALUES
                                       ('pendiente'), ('aprobada'), ('rechazada'), ('reportada'), ('desactivada');

-- Preguntas de ejemplo
INSERT INTO question (enunciado, dificultad, categoria_id, estado_id, activo) VALUES
                                                                                  ('¿Cuál es el país de origen del fútbol?', 'Fácil', 1, 2, TRUE),
                                                                                  ('¿Quién pintó la Mona Lisa?', 'Media', 2, 2, TRUE),
                                                                                  ('¿Qué planeta es conocido como el Planeta Rojo?', 'Fácil', 3, 2, TRUE),
                                                                                  ('¿En qué serie aparece el personaje Goku?', 'Fácil', 4, 2, TRUE);

-- Respuestas de ejemplo
INSERT INTO answer (texto_respuesta, categoria_id) VALUES
                                                       ('Inglaterra', 1),      -- Correcta para la primera pregunta
                                                       ('Italia', 1),
                                                       ('Brasil', 1),
                                                       ('Argentina', 1),

                                                       ('Leonardo da Vinci', 2), -- Correcta para la segunda pregunta
                                                       ('Pablo Picasso', 2),
                                                       ('Vincent van Gogh', 2),
                                                       ('Miguel Ángel', 2),

                                                       ('Marte', 3),            -- Correcta para la tercera pregunta
                                                       ('Júpiter', 3),
                                                       ('Saturno', 3),
                                                       ('Venus', 3),

                                                       ('Dragon Ball', 4),      -- Correcta para la cuarta pregunta
                                                       ('Naruto', 4),
                                                       ('One Piece', 4),
                                                       ('Bleach', 4);

-- Relación de question y answer
INSERT INTO question_answer (pregunta_id, respuesta_id, es_correcta) VALUES
                                                                         (1, 1, TRUE), (1, 2, FALSE), (1, 3, FALSE), (1, 4, FALSE),
                                                                         (2, 5, TRUE), (2, 6, FALSE), (2, 7, FALSE), (2, 8, FALSE),
                                                                         (3, 9, TRUE), (3, 10, FALSE), (3, 11, FALSE), (3, 12, FALSE),
                                                                         (4, 13, TRUE), (4, 14, FALSE), (4, 15, FALSE), (4, 16, FALSE);


-- Preguntas de ejemplo
INSERT INTO question (enunciado, dificultad, categoria_id, estado_id, activo) VALUES
                                                                                  ('¿En qué año se celebró el primer Mundial de Fútbol?', 'Difícil', 1, 2, TRUE),
                                                                                  ('¿Qué país ha ganado más Copas del Mundo de fútbol?', 'Media', 1, 2, TRUE),
                                                                                  ('¿Quién escribió "La Divina Comedia"?', 'Media', 2, 2, TRUE),
                                                                                  ('¿En qué museo se encuentra "La Noche Estrellada" de Van Gogh?', 'Difícil', 2, 2, TRUE),
                                                                                  ('¿Cuál es el planeta más grande del sistema solar?', 'Fácil', 3, 2, TRUE),
                                                                                  ('¿Qué elemento químico tiene el símbolo "O"?', 'Fácil', 3, 2, TRUE),
                                                                                  ('¿En qué serie aparece el personaje Ash Ketchum?', 'Fácil', 4, 2, TRUE),
                                                                                  ('¿Quién es el creador de la serie "One Piece"?', 'Media', 4, 2, TRUE);

-- Respuestas de ejemplo
INSERT INTO answer (texto_respuesta, categoria_id) VALUES
                                                       ('1930', 1),           -- Correcta para la primera pregunta
                                                       ('1950', 1),
                                                       ('1962', 1),
                                                       ('1970', 1),

                                                       ('Brasil', 1),         -- Correcta para la segunda pregunta
                                                       ('Argentina', 1),
                                                       ('Alemania', 1),
                                                       ('Italia', 1),

                                                       ('Dante Alighieri', 2), -- Correcta para la tercera pregunta
                                                       ('Miguel de Cervantes', 2),
                                                       ('Homer', 2),
                                                       ('William Shakespeare', 2),

                                                       ('Museo de Arte Moderno de Nueva York', 2), -- Correcta para la cuarta pregunta
                                                       ('Museo del Louvre', 2),
                                                       ('Museo del Prado', 2),
                                                       ('Museo Británico', 2),

                                                       ('Júpiter', 3),       -- Correcta para la quinta pregunta
                                                       ('Saturno', 3),
                                                       ('Marte', 3),
                                                       ('Venus', 3),

                                                       ('Oxígeno', 3),        -- Correcta para la sexta pregunta
                                                       ('Carbono', 3),
                                                       ('Hidrógeno', 3),
                                                       ('Nitrógeno', 3),

                                                       ('Pokémon', 4),        -- Correcta para la séptima pregunta
                                                       ('Yu-Gi-Oh!', 4),
                                                       ('Digimon', 4),
                                                       ('Beyblade', 4),

                                                       ('Eiichiro Oda', 4),   -- Correcta para la octava pregunta
                                                       ('Masashi Kishimoto', 4),
                                                       ('Akira Toriyama', 4),
                                                       ('Yoshihiro Togashi', 4);

-- Relación de question y answer
INSERT INTO question_answer (pregunta_id, respuesta_id, es_correcta) VALUES
 (5, 17, TRUE),
 (5, 18, FALSE),
 (5, 19, FALSE),
 (5, 20, FALSE),
 (6, 21, TRUE),
 (6, 22, FALSE),
 (6, 23, FALSE),
 (6, 24, FALSE),
 (7, 25, TRUE),
 (7, 26, FALSE),
 (7, 27, FALSE),
 (7, 28, FALSE),
 (8, 29, TRUE),
 (8, 30, FALSE),
 (8, 31, FALSE),
 (8, 32, FALSE),
 (9, 33, TRUE),
 (9, 34, FALSE),
 (9, 35, FALSE),
 (9, 36, FALSE),
 (10, 37, TRUE),
 (10, 38, FALSE),
 (10, 39, FALSE),
 (10, 40, FALSE),
 (11, 41, TRUE),
 (11, 42, FALSE),
 (11, 43, FALSE),
 (11, 44, FALSE),
 (12, 45, TRUE),
 (12, 46, FALSE),
 (12, 47, FALSE),
 (12, 48, FALSE);
