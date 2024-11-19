<?php

class QuestionService
{
    private $database;
    private $authHelper;

    public function __construct($database, $authHelper)
    {
        $this->database = $database;
        $this->authHelper = $authHelper;
    }

    public function insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category)
    {
        $rolId = $this->authHelper->getRolId();
        $estadoActivo = ($rolId == 3) ? 1 : 0;
        $status = ($rolId == 3) ? 2 : 1;

        $sql = "INSERT INTO question (enunciado, dificultad, categoria_id, estado_id, activo) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->database->prepare($sql);
        $dificultad = "Facil";
        $stmt->bind_param("ssiii", $question, $dificultad, $category, $status, $estadoActivo);
        $stmt->execute();

        $questionId = $stmt->insert_id;
        $stmt->close();

        $sql = "INSERT INTO answer (texto_respuesta, categoria_id) VALUES (?, ?)";
        $stmt = $this->database->prepare($sql);
        $respuestas = [$correctAnswer, $answer2, $answer3, $answer4];
        $respuestaIds = [];

        foreach ($respuestas as $respuesta) {
            $stmt->bind_param("si", $respuesta, $category);
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

        $sql2 = "INSERT INTO statistics_admin (question_id) VALUES (?)";
        $stmt2 = $this->database->prepare($sql2);
        $stmt2->bind_param("i", $questionId);
        $stmt2->execute();
        $stmt2->close();

        return $questionId;
    }
}
