<?php
require_once './vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
class PdfController //TODO SI ME LOGUEO COMO ADMIN, EL METODO ESTA INHABILITADO
{
    public function generatePdf() {
        // Configurar opciones de Dompdf
//        $options = new Options();
//        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf();

        // Crear contenido HTML para el PDF
        $html = '
        <h1>Reporte de Gráficos</h1>
        <p>Este documento incluye los gráficos generados en el sistema.</p>
        <img src="/PreguntasYRespuestasPHP/admin/renderChart" alt="Gráfico 1" style="width:100%; max-width:500px;">
        <p>Información adicional sobre los datos representados.</p>';

        // Cargar el HTML en Dompdf
        $dompdf->loadHtml($html);

        // Configurar el tamaño de papel y la orientación
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar el PDF
        $dompdf->render();

        // Enviar el PDF al navegador para descargar
        $dompdf->stream("reporte_graficos.pdf", ["Attachment" => true]);
    }
}