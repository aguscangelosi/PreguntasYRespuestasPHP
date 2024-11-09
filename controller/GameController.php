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
        $userId = $user["user_id"];
        $idMatch = isset($_POST['idMatch']) ? $_POST['idMatch'] : null;
        $data = $this->model->getMatch($idMatch, $userId);

        $this->presenter->show('roulette', ['idMatch' => $data['id']]);
    }

//    public function playAgain()
//    {
//        $user = $this->authHelper->getUser();
//        $userId = $user["user_id"];
//        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;
//
//        if (!$userId || !$idMatch) {
//            $this->presenter->show('notFound');
//            return;
//        }
//
//        $data['id'] = $userId;
//        $data['idMatch'] = $idMatch;
//        $this->presenter->show('roulette', $data);
//    }

    public function finish()
    {
        $user = $this->authHelper->getUser();
        $userId = $user["user_id"];
        $idMatch = isset($_POST['idMatch']) ? $_POST['idMatch'] : null;

        if (!$userId || !$idMatch) {
            $this->presenter->show('notFound');
            return;
        }
        $puntaje = $this->model->findUserMatch($userId, $idMatch);
        $this->presenter->show('finishGame', ["puntaje" => $puntaje]);

    }


    public function findQuestions()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $idMatch = isset($_GET['idMatch']) ? $_GET['idMatch'] : null;

        $user = $this->authHelper->getUser();
        $userId = $user["user_id"];

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
        $user = $this->authHelper->getUser();
        $username = $user["username"];
        $this->presenter->show('lobby', ['username' => $username]);
    }

    public function sendQuestion()
    {
        $user = $this->authHelper->getUser();
        $idUser = $user["user_id"];
        $idMatch = $_POST['idMatch'];
        $idQuestion = $_POST['idQuestion'];
        $idResponse = $_POST['idResponse'];

        $result = $this->model->validateResponse($idUser, $idMatch, $idQuestion, $idResponse);

        if (isset($result['timeout']) && $result['timeout'] === true) {
            echo json_encode([
                'error' => 'timeout',
                'message' => 'El tiempo para responder ha expirado.'
            ]);
        } elseif ($result['isCorrect']) {
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

    public function reportQuestion()
    {
        $questionId = isset($_POST['question_id']) ? (int) $_POST['question_id'] : null;
        $description = isset($_POST['description']) ? $_POST['description'] : '';


        if (!$questionId || empty($description)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos'
            ]);
            return;
        }

        if ($this->model->reportQuestion($questionId, $description)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Reporte insertado exitosamente.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al insertar el reporte']);
        }

        exit;
    }

}
