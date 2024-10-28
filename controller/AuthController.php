<?php
include_once("./helper/EmailSender.php");

class AuthController
{

    private $model;
    private $presenter;
    private $mail;

    private $authHelper;

    public function __construct($model, $presenter, $authHelper)
    {
        $this->mail = new MailService(true);
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
    }

    public function init()
    {
        $this->presenter->show('register');
    }

    public function initLogin()
    {
        $this->presenter->show('login');
    }

    public function register()
    {

        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $repeatPassword = $_POST['repeat_password'];
        $birthday = $_POST['birthday'];
        $username = $_POST['username'];

        if ($repeatPassword == $password) {
            $result = $this->model->register($name, $email, $password, $birthday, $username);
        } else {
            $result = "Las contraseñas no coinciden";
        }

        if ($result == "" || !$result) {
            $this->presenter->show('register', ['error_message' => $result]);
        } else {
            $this->mail->sendMail($email, "Validacion de correo", "<a href='localhost/PreguntasYRespuestasPHP/auth/validateEmail?id=$result'>Validar correo</a>");
            $this->redirectHome();
        }
    }

    public function login()
    {
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $password = $_POST['password'];
            $username = $_POST['username'];

            $user = $this->model->login($username, $password);
            $hasAccess = $this->authHelper->loginUser($user);
            if ($user && $hasAccess) {
                header('location: /PreguntasYRespuestasPHP/game/lobby');
            }
            if (!$hasAccess) {
                $data["has_access"] = "Debe verificar su mail.";
                $this->presenter->show('login',$data);
            }
            else {
                $this->presenter->show('register');
            }
        }
    }

    public function play(){
        if(isset($_POST['click'])){
            $this->presenter->show('roulette');
        }else{
            $this->presenter->show('notFoundView');
        }
    }

    public function validateEmail()
    {
        if (isset($_GET['id'])) {
            $this->model->allowUser($_GET['id']);
            $this->presenter->show('login', ['validacion' => 'Correcta validación']);
        }
    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }


}