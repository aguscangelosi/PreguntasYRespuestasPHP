<?php

class GameModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function dropCategory()
    {
        $sql = "SELECT * FROM category ORDER BY RAND() LIMIT 1";

        $category = $this->database->query($sql);

        return $category;
    }

    public function getMatch($idMatch, $idUser)
    {
        if ($idMatch == null) {
            $match = $this->createMatch($idUser);
        } else {
            $match = $this->findMatch($idUser, $idMatch);
        }
        return $match;
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

    public function game($category, $idUser, $idMatch)
    {
        $match = $this->findMatch($idUser, $idMatch);
        $idMatch = $match['id'];

        $pendingQuestion = $this->getPendingQuestion($idMatch, $idUser);

        if ($pendingQuestion) {
            $pendingQuestion['idMatch'] = $idMatch;
            return $pendingQuestion;
        }

        $questionId = $this->addQuestionToMatch($idMatch, $category);

        $this->updateUserGameQuestion($idMatch, $idUser, $questionId, "pendiente");

        $questionData = $this->getQuestionDetails($questionId);
        $questionData['idMatch'] = $idMatch;

        return $questionData;
    }

    public function getPendingQuestion($matchId, $userId)
    {
        $sql = "
    SELECT DISTINCT q.id AS question_id, q.enunciado, q.dificultad, a.id AS answer_id, a.texto_respuesta, qa.es_correcta
    FROM user_game ug
    JOIN game_question gq ON ug.ultima_pregunta_id = gq.pregunta_id
    JOIN question q ON gq.pregunta_id = q.id
    LEFT JOIN question_answer qa ON q.id = qa.pregunta_id
    LEFT JOIN answer a ON qa.respuesta_id = a.id
    WHERE ug.partida_id = ? AND ug.user_id = ? AND ug.estado_pregunta = 'pendiente'
    ";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $matchId, $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $question = null;
        if ($result) {
            $addedAnswers = []; // Usaremos esto para rastrear respuestas ya añadidas
            while ($row = $result->fetch_assoc()) {
                if (!$question) {
                    $question = [
                        'question_id' => $row['question_id'],
                        'enunciado' => $row['enunciado'],
                        'dificultad' => $row['dificultad'],
                        'respuestas' => []
                    ];
                }
                // Verificamos si la respuesta ya fue añadida usando answer_id
                if (!in_array($row['answer_id'], $addedAnswers)) {
                    $question['respuestas'][] = [
                        'answer_id' => $row['answer_id'],
                        'texto_respuesta' => $row['texto_respuesta'],
                        'es_correcta' => $row['es_correcta']
                    ];
                    $addedAnswers[] = $row['answer_id'];
                }
            }
        }

        return $question;
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

    public function updateUserGameQuestion($matchId, $userId, $questionId, $statusQuestion)
    {
        $sql = "
        UPDATE user_game 
        SET ultima_pregunta_id = ?, 
            estado_pregunta = ?, 
            fecha_respuesta = NOW()
        WHERE partida_id = ? 
          AND user_id = ?
    ";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('isii', $questionId, $statusQuestion, $matchId, $userId);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
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
                        'question_id' => $row['question_id'],
                        'enunciado' => $row['enunciado'],
                        'dificultad' => $row['dificultad'],
                        'respuestas' => []
                    ];
                }
                $question['respuestas'][] = [
                    'answer_id' => $row['answer_id'],
                    'texto_respuesta' => $row['texto_respuesta'],
                    'es_correcta' => $row['es_correcta']
                ];
            }
        }

        return $question;
    }

    public function findUserMatch($idUser, $idMatch)
    {
        $sql = "SELECT ug.puntaje FROM user_game ug
                 WHERE ug.user_id = ? AND ug.partida_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $idUser, $idMatch);
        $stmt->execute();

        $result = $stmt->get_result();


        $match = $result->fetch_assoc();

        return $match["puntaje"];
    }

    public function updateMatch($idUser, $idMatch)
    {
        $query = "UPDATE user_game SET puntaje = puntaje + 1 WHERE user_id = ? AND partida_id = ?";

        $stmt = $this->database->prepare($query);
        $stmt->bind_param("ii", $idUser, $idMatch);
        $stmt->execute();
    }

    public function validateResponse($idUser, $idMatch, $idQuestion, $idResponse)
    {
        $sqlCorrectAnswer = "SELECT respuesta_id FROM question_answer WHERE pregunta_id = ? AND es_correcta = ?";
        $stmt = $this->database->prepare($sqlCorrectAnswer);
        $true = 1;
        $stmt->bind_param('is', $idQuestion, $true);
        $stmt->execute();
        $correctAnswer = $stmt->get_result()->fetch_assoc();
        $correctAnswerId = $correctAnswer["respuesta_id"];
        $stmt->close();

        if ($idResponse == $correctAnswerId) {
            $sqlUpdateScore = "UPDATE user_game SET puntaje = puntaje + 10, estado_pregunta = 'respondida' WHERE user_id = ? AND partida_id = ?";
            $stmt = $this->database->prepare($sqlUpdateScore);
            $stmt->bind_param('ii', $idUser, $idMatch);
            $stmt->execute();

            $sqlGameQuestion = "UPDATE game_question SET es_correcta = ? WHERE partida_id = ? AND pregunta_id = ?";
            $stmt = $this->database->prepare($sqlGameQuestion);
            $true = 1;
            $stmt->bind_param('sii', $true, $idMatch, $idQuestion);
            $stmt->execute();

            $sqlUserQuestion = "INSERT IGNORE INTO user_question (user_id, question_id) VALUES (?, ?)";
            $stmt = $this->database->prepare($sqlUserQuestion);
            $stmt->bind_param('ii', $idUser, $idQuestion);
            $stmt->execute();
            $stmt->close();

            return [
                'correctAnswerId' => $correctAnswerId,
                'isCorrect' => true
            ];
        } else {
            $sqlEndGame = "UPDATE game SET estado = 'finalizada', fecha_fin = NOW() WHERE id = ?";
            $stmt = $this->database->prepare($sqlEndGame);
            $stmt->bind_param('i', $idMatch);
            $stmt->execute();

            $sqlUpdateScore = "UPDATE user_game SET estado_pregunta = 'respondida' WHERE user_id = ? AND partida_id = ?";
            $stmt = $this->database->prepare($sqlUpdateScore);
            $stmt->bind_param('ii', $idUser, $idMatch);
            $stmt->execute();

            $sqlGetScore = "SELECT puntaje FROM user_game WHERE user_id = ? AND partida_id = ?";
            $stmt = $this->database->prepare($sqlGetScore);
            $stmt->bind_param('ii', $idUser, $idMatch);
            $stmt->execute();
            $score = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return [
                'correctAnswerId' => $correctAnswerId,
                'isCorrect' => false,
                'score' => $score['puntaje']
            ];
        }
    }
}