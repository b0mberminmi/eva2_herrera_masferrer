<?php
require_once './models/Comentario.php';

// Clase ComentarioController
// Controlador encargado de gestionar los comentarios asociados a las postulaciones.
class ComentarioController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método agregar
    // Permite agregar un comentario a una postulación específica.
    // Parámetros:
    // - $id: ID de la postulación a la que se agregará el comentario.
    public function agregar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $comentario = $data['comentario'] ?? '';

        // Validar que el comentario no esté vacío
        if (!$comentario) {
            http_response_code(400);
            echo json_encode(["error" => "Comentario requerido"]);
            return;
        }

        // Verificar si la postulación existe
        if (!Comentario::existePostulacion($id, $this->db)) {
            http_response_code(404);
            echo json_encode(["error" => "Postulación no existe"]);
            return;
        }

        // Agregar el comentario a la postulación
        if (Comentario::agregarComentario($id, $comentario, $this->db)) {
            echo json_encode(["mensaje" => "Comentario agregado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al agregar el comentario"]);
        }
    }

    // Método eliminar
    // Permite eliminar un comentario asociado a una postulación específica.
    // Parámetros:
    // - $id: ID de la postulación de la que se eliminará el comentario.
    public function eliminar($id) {
        // Verificar si la postulación existe
        if (!Comentario::existePostulacion($id, $this->db)) {
            http_response_code(404);
            echo json_encode(["error" => "Postulación no existe"]);
            return;
        }

        // Eliminar el comentario de la postulación
        if (Comentario::eliminarComentario($id, $this->db)) {
            echo json_encode(["mensaje" => "Comentario eliminado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar el comentario"]);
        }
    }
}
?>