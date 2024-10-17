<?php

class AuthModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

//    public function validate($user, $pass)
//    {
//        $sql = "SELECT 1
//                FROM user
//                WHERE username = '" . $user . "'
//                AND password = '" . $pass . "'";
//
//        $usuario = $this->database->query($sql);
//
//        return sizeof($usuario) == 1;
//    }


    public function validateEmail($email)
    {
        $sql = "SELECT 1 
                FROM user 
                WHERE email = '" . $email . "'";

        $usuario = $this->database->query($sql);

        return sizeof($usuario) == 1;
    }

    public function register($name, $email, $password, $birthday, $username)
    {
        if ($this->validateEmail($email)) {
            return "Usuario ya registrado";
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = null;

        try {
            $stmt = $this->database->prepare("INSERT INTO user (username, password, rol_id, email, birthday, name, profile_picture, register_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta");
            }

            $rolId = 2;
            $profilePicture = "default.png";

            $stmt->bind_param('ssissss', $username, $hashedPassword, $rolId, $email, $birthday, $name, $profilePicture);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear el usuario");
            }

            return true;
        } catch (Exception $e) {
            return $e->getMessage();

        } finally {
            if ($stmt !== null) {
                $stmt->close();
            }
        }
    }


}