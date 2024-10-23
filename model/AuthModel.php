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

    public function validatePassword($password)
    {
        return preg_match('/^(?=.*[A-Z])(?=.*[\W])(?=.{8,})/', $password);
    }

    private function validateAge($birthday)
    {
        $birthDate = new DateTime($birthday);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;

        return $age >= 13;
    }
    public function register($name, $email, $password, $birthday, $username)
    {
        if ($this->validateEmail($email)) {
            return "Usuario ya registrado";
        }

        if (!$this->validatePassword($password)) {
            return "Contraseña inválida";
        }

        if (!$this->validateAge($birthday)) {
            return "Eres muy pequeñ@";
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

            $lastId = $this->database->insert_id();
            return $lastId;
        } catch (Exception $e) {
            return $e->getMessage();

        } finally {
            if ($stmt !== null) {
                $stmt->close();
            }
        }
    }

    public function login($username, $password) {
        // Consulta con un placeholder para evitar inyección SQL
        $sql = "SELECT * FROM user WHERE username = ?";

        $stmt = $this->database->prepare($sql);
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->database->error);
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }

        // Si no coincide la contraseña o el usuario no existe, retorna null
        return null;
    }

}