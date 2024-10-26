<?php

class GameController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function play()
    {
        $this->presenter->show('roulette');
    }



    public function findQuestions()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $idUser = isset($_GET['id']) ? $_GET['id'] : null;
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null; // ID de partida recibido

        if (!$category) {
            $this->presenter->show('notFound');
            return;
        }

        $questions = $this->model->game($category,$idUser,$idMatch);

        $this->presenter->show('question', $questions);
    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }
}