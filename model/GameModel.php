<?php

class GameModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function dropCategory(){
        $sql = "SELECT * FROM category ORDER BY RAND() LIMIT 1";

        $category = $this->database->query($sql);

        return $category;
    }

    public function findQuestions($category) {
        $sql = "
        SELECT q.id AS question_id, q.enunciado, q.dificultad, a.id AS answer_id, a.texto_respuesta, qa.es_correcta
        FROM question q
        LEFT JOIN question_answer qa ON q.id = qa.pregunta_id
        LEFT JOIN answer a ON qa.respuesta_id = a.id
        WHERE q.categoria_id = '$category'
        ORDER BY RAND()
    ";

        $result = $this->database->query($sql);

        if (!empty($result)) {
            $question = [
                'id' => $result[0]['question_id'],
                'enunciado' => $result[0]['enunciado'],
                'dificultad' => $result[0]['dificultad'],
                'respuestas' => []
            ];

            foreach ($result as $row) {
                $question['respuestas'][] = [
                    'id' => $row['answer_id'],
                    'texto_respuesta' => $row['texto_respuesta'],
                    'es_correcta' => $row['es_correcta']
                ];
            }

            return $question;
        }

        return null;
    }



}