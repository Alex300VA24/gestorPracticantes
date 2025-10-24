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

    public function obtenerDocumentosPorPracticante() {
        if (!isset($_GET['practicanteID'])) {
            echo json_encode([]);
            return;
        }

        $id = (int) $_GET['practicanteID'];
        $documentos = $this->service->obtenerDocumentosPorPracticante($id);

        if (!$documentos || count($documentos) === 0) {
            echo json_encode([]);
            return;
        }

        $resultado = [];

        foreach ($documentos as $doc) {
            $resultado[] = [
                'documentoID'   => $doc['DocumentoID'],
                'solicitudID'   => $doc['SolicitudID'],
                'tipo'          => $doc['TipoDocumento'],
                'fecha'         => $doc['FechaSubida'],
                'archivo'       => $doc['Archivo'],
                'observaciones' => $doc['Observaciones'] ?? '',
                'area'          => $doc['Area'] ?? '-'
            ];
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    public function obtenerDocumentoPorTipoYPracticante()
    {
        // Siempre devolver JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            // 🔹 Obtener parámetros desde la URL
            $practicanteID = $_GET['practicanteID'] ?? null;
            $tipoDocumento = $_GET['tipoDocumento'] ?? null;

            if (!$practicanteID || !$tipoDocumento) {
                echo json_encode([
                    "success" => false,
                    "message" => "Faltan parámetros: practianteID o tipoDocumento."
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // 🔹 Llamar al servicio (modelo)
            $documento = $this->service->obtenerDocumentoPorTipoYPracticante($practicanteID, $tipoDocumento);

            // 🔹 Asegurar respuesta coherente
            if ($documento && is_array($documento)) {
                echo json_encode([
                    "success" => true,
                    "data" => $documento
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    "success" => true,
                    "data" => null
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            // 🔹 Capturar cualquier error del servidor
            echo json_encode([
                "success" => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }



    public function subirDocumento() {
        try {
            // Log rápido para depuración
            file_put_contents("debug_subida.txt", print_r($_POST, true) . "\n" . print_r($_FILES, true), FILE_APPEND);

            $solicitudID   = $_POST['solicitudID'] ?? null;
            $tipoDocumento = $_POST['tipoDocumento'] ?? null;
            $observaciones = $_POST['observacionesDoc'] ?? null;
            $archivo       = $_FILES['archivoDocumento']['tmp_name'] ?? null;

            if (!$solicitudID || !$tipoDocumento || !$archivo) {
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }

            // Mapeo de tipos válidos
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

            // Llamar al servicio con 4 parámetros
            $ok = $this->service->subirDocumento($solicitudID, $tipoSP, $contenido, $observaciones);

            if ($ok) {
                // Si la subida fue exitosa ($ok es true)
                echo json_encode(['success' => true, 'message' => 'Documento subido correctamente']); 
            } else {
                // Si no fue exitosa ($ok es false)
                echo json_encode(['success' => false, 'message' => 'Error al subir el documento en el servicio']); 
            }
        } catch (\Throwable $e) {
            file_put_contents("error_subida.txt", $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
        }
    }

    public function actualizarDocumento(){
        try {
            // debug pre
            file_put_contents("debug_actualizar.txt", "---- INICIO Llamada actualizarDocumento ----\n", FILE_APPEND);
            file_put_contents("debug_actualizar.txt", print_r($_POST, true) . "\n" . print_r($_FILES, true) . "\n", FILE_APPEND);

            $solicitudID   = $_POST['solicitudID'] ?? null;
            $tipoDocumento = $_POST['tipoDocumento'] ?? null;
            $observaciones = $_POST['observacionesDoc'] ?? null;
            $archivoTmp    = $_FILES['archivoDocumento']['tmp_name'] ?? null;
            $archivoErr    = $_FILES['archivoDocumento']['error'] ?? 4; // 4 = UPLOAD_ERR_NO_FILE
            $archivoSize   = $_FILES['archivoDocumento']['size'] ?? 0;

            if (!$solicitudID || !$tipoDocumento) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos: solicitudID o tipoDocumento']);
                return;
            }

            // Normalizar tipoDocumento por seguridad
            $tipoDocumento = strtolower(trim($tipoDocumento));
            $tipoDocumento = str_replace([' ', '-'], '_', $tipoDocumento);
            $tipoDocumento = iconv('UTF-8', 'ASCII//TRANSLIT', $tipoDocumento);

            // Leer archivo si existe y si no hubo error
            $contenido = null;
            if ($archivoTmp && file_exists($archivoTmp) && $archivoErr === UPLOAD_ERR_OK && $archivoSize > 0) {
                $contenido = file_get_contents($archivoTmp);
                if ($contenido === false) {
                    echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo en el servidor']);
                    return;
                }
                // fijar fecha de subida sólo si hay archivo
                $fechaSubida = date('Y-m-d H:i:s');
                file_put_contents("debug_actualizar.txt", "Archivo leido, bytes: " . strlen($contenido) . " FechaSubida: $fechaSubida\n", FILE_APPEND);
            } else {
                $contenido = null;
                $fechaSubida = null;
                file_put_contents("debug_actualizar.txt", "No hay archivo para actualizar (archivoErr=$archivoErr size=$archivoSize)\n", FILE_APPEND);
            }

            // Llamada a la capa de servicio/repositorio
            $ok = false;

            try {
                $ok = $this->service->actualizarDocumento($solicitudID, $tipoDocumento, $contenido, $observaciones);

                file_put_contents("debug_actualizar.txt", "Resultado service.actualizarDocumento: " . json_encode($ok) . "\n", FILE_APPEND);

                echo json_encode([
                    'success' => true,
                    'message' => 'Documento procesado correctamente'
                ]);
            } catch (\Exception $e) {
                file_put_contents("debug_actualizar.txt", "❌ Error en actualizarDocumento: " . $e->getMessage() . "\n", FILE_APPEND);

                echo json_encode([
                    'success' => false,
                    'message' => 'Error al procesar el documento: ' . $e->getMessage()
                ]);
            }

        } catch (\Throwable $e) {
            file_put_contents("error_actualizar.txt", $e->getMessage() . "\n" . $e->getTraceAsString(), FILE_APPEND);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Agregar este método a SolicitudController

    public function obtenerSolicitudPorPracticante() {
        try {
            if (!isset($_GET['practicanteID'])) {
                echo json_encode(['success' => false, 'message' => 'Falta practicanteID']);
                return;
            }
            
            $practicanteID = (int)$_GET['practicanteID'];
            $solicitud = $this->service->obtenerSolicitudPorPracticante($practicanteID);
            
            echo json_encode([
                'success' => true,
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }






}
