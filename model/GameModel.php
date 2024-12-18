<?php

class GameModel
{
    private $database;

    private $questionService;

    public function __construct($database, $questionService)
    {
        $this->database = $database;
        $this->questionService = $questionService;
    }

    public function getMatch($idMatch, $idUser)
    {
        return $idMatch ? $this->findMatch($idUser, $idMatch) : $this->createMatch($idUser);
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

        $questionId = $this->addQuestionToMatch($idMatch, $category, $idUser);

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
    WHERE ug.partida_id = ? AND ug.user_id = ? AND ug.estado_pregunta = 'pendiente' AND q.activo = 1;
    ";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $matchId, $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $question = null;
        if ($result) {
            $addedAnswers = [];
            while ($row = $result->fetch_assoc()) {
                if (!$question) {
                    $question = [
                        'question_id' => $row['question_id'],
                        'enunciado' => $row['enunciado'],
                        'dificultad' => $row['dificultad'],
                        'respuestas' => []
                    ];
                }
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

        if ($match && $match['estado'] === 'en curso') {
            return $match;
        }

        return null;
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

    public function addQuestionToMatch($matchId, $category, $idUser)
    {
        // Verificar cuántas preguntas ha respondido el usuario
        $sqlUserStats = "SELECT preguntas_respondidas FROM user WHERE id = ?";
        $stmt = $this->database->prepare($sqlUserStats);
        $stmt->bind_param('i', $idUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $userStats = $result->fetch_assoc();
        $questionsAnswered = $userStats['preguntas_respondidas'] ?? 0;

        // Si respondió menos de 10 preguntas, seleccionar aleatoriamente
        if ($questionsAnswered < 10) {
            $sqlRandom = "
        SELECT q.id 
        FROM question q
        WHERE q.categoria_id = ? 
          AND q.id NOT IN (
              SELECT pregunta_id 
              FROM game_question 
              WHERE partida_id = ?
          )
        ORDER BY RAND()
        LIMIT 1";

            $stmt = $this->database->prepare($sqlRandom);
            $stmt->bind_param('ii', $category, $matchId);
            $stmt->execute();
            $result = $stmt->get_result();
            $questionId = $result->fetch_row()[0] ?? null;
        } else {
            // Después de 10 preguntas, aplicar lógica de dificultad

            // Obtener el nivel del usuario (fácil, normal o difícil)
            $sqlUserLevel = "
        SELECT CASE 
            WHEN preguntas_correctas / GREATEST(preguntas_respondidas, 1) < 0.33 THEN 'facil'
            WHEN preguntas_correctas / GREATEST(preguntas_respondidas, 1) BETWEEN 0.33 AND 0.66 THEN 'normal'
            ELSE 'dificil'
        END AS nivel
        FROM user
        WHERE id = ?";
            $stmt = $this->database->prepare($sqlUserLevel);
            $stmt->bind_param('i', $idUser);
            $stmt->execute();
            $result = $stmt->get_result();
            $userLevel = $result->fetch_assoc()['nivel'];

            // Filtrar preguntas basadas en la dificultad del usuario
            $sqlDifficulty = "
        SELECT q.id 
        FROM question q
        WHERE q.categoria_id = ? 
          AND q.id NOT IN (
              SELECT pregunta_id 
              FROM game_question 
              WHERE partida_id = ?
          )
          AND (
              (q.veces_correctas / GREATEST(q.veces_presentada, 1) < 0.33 AND ? = 'facil') OR
              (q.veces_correctas / GREATEST(q.veces_presentada, 1) BETWEEN 0.33 AND 0.66 AND ? = 'normal') OR
              (q.veces_correctas / GREATEST(q.veces_presentada, 1) > 0.66 AND ? = 'dificil')
          )
        ORDER BY RAND()
        LIMIT 1";

            $stmt = $this->database->prepare($sqlDifficulty);
            $stmt->bind_param('iiiii', $category, $matchId, $userLevel, $userLevel, $userLevel);
            $stmt->execute();
            $result = $stmt->get_result();
            $questionId = $result->fetch_row()[0] ?? null;
        }

        // Si no se encuentra una pregunta, devolver cualquier otra (fallback)
        if (!$questionId) {
            $sqlFallback = "
        SELECT q.id 
        FROM question q
        WHERE q.categoria_id = ? 
          AND q.id NOT IN (
              SELECT pregunta_id 
              FROM game_question 
              WHERE partida_id = ?
          )
        ORDER BY RAND()
        LIMIT 1";
            $stmt = $this->database->prepare($sqlFallback);
            $stmt->bind_param('ii', $category, $matchId);
            $stmt->execute();
            $result = $stmt->get_result();
            $questionId = $result->fetch_row()[0];
        }

        // Registrar la pregunta en la partida
        if ($questionId) {
            $sqlInsert = "INSERT INTO game_question (partida_id, pregunta_id) VALUES (?, ?)";
            $stmt = $this->database->prepare($sqlInsert);
            $stmt->bind_param('ii', $matchId, $questionId);
            $stmt->execute();
        }

        return $questionId;
    }

    public function addQuestionToMatch2($matchId, $category)
    {
        $sql = "
    SELECT q.id 
FROM question q
WHERE q.categoria_id = ? 
  AND q.activo = 1 
  AND q.id NOT IN (
      SELECT pregunta_id 
      FROM game_question 
      WHERE partida_id = ?
  )
ORDER BY RAND()
LIMIT 1;
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
    ORDER BY RAND()
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

    public function validateResponse($idUser, $idMatch, $idQuestion, $idResponse)
    {
        $timeError = $this->validateResponseTime($idUser, $idMatch);
        if ($timeError === "timeout") {
            $this->endGame($idMatch);
            return ['timeout' => true];
        }

        $sqlCorrectAnswer = "SELECT respuesta_id FROM question_answer WHERE pregunta_id = ? AND es_correcta = ?";
        $stmt = $this->database->prepare($sqlCorrectAnswer);
        $true = 1;
        $stmt->bind_param('is', $idQuestion, $true);
        $stmt->execute();
        $correctAnswer = $stmt->get_result()->fetch_assoc();
        $correctAnswerId = $correctAnswer["respuesta_id"];
        $stmt->close();

        if ($idResponse == $correctAnswerId) {
            $this->updateMetrics($idUser, $idQuestion, true);
            $this->updateScore($idUser, $idMatch, $idQuestion);
            return [
                'correctAnswerId' => $correctAnswerId,
                'isCorrect' => true
            ];
        } else {
            $this->endGame($idMatch);
            $this->updateMetrics($idUser, $idQuestion, false);
            $score = $this->getScore($idUser, $idMatch);
            return [
                'correctAnswerId' => $correctAnswerId,
                'isCorrect' => false,
                'score' => $score
            ];
        }
    }

    private function endGame($idMatch)
    {
        $sqlEndGame = "UPDATE game SET estado = 'finalizada', fecha_fin = NOW() WHERE id = ?";
        $stmt = $this->database->prepare($sqlEndGame);
        $stmt->bind_param('i', $idMatch);
        $stmt->execute();
        $stmt->close();
    }

    private function updateScore($idUser, $idMatch, $idQuestion)
    {
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
    }

    private function getScore($idUser, $idMatch)
    {
        $sqlGetScore = "SELECT puntaje FROM user_game WHERE user_id = ? AND partida_id = ?";
        $stmt = $this->database->prepare($sqlGetScore);
        $stmt->bind_param('ii', $idUser, $idMatch);
        $stmt->execute();
        $score = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $score['puntaje'];
    }


    private function validateResponseTime($idUser, $idMatch)
    {
        $sql = "SELECT fecha_respuesta FROM user_game WHERE user_id = ? AND partida_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('ii', $idUser, $idMatch);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$data || !isset($data['fecha_respuesta'])) {
            return null;
        }

        $lastResponseTime = strtotime($data['fecha_respuesta']);
        $now = time();
        $timeDifference = $now - $lastResponseTime;


        if ($timeDifference > 12) {
            return "timeout";
        }
        return null;
    }

    public function reportQuestion($questionId, $description)
    {
        $sql = "INSERT INTO question_report (question_id, description, report_date) VALUES (?, ?, NOW())";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("is", $questionId, $description);

        if ($stmt->execute()) {
            $sql = "UPDATE question
            SET estado_id = 4
            WHERE id = $questionId";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return true;
        }
    }
    public function reporterQuestion($questionId){
        $sql = "UPDATE question
        SET estado_id = 4
        WHERE id=$questionId";
    }

    function suggestQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category)
    {
        $this->questionService->insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category);
    }

    public function findCategories()
    {
        $sql = "SELECT * FROM category c";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $categories = $stmt->get_result();

        return $categories;
    }

    function suggestedQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category)
    {
        return $this->questionService->insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category);
    }

