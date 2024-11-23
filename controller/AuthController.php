<?php
include_once("./helper/EmailSender.php");

class AuthController
{

    private $model;
    private $presenter;
    private $mail;
    private $authHelper;
    private $qrHelper;

    public function __construct($model, $presenter, $authHelper, $qrHelper)
    {
        $this->mail = new MailService(true);
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
        $this->qrHelper = $qrHelper;
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
        $sex = $_POST['sex'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $repeatPassword = $_POST['repeat_password'];
        $birthday = $_POST['birthday'];
        $username = $_POST['username'];
        $pais = $_POST['pais'];
        $ciudad = $_POST['ciudad'];

        if ($repeatPassword !== $password) {
            $errorMessage = "Las contraseñas no coinciden";
            $this->presenter->show('register', ['error_message' => $errorMessage]);
            return;
        }

        $result = $this->model->register($name, $sex, $email, $password, $birthday, $username, $pais, $ciudad);


        if (is_string($result)) {
            $this->presenter->show('register', ['error_message' => $result]);
        } else {
            $algo = $this->qrHelper->generarQrParaUsuario($result);
            $this->mail->sendMail($email, "Validación de correo", "<a href='localhost/PreguntasYRespuestasPHP/auth/validateEmail?id=$result'>Validar correo</a>");
            $this->redirectHome();
        }
    }

    public function login()
    {
        $data = [];

        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            $user = $this->model->login($_POST['username'], $_POST['password']);

            if ($user) {
                if ($this->authHelper->loginUser($user)) {
                    $this->qrHelper->generarQrParaUsuario($user["id"]);
                    header('location: /PreguntasYRespuestasPHP/game/lobby');
                    exit;
                }
                $data["error_message"] = "Debe verificar su mail.";
            } else {
                $data["error_message"] = "Usuario o contraseña incorrectos";
            }
        }
        $this->presenter->show('login', $data);
    }


    public function validateEmail()
    {
        if (isset($_GET['id'])) {
            $this->model->allowUser($_GET['id']);
            $this->presenter->show('login', ['validacion' => 'Correcta validación']);
        }
    }


    public function logout()
    {
        $this->authHelper->logout();
        $this->redirectHome();
    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/auth/initLogin');
        exit();
    }


}