<?php
require_once './models/Postulacion.php';

// Clase PostulacionController
// Controlador encargado de gestionar las operaciones relacionadas con las postulaciones a ofertas laborales.
class PostulacionController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método registrarPostulacion
    // Permite registrar una nueva postulación a una oferta laboral.
    public function registrarPostulacion() {
        $data = json_decode(file_get_contents("php://input"), true);
        $candidato_id = $data['candidato_id'] ?? null;
        $oferta_id = $data['oferta_laboral_id'] ?? null;

        // Validar que los campos obligatorios estén presentes
        if (!$candidato_id || !$oferta_id) {
            http_response_code(400);
            echo json_encode(["error" => "Debe indicar candidato_id y oferta_laboral_id"]);
            return;
        }

        // Validar que el usuario sea un candidato
        if (!Postulacion::esCandidato($candidato_id, $this->db)) {
            http_response_code(403);
            echo json_encode(["error" => "Solo los usuarios con rol 'Candidato' pueden postular a una oferta"]);
            return;
        }

        // Validar que la oferta laboral exista y esté en estado 'Vigente'
        $oferta = Postulacion::obtenerOferta($oferta_id, $this->db);

        if (!$oferta) {
            http_response_code(404);
            echo json_encode(["error" => "La oferta laboral no existe"]);
            return;
        }

        if ($oferta['estado'] !== 'Vigente') {
            http_response_code(403);
            echo json_encode(["error" => "Solo se puede postular a ofertas laborales Vigentes"]);
            return;
        }

        // Validar que no exista una postulación previa para la misma oferta
        if (Postulacion::yaExiste($candidato_id, $oferta_id, $this->db)) {
            http_response_code(409);
            echo json_encode(["error" => "El candidato ya está postulando a esta oferta"]);
            return;
        }

        // Registrar la postulación en la base de datos
        if (Postulacion::registrarPostulacion($candidato_id, $oferta_id, $this->db)) {
            http_response_code(201);
            echo json_encode(["mensaje" => "Postulación registrada correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar la postulación"]);
        }
    }

    // Método verPostulacionesPorCandidato
    // Devuelve una lista de postulaciones realizadas por un candidato específico.
    public function verPostulacionesPorCandidato($id) {
        // Obtener las postulaciones del candidato
        $postulaciones = Postulacion::obtenerPostulacionesPorCandidato($id, $this->db);

        // Validar que el usuario tenga el rol de candidato
        if ($postulaciones === null) {
            http_response_code(403);
            echo json_encode(["error" => "Solo los usuarios con rol Candidato pueden ver sus postulaciones"]);
            return;
        }

        // Validar si el candidato no tiene postulaciones registradas
        if (empty($postulaciones)) {
            echo json_encode(["mensaje" => "El candidato no tiene postulaciones registradas"]);
            return;
        }

        // Devolver las postulaciones en formato JSON
        echo json_encode(["postulaciones" => $postulaciones]);
    }
}
?>