    private function updateMetrics($idUser, $idQuestion, $isCorrect)
    {
        $sqlUpdateQuestion = "UPDATE question SET veces_presentada = veces_presentada + 1 WHERE id = ?";
        $stmt = $this->database->prepare($sqlUpdateQuestion);
        $stmt->bind_param('i', $idQuestion);
        $stmt->execute();

        if ($isCorrect) {
            $sqlUpdateCorrect = "UPDATE question SET veces_correctas = veces_correctas + 1 WHERE id = ?";
            $stmt = $this->database->prepare($sqlUpdateCorrect);
            $stmt->bind_param('i', $idQuestion);
            $stmt->execute();

            $sqlUpdateUser = "UPDATE user SET preguntas_correctas = preguntas_correctas + 1, preguntas_respondidas = preguntas_respondidas + 1 WHERE id = ?";
            $stmt = $this->database->prepare($sqlUpdateUser);
            $stmt->bind_param('i', $idUser);
            $stmt->execute();
        } else {
            $sqlUpdateUser = "UPDATE user SET preguntas_respondidas = preguntas_respondidas + 1 WHERE id = ?";
            $stmt = $this->database->prepare($sqlUpdateUser);
            $stmt->bind_param('i', $idUser);
            $stmt->execute();
        }
    }



}