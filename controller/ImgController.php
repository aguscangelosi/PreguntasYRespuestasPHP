<?php

class ImgController
{

    private $model;
    private $presenter;
    private $authHelper;

    public function __construct($model, $presenter, $authHelper)
    {
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
    }

    public function profile()
    {
        $idUser = $_GET['id'];

        $imagePath = "/PreguntasYRespuestasPHP/img/profile/usuario_" . $idUser . ".png";

        if (file_exists($imagePath)) {
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($imagePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            readfile($imagePath);
            exit;
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "Imagen no encontrada";
            exit;
        }
    }
}