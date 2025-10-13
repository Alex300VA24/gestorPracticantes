<?php
namespace App\Controllers;

use App\Services\SolicitudService;

class SolicitudController {
    private $service;

    public function __construct() {
        $this->service = new SolicitudService();
    }

    public function listarPracticantes() {
        $data = $this->service->listarNombresPracticantes();
        echo json_encode($data);
    }

    public function obtenerDocumentosPorPracticante(){

        if (!isset($_GET['practicanteID'])) {
            echo json_encode([]);
            return;
        }

        $id = $_GET['practicanteID'];
        $solicitud = $this->service->obtenerDocumentosPorPracticante($id);

        $documentos = [];

        // Codificamos cada documento en Base64 (solo si existe)
        if ($solicitud->getDocCV()) {
            $documentos[] = [
                'tipo' => 'CV',
                'fecha' => '',
                'archivo' => base64_encode($solicitud->getDocCV()),
                'observaciones' => ''
            ];
        }

        if ($solicitud->getDocCartaPresentacionUniversidad()) {
            $documentos[] = [
                'tipo' => 'Carta de Presentacion',
                'fecha' => '',
                'archivo' => base64_encode($solicitud->getDocCartaPresentacionUniversidad()),
                'observaciones' => ''
            ];
        }

        if ($solicitud->getDocCarnetVacunacion()) {
            $documentos[] = [
                'tipo' => 'Carnet de Vacunacion',
                'fecha' => '',
                'archivo' => base64_encode($solicitud->getDocCarnetVacunacion()),
                'observaciones' => ''
            ];
        }

        if ($solicitud->getDocDNI()) {
            $documentos[] = [
                'tipo' => 'DNI',
                'fecha' => '',
                'archivo' => base64_encode($solicitud->getDocDNI()),
                'observaciones' => ''
            ];
        }

        // ✅ Codificamos el JSON con manejo de errores
        $json = json_encode($documentos, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("❌ JSON Error: " . json_last_error_msg());
            //echo json_encode(['error' => json_last_error_msg()]);
            return;
        }

        echo $json;
    }

    public function verDocumento()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros insuficientes']);
            return;
        }

        $id = $_GET['id'];
        $tipo = $_GET['tipo']; // por ejemplo: cv, carta_presentacion, dni, etc.

        // Obtener los datos desde el servicio
        $solicitud = $this->service->obtenerDocumentosPorPracticante($id);

        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró el practicante']);
            return;
        }

        // Seleccionar el documento binario según el tipo
        switch ($tipo) {
            case 'cv':
                $contenido = $solicitud->getDocCV();
                $nombre = "Curriculum_Vitae.pdf";
                break;
            case 'carta_presentacion':
                $contenido = $solicitud->getDocCartaPresentacionUniversidad();
                $nombre = "Carta_Presentacion.pdf";
                break;
            case 'carnet_vacunacion':
                $contenido = $solicitud->getDocCarnetVacunacion();
                $nombre = "Constancia_Universidad.pdf";
                break;
            case 'dni':
                $contenido = $solicitud->getDocDNI();
                $nombre = "DNI.pdf";
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de documento inválido']);
                return;
        }

        if (!$contenido) {
            http_response_code(404);
            echo json_encode(['error' => 'Documento vacío o no encontrado']);
            return;
        }

        // Cabeceras para descarga o visualización
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombre . '"');
        echo $contenido;
    }




    public function subirDocumento() {
        try {
            // Log rápido de lo que llega
            file_put_contents("debug_subida.txt", print_r($_POST, true) . "\n" . print_r($_FILES, true));

            $practicanteID = $_POST['practicanteID'] ?? null;
            $tipoDocumento = $_POST['tipoDocumento'] ?? null;
            $archivo = $_FILES['archivoDocumento']['tmp_name'] ?? null;

            if (!$practicanteID || !$tipoDocumento || !$archivo) {
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }

            $mapaTipos = [
                'cv' => 'cv',
                'carta_presentacion' => 'carta_presentacion',
                'carnet_vacunacion' => 'carnet_vacunacion',
                'dni' => 'dni'
            ];

            if (!isset($mapaTipos[$tipoDocumento])) {
                echo json_encode(['error' => 'Tipo de documento no válido']);
                return;
            }

            $tipoSP = $mapaTipos[$tipoDocumento];

            if (!file_exists($archivo)) {
                echo json_encode(['error' => 'No se pudo acceder al archivo']);
                return;
            }

            $contenido = file_get_contents($archivo);
            if ($contenido === false) {
                echo json_encode(['error' => 'No se pudo leer el archivo']);
                return;
            }

            $ok = $this->service->subirDocumento($practicanteID, $tipoSP, $contenido);

            echo json_encode(['success' => $ok]);
        } catch (\Throwable $e) {
            file_put_contents("error_subida.txt", $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
        }

    }



}
