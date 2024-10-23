<?php

include_once './vendor/PHPMailer/src/PHPMailer.php';
include_once './vendor/PHPMailer/src/SMTP.php';
include_once './vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;

        $this->mail->Username = 'facundortega1234@gmail.com';
        $this->mail->Password = 'tyjc nybr ypbn bbpw';

        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->SMTPDebug = 2;  // 2 para mensajes detallados de cliente y servidor
    }

    public function sendMail($to, $subject, $body) {
        try {
            $this->mail->setFrom('facundortega1234@gmail.com', 'Solomeo');
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return 'Error: ' . $this->mail->ErrorInfo;
        }
    }
}