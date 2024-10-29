<?php

class GameController
{

    private $model;
    private $presenter;

    private $authHelper;

    public function __construct($model, $presenter,$authHelper)
    {
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
    }

    public function play()
    {
        $this->presenter->show('roulette');
    }

    public function playAgain()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["id"];
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] :  null;

        if (!$userId || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }

        $this->model->updateMatch($userId, $idMatch);
        $data['id'] = $userId;
        $data['idMatch'] = $idMatch;
        $this->presenter->show('roulette', $data);
    }

    public function finish()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["id"];
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] :  null;

        if (!$userId || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }
//        $this->model->updateMatch($idUser, $idMatch); //TODO falta cambiar el estado
        $match = $this->model->findUserMatch($userId, $idMatch);
        $this->presenter->show('finishGame', $match);

    }


    public function findQuestions()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;

        $user = $this->authHelper->getUser();
        $userId = $user["id"];

        if (!$category) {
            $this->presenter->show('notFound');
            return;
        }

        $questions = $this->model->game($category, $userId, $idMatch);

        $questions["idUser"] = $userId;

        $this->presenter->show('question', $questions);
    }

    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/index.php');
        exit();
    }


    public function lobby()
    {
        $this->presenter->show('lobby');
    }

}