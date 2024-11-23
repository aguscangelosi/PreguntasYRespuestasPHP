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
    public function register($name, $sex, $email, $password, $birthday, $username, $pais, $ciudad, $picture)
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
            $url = $this->savePicture( $picture);
            $stmt = $this->database->prepare("INSERT INTO user (username, sex, password, rol_id, email, birthday, name, profile_picture, register_date, pais, ciudad)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta");
            }

            $rolId = 2;

            $stmt->bind_param('sssissssss', $username, $sex, $hashedPassword, $rolId, $email, $birthday, $name, $url, $pais, $ciudad);
            $this->counterUsers();
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
        //TODO IMPLEMENTAR INICIO DE SESION CON EMAIL O CON USERNAME
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

        return null;
    }

    public function allowUser($id) {
        $sql = "UPDATE user SET hasAccess = ? WHERE id = ?";
        try {
            $stmt = $this->database->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->database->error);
            }

            $true = 1;
            $stmt->bind_param('si', $true, $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return true;
            } else {
                return "No se actualizó ningún registro. Verifica que el ID exista.";
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }

    }

    public function savePicture($picture)
    {
        if (!$picture) {
            throw new Exception("No se recibió ningún archivo.");
        }

        $fileExtension = strtolower(pathinfo($picture['name'], PATHINFO_EXTENSION));
        $validExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        if (!in_array($fileExtension, $validExtensions)) {
            throw new Exception("Extensión de archivo no válida: " . $fileExtension);
        }

        $id = $this->uuid();
        $base = "/PreguntasYRespuestasPHP";
        $url = "/img/profile/profile_" . $id . "." . $fileExtension;
        $filePath = "." . $url ;

        $directory = dirname($filePath);
        if (!is_writable($directory)) {
            throw new Exception("El directorio no es escribible: " . $directory);
        }

        if (!move_uploaded_file($picture['tmp_name'], $filePath)) {
            throw new Exception("Error al mover el archivo desde " . $picture['tmp_name'] . " a " . $filePath);
        }

        return $base . $url;
    }

    function uuid() {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function counterUsers()
    {
        $sql = "SELECT id 
        FROM user 
        ORDER BY id DESC 
        LIMIT 1"; // Asegúrate de limitar la consulta a un solo resultado
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($lastID); // Vincula la variable para capturar el resultado
        $stmt->fetch(); // Obtén el valor del último ID
        $stmt->close();

        $sql2 = "INSERT INTO statistics_admin (user_id) VALUES (?)";
        $stmt2 = $this->database->prepare($sql2);
        $stmt2->bind_param("i", $lastID);
        $stmt2->execute();
        $stmt2->close();
    }
   

}