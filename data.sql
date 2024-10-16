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
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id   INT,
    email VARCHAR(100) NOT NULL ,
    birthday DATE,
    name VARCHAR(60) NOT NULL ,
    profile_picture VARCHAR(255),
    register_date DATE,
    FOREIGN KEY (rol_id) REFERENCES rol (id)
);

INSERT INTO rol (description)
values ('admin');
INSERT INTO rol (description)
values ('user');

INSERT INTO user (username, password, rol_id, email, birthday, name, profile_picture, register_date)
VALUES
    ('jdoe', '12345', 2, 'jdoe@example.com', '1990-05-15', 'John Doe', 'profile1.jpg', '2024-01-10'),
    ('asmith', '12345', 2, 'asmith@example.com', '1985-07-22', 'Alice Smith', 'profile2.jpg', '2024-02-18'),
    ('bwilliams', '12345', 2, 'bwilliams@example.com', '1992-11-10', 'Bob Williams', 'profile3.jpg', '2024-03-12'),
    ('cmiller', '12345', 2, 'cmiller@example.com', '2000-03-30', 'Charlie Miller', 'profile4.jpg', '2024-04-05'),
    ('djohnson', '12345', 2, 'djohnson@example.com', '1998-09-12', 'Diana Johnson', 'profile5.jpg', '2024-05-20');
