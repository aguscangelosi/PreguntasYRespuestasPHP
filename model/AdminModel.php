<?php

class AdminModel
{
    private $database;
    private $questionService;

    public function __construct($database, $questionService)
    {
        $this->database = $database;
        $this->questionService = $questionService;
    }

    public function obtainReportedQuestions() {
        $sql = "SELECT qr.question_id AS question_id, qr.description, q.enunciado, q.dificultad, c.nombre_categoria
        FROM question_report qr 
        JOIN question q ON q.id = qr.question_id
        JOIN category c ON q.categoria_id = c.id";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = ['reportedQuestions' => []];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions['reportedQuestions'][] = [
                    'question_id' => $row['question_id'],
                    'enunciado' => $row['enunciado'],
                    'dificultad' => $row['dificultad'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'descripcion' => $row['description'],
                ];
            }
        }

        return $questions;
    }

    public function findCategories(){
        $sql = "SELECT * FROM category c";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $categories = $stmt->get_result();

        return $categories;
    }

    public function insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category) {
        return $this->questionService->insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category);
    }

    function filterSuggestedQuestion()
    {
//        $sql = "SELECT * FROM question q JOIN question_answer
//                WHERE q.activo = 0 AND q.estado_id = 1";
//        $stmt = $this->database->prepare($sql);
//        $stmt->execute();
//        $suggestedQuestion = $stmt->get_result();
        $sql = "SELECT q.id, ca.nombre_categoria, s.nombre_estado, q.enunciado, q.dificultad, q.categoria_id, 
                q.estado_id, q.activo, a.texto_respuesta FROM question q
                JOIN category ca ON ca.id = q.categoria_id
                JOIN status s ON s.id = q.estado_id
                JOIN question_answer qa ON q.id = qa.pregunta_id
                JOIN answer a ON qa.respuesta_id = a.id
                WHERE q.activo = 0 AND q.estado_id = 1;";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $suggestedQuestion = $stmt->get_result();
        $suggestedQuestion = $suggestedQuestion->fetch_assoc();

        return $suggestedQuestion;
    }

    function deleteQuestion($idQuestion)
    {
    $sql ="UPDATE question
        SET estado_id = 3, activo = 0  
        WHERE id = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idQuestion);
        $stmt->execute();

        $questionDelete = $stmt->get_result();
        $stmt->close();

        return $questionDelete;
    }

    function approveQuestion($idQuestion)
    {
        $sql ="UPDATE question
        SET estado_id = 2, activo = 1  
        WHERE id = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idQuestion);
        $stmt->execute();

        $questionApprove = $stmt->get_result();
        $stmt->close();

        return $questionApprove;
    }

    function approveReport($idQuestion)
    {
        // Elimina primero los registros relacionados
        $stmt = $this->database->prepare("DELETE FROM question_report WHERE question_id = ?");
        $stmt->bind_param("i", $idQuestion);
        $stmt->execute();
        $stmt->close();

        $stmt1 = $this->database->prepare("DELETE FROM question_answer WHERE pregunta_id = ?");
        $stmt1->bind_param("i", $idQuestion);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $this->database->prepare("DELETE FROM user_game WHERE ultima_pregunta_id  = ?");
        $stmt2->bind_param("i", $idQuestion);
        $stmt2->execute();
        $stmt2->close();

        $stmt3 = $this->database->prepare("DELETE FROM game_question WHERE pregunta_id = ?");
        $stmt3->bind_param("i", $idQuestion);
        $stmt3->execute();
        $stmt3->close();

        $stmt4 = $this->database->prepare("DELETE FROM question WHERE id = ?");
        $stmt4->bind_param("i", $idQuestion);
        $stmt4->execute();
        $stmt4->close();
        return true;
    }

    public function declineReport($idQuestion)
    {
        $this->approveQuestion($idQuestion);
        $stmt = $this->database->prepare("DELETE FROM question_report WHERE question_id = ?");
        $stmt->bind_param("i", $idQuestion);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    public function findPlayers()
    {
        $sql = "SELECT COUNT FROM user";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $players = $stmt->get_result();
    }

    public function findMatchesPlayed()
    {
        $sql = "SELECT COUNT FROM game";
    }

}