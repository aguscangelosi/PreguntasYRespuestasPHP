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

    public function filterSuggestedQuestions() {
        $sql = "SELECT q.id, ca.nombre_categoria, s.nombre_estado, q.enunciado, q.dificultad, q.categoria_id, 
            q.estado_id, q.activo
            FROM question q
            JOIN category ca ON ca.id = q.categoria_id
            JOIN status s ON s.id = q.estado_id
            WHERE q.activo = 0 AND q.estado_id = 1;";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $suggestedQuestions = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suggestedQuestions[] = [
                    'id' => $row['id'],
                    'nombre_categoria' => $row['nombre_categoria'],
                    'nombre_estado' => $row['nombre_estado'],
                    'enunciado' => $row['enunciado'],
                    'dificultad' => $row['dificultad'],
                    'categoria_id' => $row['categoria_id'],
                    'estado_id' => $row['estado_id'],
                    'activo' => $row['activo'],
                ];
            }
        }

        return $suggestedQuestions;
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
        $sql = "SELECT COUNT(*) as total_users FROM user
        WHERE rol_id = ?";
        $stmt = $this->database->prepare($sql);
        $rolID = 2;
        $stmt->bind_param("i", $rolID);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findMatchesPlayed()
    {
        $sql = "SELECT COUNT(*) as total_games FROM game g WHERE g.estado = ?";
        $stmt = $this->database->prepare($sql);
        $estado = "finalizada";
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findAllQuestions(){
        $sql = "SELECT COUNT(*) as total_questions FROM question";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findAllQuestionsCreated(){
        $sql = "SELECT COUNT(question_id) as total_questions_created FROM statistics_admin";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function findAllUsersCreated(){
        $sql = "SELECT COUNT(user_id) as total_users_created FROM statistics_admin";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }



    public function ratioForAccierts()
    {
        $sql = "SELECT ROUND((SUM(es_correcta) / COUNT(*) * 100), 2) AS promedio_aciertos
        FROM game_question;";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getSexF()
    {
        $sql = "SELECT sex, COUNT(*) AS sex_F
        FROM user
        WHERE sex LIKE 'F'
        GROUP BY sex";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getSexM()
    {
        $sql = "SELECT sex, COUNT(*) AS sex_M
        FROM user
        WHERE sex LIKE 'M'
        GROUP BY sex";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getSexX()
    {
        $sql = "SELECT sex, COUNT(*) AS sex_X
        FROM user
        WHERE sex LIKE 'X'
        GROUP BY sex";

        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    public function ratioAge(){
        $sql = "SELECT ROUND(AVG(TIMESTAMPDIFF(YEAR, birthday, CURDATE()))) AS promedio_edad FROM user";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function ratioAgeChildren(){
        $sql = "SELECT ROUND(AVG(TIMESTAMPDIFF(year, birthday, CURDATE()))) AS promedio_edad_menores
        FROM user
        WHERE TIMESTAMPDIFF(year, birthday, CURDATE()) < 18";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
    public function ratioAgeAdults(){
        $sql = "SELECT ROUND(AVG(TIMESTAMPDIFF(year, birthday, CURDATE()))) AS promedio_edad_adultos
        FROM user
        WHERE TIMESTAMPDIFF(year, birthday, CURDATE()) >= 18 AND TIMESTAMPDIFF(year, birthday, CURDATE()) < 65";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function ratioAgeMajorAdults(){
        $sql = "SELECT ROUND(AVG(TIMESTAMPDIFF(year, birthday, CURDATE()))) AS promedio_edad_adultosMayores
        FROM user
        WHERE TIMESTAMPDIFF(year, birthday, CURDATE()) >= 65";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    public function getSexTotal($date)
    {
        $sql = "SELECT sex AS keyValue, COUNT(*) AS total
            FROM user
            WHERE register_date < ?
            GROUP BY keyValue";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param('s', $date); // 's' indica que es un string
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCountry($date)
    {
        $sql = "SELECT pais AS keyValue, COUNT(*) AS total
            FROM user
            WHERE register_date < ?
            GROUP BY pais
            ORDER BY total DESC";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function findAllData()
    {
        $date= date('d-m-Y');
        $totalQuestions = $this->findAllQuestions()['total_questions'];
        $matchesPlayed = $this->findMatchesPlayed()['total_games'];
        $players = $this->findPlayers()['total_users'];
        $questionsCreated = $this->findAllQuestionsCreated()['total_questions_created'];
        $usersCreated = $this->findAllUsersCreated()['total_users_created'];
        $ratioForAge = $this->ratioAge()['promedio_edad'];
        $ratioForAgeChildrens = $this->ratioAgeChildren()['promedio_edad_menores'];
        $ratioForAgeAdults = $this->ratioAgeAdults()['promedio_edad_adultos'];
        $ratioAgeMajorAdults = $this->ratioAgeMajorAdults()['promedio_edad_adultosMayores'];
        $totalContries = $this->getCountry($date);
        $sexWomen = $this->getSexF()['sex_F'];
        $sexMen = $this->getSexM()['sex_M'];
        $sexElle = $this->getSexX()['sex_X'];
        $sexTotal = $this->getSexTotal($date);
        $ratioForAccierts = $this->ratioForAccierts()['promedio_aciertos'];
        $data = [
            'total_users' => $players,
            'total_questions' => $totalQuestions,
            'total_games' => $matchesPlayed,
            'total_questions_created' =>$questionsCreated,
            'total_users_created' =>$usersCreated,
            'promedio_edad' => $ratioForAge,
            'promedio_edad_menores' => $ratioForAgeChildrens,
            'promedio_edad_adultos' => $ratioForAgeAdults,
            'promedio_edad_adultosMayores' => $ratioAgeMajorAdults,
            'totalDeHombres' => $sexMen,
            'totalDeMujeres' => $sexWomen,
            'totalDeElles' => $sexElle,
            'sex_Total' => $sexTotal,
            'ratioForAccierts' => $ratioForAccierts,
        ];

        return $data;
    }

   public function getDataCharts($type,$date)
   {
       if ($type === 'genre') {
           return $this->getSexTotal($date);

       }
       if ($type === 'country')  {
           return $this->getCountry($date);
       }

   }

    public function renderChart($datos, $tipoGrafico, $isPDF = false)
    {
        switch ($tipoGrafico) {
            case 'Pie':
                $this->grafico = new PieGraph(400, 300, "auto");
                break;
            case 'Bar':
                $this->grafico = new Graph(400, 300, "auto");
                $this->grafico->SetScale("textlin");
                break;
            default:
                throw new Exception("Tipo de gráfico no soportado: $tipoGrafico");
        }

        $this->grafico->SetShadow();

        $labels = [];
        $values = [];
        foreach ($datos as $dato) {
            $labels[] = $dato["keyValue"];
            $values[] = $dato["total"];
        }

        switch ($tipoGrafico) {
            case 'Pie':
                $plot = new PiePlot($values);
                $plot->SetLegends($labels);
                $plot->SetLabelType(PIE_VALUE_PER);
                $plot->value->SetFormat('%2.1f%%');
                break;

            case 'Bar':
                $plot = new BarPlot($values);
                $plot->SetLegend(implode(", ", $labels));
                break;
        }

        $this->grafico->Add($plot);

        ob_start();

        if (!$isPDF) {
            return $this->grafico->Stroke();
        }
        $this->grafico->Stroke();
        $chartContent = ob_get_clean();

        $chartBase64 = 'data:image/png;base64,' . base64_encode($chartContent);

        return $chartBase64;
    }


}