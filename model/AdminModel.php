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
}