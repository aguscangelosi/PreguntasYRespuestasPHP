<?php

class AdminModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtainReportedQuestions() {
        $sql = "SELECT q.id AS question_id, q.enunciado, q.dificultad, c.nombre_categoria, s.nombre_estado
            FROM question q 
            INNER JOIN status s ON q.estado_id = s.id 
            INNER JOIN category c ON q.categoria_id = c.id
            WHERE s.nombre_estado = 'reportada'";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $questions = ['reportedQuestions' => []]; // Inicializar con array vacío

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions['reportedQuestions'][] = [
                    'question_id' => $row['question_id'],
                    'enunciado' => $row['enunciado'],
                    'dificultad' => $row['dificultad'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'nombre_estado' => $row['nombre_estado']
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
        $sql = "INSERT INTO question (enunciado, dificultad, categoria_id, estado_id, activo) VALUES (?, ?, ?, 2, 1)";
        $stmt = $this->database->prepare($sql);
        $dificultad = "Media";
        $stmt->bind_param("ssi", $question, $dificultad, $category);
        $stmt->execute();

        $questionId = $stmt->insert_id;
        $stmt->close();

        $sql = "INSERT INTO answer (texto_respuesta, categoria_id) VALUES (?, ?)";
        $stmt = $this->database->prepare($sql);

        $respuestas = [$correctAnswer, $answer2, $answer3, $answer4];
        $categoria = $category;
        $respuestaIds = [];

        foreach ($respuestas as $index => $respuesta) {
            $stmt->bind_param("si", $respuesta, $categoria);
            $stmt->execute();

            $respuestaIds[] = $stmt->insert_id;
        }
        $stmt->close();

        $sql = "INSERT INTO question_answer (pregunta_id, respuesta_id, es_correcta) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($sql);

        foreach ($respuestaIds as $index => $respuestaId) {
            $esCorrecta = ($index === 0);

            $stmt->bind_param("iii", $questionId, $respuestaId, $esCorrecta);
            $stmt->execute();
        }
        $stmt->close();
    }

    function filterSuggestedQuestion()
    {
        $sql = "SELECT * FROM question q
                WHERE q.activo = 0";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $suggestedQuestion = $stmt->get_result();

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