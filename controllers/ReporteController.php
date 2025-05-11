<?php
require_once './models/Reporte.php';

// Clase ReporteController
// Controlador encargado de gestionar los reportes relacionados con postulaciones y ofertas laborales.
class ReporteController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método postulantesPorOferta
    // Obtiene una lista de postulantes asociados a una oferta laboral específica.
    // Parámetros:
    // - $id: ID de la oferta laboral.
    public function postulantesPorOferta($id) {
        $resultado = Reporte::obtenerPostulantesPorOferta($id, $this->db);

        // Verificar si la oferta laboral existe
        if (!$resultado) {
            http_response_code(404);
            echo json_encode(["error" => "La oferta laboral no existe"]);
            return;
        }

        // Devolver el resultado en formato JSON
        echo json_encode($resultado);
    }

    // Método totalPostulacionesPorOferta
    // Obtiene el número total de postulaciones realizadas para una oferta laboral específica.
    // Parámetros:
    // - $id: ID de la oferta laboral.
    public function totalPostulacionesPorOferta($id) {
        $resultado = Reporte::contarPostulacionesPorOferta($id, $this->db);

        // Verificar si la oferta laboral existe
        if (!$resultado) {
            http_response_code(404);
            echo json_encode(["error" => "La oferta laboral no existe"]);
            return;
        }

        // Devolver el resultado en formato JSON
        echo json_encode($resultado);
    }

    // Método postulacionesPorReclutador
    // Obtiene una lista de postulaciones asociadas a un reclutador específico.
    // Parámetros:
    // - $id: ID del reclutador.
    public function postulacionesPorReclutador($id) {
        // Obtener el nombre del reclutador
        $reclutador = Reporte::obtenerNombreReclutador($id, $this->db);

        // Verificar si el reclutador existe
        if (!$reclutador) {
            http_response_code(404);
            echo json_encode(["error" => "Reclutador no encontrado"]);
            return;
        }

        // Obtener las postulaciones asociadas al reclutador
        $postulaciones = Reporte::obtenerPostulacionesPorReclutador($id, $this->db);

        // Verificar si no hay postulaciones
        if (empty($postulaciones)) {
            echo json_encode([
                "reclutador" => $reclutador['nombre'] . " " . $reclutador['apellido'],
                "mensaje" => "No posee postulaciones a su cargo"
            ]);
            return;
        }

        // Devolver el resultado en formato JSON
        echo json_encode([
            "reclutador" => $reclutador['nombre'] . " " . $reclutador['apellido'],
            "postulaciones" => $postulaciones
        ]);
    }
}
?>