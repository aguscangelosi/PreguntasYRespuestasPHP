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

    public function game($category, $idUser, $idMatch){
        if ($idMatch == null) {
            $match = $this->createMatch($idUser);
        } else {
            $match = $this->findMatch($idUser, $idMatch);
        }
        $idMatch = $match['id'];

        $questionId = $this->addQuestionToMatch($idMatch, $category);

        $questionData = $this->getQuestionDetails($questionId);
        $questionData['idMatch'] = $idMatch;

        return $questionData;
    }

    public function createMatch($idUser)
    {
        $sql = "INSERT INTO game (estado) VALUES ('en curso')";
        $this->database->prepare($sql)->execute();

        $matchId = $this->database->insert_id();

        if (!$matchId) {
            throw new Exception("Error al crear la partida.");
        }

        $sql = "INSERT INTO user_game (user_id, partida_id, puntaje) VALUES (?, ?, 0)";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $idUser, $matchId);
        $stmt->execute();

        $sql = "SELECT * FROM game g
            JOIN user_game ug ON g.id = ug.partida_id
            WHERE g.id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $result = $stmt->get_result();

        $match = $result->fetch_assoc();
        if (!$match) {
            throw new Exception("No se encontró la partida creada.");
        }

        return $match;
    }

    public function findMatch($idUser, $idMatch)
    {
        $sql = "SELECT * FROM game g
            JOIN user_game ug ON g.id = ug.partida_id
            WHERE g.id = ? AND ug.user_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $idMatch, $idUser);
        $stmt->execute();

        $result = $stmt->get_result();


        $match = $result->fetch_assoc();

        return $match;
    }

    public function findUserMatch($idUser, $idMatch)
    {
        $sql = "SELECT * FROM user_game ug
                 WHERE ug.user_id = ? AND ug.partida_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $idUser, $idMatch);
        $stmt->execute();

        $result = $stmt->get_result();


        $match = $result->fetch_assoc();

        return $match;
    }

    public function addQuestionToMatch($matchId, $category)
    {
        $sql = "
    SELECT q.id 
    FROM question q
    WHERE q.categoria_id = ? 
      AND q.id NOT IN (
          SELECT pregunta_id 
          FROM game_question 
          WHERE partida_id = ?
      )
    ORDER BY RAND()
    LIMIT 1
    ";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $category, $matchId);
        $stmt->execute();
        $result = $stmt->get_result();

        $questionId = null;
        if ($row = $result->fetch_row()) {
            $questionId = $row[0];
        }

        if ($questionId) {
            $sql = "INSERT INTO game_question (partida_id, pregunta_id) VALUES (?, ?)";
            $stmt = $this->database->prepare($sql);
            $stmt->bind_param('ii', $matchId, $questionId);
            $stmt->execute();
        }

        return $questionId;
    }
    public function getQuestionDetails($questionId)
    {
        $sql = "
    SELECT q.id AS question_id, q.enunciado, q.dificultad, a.id AS answer_id, a.texto_respuesta, qa.es_correcta
    FROM question q
    LEFT JOIN question_answer qa ON q.id = qa.pregunta_id
    LEFT JOIN answer a ON qa.respuesta_id = a.id
    WHERE q.id = ?
    ";

        $stmt = $this->database->prepare($sql);

        $stmt->bind_param('i', $questionId);
        $stmt->execute();

        $result = $stmt->get_result();

        $question = null;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if (!$question) {
                    $question = [
                        'id' => $row['question_id'],
                        'enunciado' => $row['enunciado'],
                        'dificultad' => $row['dificultad'],
                        'respuestas' => []
                    ];
                }
                $question['respuestas'][] = [
                    'id' => $row['answer_id'],
                    'texto_respuesta' => $row['texto_respuesta'],
                    'es_correcta' => $row['es_correcta']
                ];
            }
        }

        return $question;
    }

    public function updateMatch($idUser, $idMatch)
    {
        $query = "UPDATE user_game SET puntaje = puntaje + 1 WHERE user_id = ? AND partida_id = ?";

        $stmt = $this->database->prepare($query);
        $stmt->bind_param("ii", $idUser, $idMatch);
        $stmt->execute();
    }

    public function answerCorrectOrNotCorrect()
    {
        $sql = "SELECT es_correcta FROM question_answer";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = mysqli_fetch_assoc($stmt->get_result());

        return $result['es_correcta'];
    }

    public function getRanking(){
        $sql = "SELECT DISTINCT u.id, u.username, ug.puntaje
               FROM user_game ug JOIN user u ON ug.user_id = u.id
               GROUP BY u.id 
               ORDER BY ug.puntaje DESC";

    $stmt = $this->database->prepare($sql);
    $stmt->execute();
        $result = $stmt->get_result();
        $resultrows = $result->fetch_all(MYSQLI_ASSOC);

        return $resultrows;
    }


}