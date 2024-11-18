<?php

include_once './vendor/phpqrcode/qrlib.php';
class_alias('QRcode', 'PhpQRcode');
class QRHelper
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = "http://localhost/PreguntasYRespuestasPHP/ranking/profile";
    }

    public function generarQrParaUsuario($userId)
    {
        $url = $this->baseUrl . "?id=" . urlencode($userId);

        $filePath = "./img/profile/qr/usuario_" . $userId . ".png";

        PhpQRcode::png($url, $filePath, QR_ECLEVEL_L, 10, 2);

        return $filePath;
    }
}

