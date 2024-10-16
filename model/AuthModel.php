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
        if (!$this->validateEmail($email)) {
            return;
        }

        $stmt = $this->database->prepare("INSERT INTO user (username, password, rol_id, email, birthday, name, profile_picture, register_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssissss', $username, $password, 2, $email, $birthday, $name, "" );
        $stmt->execute();

        $sql = ";";
        $this->database->execute($sql);
    }



}