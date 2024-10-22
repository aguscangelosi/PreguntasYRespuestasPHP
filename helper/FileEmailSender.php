<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once './vendor/PHPMailer/src/PHPMailer.php';
include_once './vendor/PHPMailer/src/SMTP.php';
include_once './vendor/PHPMailer/src/Exception.php';

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);  // Habilitar excepciones
        $this->mail->isSMTP();              // Usar SMTP
        $this->mail->Host = 'smtp.gmail.com';  // Cambiar según tu proveedor SMTP
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'psolomeo2000@gmail.com'; // Tu correo
        $this->mail->Password = 'PSolomeo2000__';     // Tu contraseña
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
    }

    public function sendMail($to, $subject, $body) {
        try {
            // Configuración del correo
            $this->mail->setFrom('psolomeo2000@gmail.com', 'Solomeo');
            $this->mail->addAddress('facundortega1234@gmail.com');
            $this->mail->isHTML(true);
            $this->mail->Subject = "Validar password";
            $this->mail->Body    = "Texto de prueba";

            // Enviar correo
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return 'Error: ' . $this->mail->ErrorInfo;
        }
    }
}