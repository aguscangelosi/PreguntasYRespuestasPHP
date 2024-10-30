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

    public function sendQuestion()
    {
        $user = $this->authHelper->getUser();
        $idUser = $user["id"];
        $idMatch = $_POST['idMatch'];
        $idQuestion = $_POST['idQuestion'];
        $idResponse = $_POST['idResponse'];

        // Validar la respuesta y obtener el resultado
        $result = $this->model->validateResponse($idUser, $idMatch, $idQuestion, $idResponse);

        // Preparar la respuesta JSON
        if ($result === true) {
            // Respuesta correcta
            echo json_encode(['correct' => true]);
        } else {
            // Respuesta incorrecta y partida terminada, incluye puntaje final
            echo json_encode(['correct' => false, 'score' => $result]);
        }

        exit; // Finalizar para evitar renderizado adicional
    }


}