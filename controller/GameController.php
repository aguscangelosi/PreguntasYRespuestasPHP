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

    public function playAgain()
    {
        $idUser = isset($_GET['id']) ? $_GET['id'] : null;
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] :  null;

        if (!$idUser || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }

        $this->model->updateMatch($idUser, $idMatch);
        $data['id'] = $idUser;
        $data['idMatch'] = $idMatch;
        $this->presenter->show('roulette', $data);
    }

    public function finish()
    {
        $idUser = isset($_GET['id']) ? $_GET['id'] : null;
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] :  null;

        if (!$idUser || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }
//        $this->model->updateMatch($idUser, $idMatch); //TODO falta cambiar el estado
        $match = $this->model->findUserMatch($idUser, $idMatch);
        $this->presenter->show('finishGame', $match);

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

        $questions = $this->model->game($category, $idUser, $idMatch);

        $this->presenter->show('question', $questions);
    }

    public function answerCorrectOrNotCorrect()
    {
        $result = $this->model->answerCorrectOrNotCorrect(); //La idea es que traiga true o false
        if ($result) {
            $this->presenter->show('roulette');
        } else {
            $this->presenter->show('gameLost');
        }

    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }
}