<?php
require_once './vendor/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include_once "./vendor/jpgraph-4.4.2/src/jpgraph.php";
include_once "./vendor/jpgraph-4.4.2/src/jpgraph_line.php";

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
        $suggestions = $this->model->filterSuggestedQuestion();
        $reportedQuestions = $this->model->obtainReportedQuestions();
        $this->presenter->show('homeEdit', [
            'suggestedQuestions' => $suggestions,
            'reportedQuestions' => $reportedQuestions['reportedQuestions']
        ]);
    }

    public function generatePdf() {
        try {
            // Configurar opciones de Dompdf
            $dompdf = new Dompdf();

            // Intentar generar el gráfico
            $chartContent = '';
            try {
                ob_start(); // Iniciar el buffer de salida
                $this->renderChart(); // Esto genera el gráfico y lo envía al buffer de salida
                $chartContent = ob_get_clean(); // Capturar el contenido del gráfico
            } catch (Exception $e) {
                // Capturar errores específicos del gráfico
                $chartError = "Error al generar el gráfico: " . $e->getMessage();
                $chartContent = null;
            }

            // Crear contenido HTML para el PDF
            $html = '<h1>Reporte de Gráficos</h1>';


                $filePath = './img/graficos/hola.png';

                // Agregar el gráfico al HTML
                $html .= '
            <p>Este documento incluye los gráficos generados en el sistema.</p>
            <img src="'.$filePath.'"  style="width:100%; max-width:500px;">
            <p>Información adicional sobre los datos representados.</p>';


            // Cargar el HTML en Dompdf
            $dompdf->loadHtml($html);

            // Configurar el tamaño de papel y la orientación
            $dompdf->setPaper('A4', 'portrait');

            // Renderizar el PDF
            $dompdf->render();

            // Enviar el PDF al navegador para descargar
            $dompdf->stream("reporte_graficos.pdf", ["Attachment" => true]);

            // Eliminar el archivo temporal después de generar el PDF
            if (!empty($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
        } catch (Exception $e) {
            // Manejo de errores generales
            $html = '<h1>Error al generar el PDF</h1>';
            $html .= '<p style="color:red;">Detalles del error:</p>';
            $html .= '<p style="color:red;">' . htmlspecialchars($e->getMessage()) . '</p>';

            // Cargar el HTML en Dompdf
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("error_reporte.pdf", ["Attachment" => true]);
        }
    }

    public function renderChart()  //TODO ASIGNAR PARAMETROS PARA HACER DINAMICO
    {
        // Crear el gráfico
        $this->grafico = new Graph(400, 300, "auto");
        $this->grafico->SetScale("textlin");
        $datos = array(10, 20, 5, 15, 20);
        $linea = new LinePlot($datos);
        $this->grafico->Add($linea);

        // Enviar el gráfico directamente al navegador
        return $this->grafico->Stroke("./img/graficos/hola.png");
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

}