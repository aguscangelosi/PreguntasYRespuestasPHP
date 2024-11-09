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

}