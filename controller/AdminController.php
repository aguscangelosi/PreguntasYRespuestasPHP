<?php
require_once './vendor/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include_once "./vendor/jpgraph-4.4.2/src/jpgraph.php";
include_once "./vendor/jpgraph-4.4.2/src/jpgraph_line.php";
include_once "./vendor/jpgraph-4.4.2/src/jpgraph_pie.php";


class AdminController
{
    private $model;
    private $presenter;
    private $authHelper;

    private $grafico;
    public function __construct($model, $presenter, $authHelper)
    {
        $this->model = $model;
        $this->presenter = $presenter;
        $this->authHelper = $authHelper;
    }

    public function home(){
        $data = $this->model->findAllData();
        $this->presenter->show('homeAdmin', $data);
    }

    public function homeEdit(){
        $suggestions = $this->model->filterSuggestedQuestions();
        $reportedQuestions = $this->model->obtainReportedQuestions();
        $this->presenter->show('homeEdit', [
            'suggestedQuestions' => $suggestions,
            'reportedQuestions' => $reportedQuestions['reportedQuestions']
        ]);
    }

    public function generatePdf()
    {
        try {
            $dompdf = new Dompdf();

            $chartBase64 = $this->renderChart(true);

            $html = '
            <h1>Reporte de Gráficos</h1>
            <p>Este documento incluye los gráficos generados en el sistema.</p>
            <br>
            <img src="' . $chartBase64 . '" style="width:100%; max-width:500px;">
            <br>
            <p>Información adicional sobre los datos representados.</p>';

            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', 'portrait');

            $dompdf->render();

            $dompdf->stream("reporte_graficos.pdf", ["Attachment" => true]);
        } catch (Exception $e) {
            echo "Error al generar el PDF: " . $e->getMessage();
        }
    }

    public function renderChart($datos, $tipoGrafico, $labelKey, $valueKey, $isPDF = false)
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
            $labels[] = $dato[$labelKey];
            $values[] = $dato[$valueKey];
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


    public function filterStats()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $date = $input['date'] ?? date('Y-m-01');
        $type = $input['type'] ?? null;
        if ($type === 'genre') {
            $datos = $this->model->getSexTotal();
            $label = "sex";
            $key = "sex_total";
        }
        if ($type === 'country')  {
            $datos = $this->model->getCountry();
            $label = "pais";
            $key = "usuarios_por_pais";
        }
        try {
            $chart = $this->renderChart($datos, 'Pie',$label,$key, true);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
        header("Content-Type: image/png");
        echo $chart;
    }

    public function createQuestion(){
        $categories = $this->model->findCategories();
        $this->presenter->show('createQuestion', ['categories' => $categories]);
    }

    public function createQuestionPost()
    {
        $question = isset($_POST['question-text']) ? $_POST['question-text'] : '';

        $correctAnswer = isset($_POST['answer1']) ? $_POST['answer1'] : '';
        $answer2 = isset($_POST['answer2']) ? $_POST['answer2'] : '';
        $answer3 = isset($_POST['answer3']) ? $_POST['answer3'] : '';
        $answer4 = isset($_POST['answer4']) ? $_POST['answer4'] : '';

        $category = isset($_POST['category']) ? $_POST['category'] : '';

        $this->model->insertNewQuestion($question, $correctAnswer, $answer2, $answer3, $answer4, $category);
        $this->redirectHome();
        $this->presenter->show('createQuestion', ['success' => "Pregunta agregada exitosamente"]);
        exit();
    }


    public function redirectHome()
    {
        header('location: /PreguntasYRespuestasPHP/homeEdit');
    }

    function deleteQuestion()
    {
        $idQuestion = isset($_GET['id']) ? $id = $_GET['id'] : '';
        $this->model->deleteQuestion($idQuestion);
        $this->presenter->show('deleteQuestion');
    }

    function approveQuestion()
    {
        $idQuestion = isset($_GET['id']) ? $id = $_GET['id'] : '';
        $this->model->approveQuestion($idQuestion);
        $this->presenter->show('approveQuestion');
    }
    function approveReport()
    {
        $idQuestion = isset($_GET['id']) ? $id = $_GET['id'] : '';
        $this->model->approveReport($idQuestion);
        $this->presenter->show('approveReport');
    }

    function declineReport()
    {
        $idQuestion = isset($_GET['id']) ? $id = $_GET['id'] : '';
        $this->model->declineReport($idQuestion);
        $this->presenter->show('declineReport');
    }

//    function creationGraphics()
//    {
//        $results = $this->model->ratioAge();
//        $this->renderChartTincho($results);
//    }
}