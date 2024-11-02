<?php

class GameController
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

    public function play()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["id"];
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;
        $data = $this->model->getMatch($idMatch, $userId);

        $this->presenter->show('roulette', ['idMatch' => $data['id']]);
    }

    public function playAgain()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["id"];
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;

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
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;

        if (!$userId || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }
//        $this->model->updateMatch($idUser, $idMatch); //TODO falta cambiar el estado
        $puntaje = $this->model->findUserMatch($userId, $idMatch);
        $this->presenter->show('finishGame', ["puntaje" => $puntaje]);

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
        $questions["idMatch"] = $idMatch;
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

    public function sendQuestion()
    {
        $user = $this->authHelper->getUser();
        $idUser = $user["id"];
        $idMatch = $_POST['idMatch'];
        $idQuestion = $_POST['idQuestion'];
        $idResponse = $_POST['idResponse'];

        $result = $this->model->validateResponse($idUser, $idMatch, $idQuestion, $idResponse);

        if ($result['isCorrect']) {
            echo json_encode([
                'correct' => true,
                'answer_id' => $result['correctAnswerId']
            ]);
        } else {
            echo json_encode([
                'correct' => false,
                'answer_id' => $result['correctAnswerId'],
                'score' => $result['score']
            ]);
        }

        exit;
    }


}