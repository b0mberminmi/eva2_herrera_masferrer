<?php
require_once './models/EstadosPostulacion.php';

// Clase EstadoPostulacionController
// Controlador encargado de gestionar los estados de las postulaciones.
class EstadoPostulacionController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método cambiarEstado
    // Permite cambiar el estado de una postulación.
    // Parámetros:
    // - $id: ID de la postulación.
    public function cambiarEstado($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $estado = $data['estado'] ?? '';

        // Verificar si la postulación existe
        if (!EstadosPostulacion::existePostulacion($id, $this->db)) {
            http_response_code(404);
            echo json_encode(["error" => "Postulación no existe"]);
            return;
        }

        // Validar que el estado sea permitido
        $permitidos = EstadosPostulacion::obtenerEstadosPermitidos();
        if (!in_array($estado, $permitidos)) {
            http_response_code(400);
            echo json_encode(["error" => "Estado no válido"]);
            return;
        }

        // Actualizar el estado de la postulación
        if (EstadosPostulacion::actualizarEstado($id, $estado, $this->db)) {
            echo json_encode(["mensaje" => "Estado actualizado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar el estado"]);
        }
    }

    // Método obtenerEstados
    // Devuelve una lista de todos los estados válidos para las postulaciones.
    public function obtenerEstados() {
        $estados = EstadosPostulacion::obtenerEstadosPermitidos();

        // Responder con la lista de estados válidos
        echo json_encode(["estados_postulacion" => $estados]);
    }
}
?>