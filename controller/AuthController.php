<?php

class AuthController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function init()
    {
        $this->presenter->show('register');
    }
    public function register()
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $birthday = $_POST['birthday'];
        $username = $_POST['username'];

        $result = $this->model->register($name, $email, $password, $birthday, $username);

        if ($result !== true) {
            $this->presenter->show('register', ['error_message' => $result]);
        } else {
            $this->redirectHome();
        }
    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }

}