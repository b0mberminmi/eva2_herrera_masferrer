<?php
require_once './models/OfertaLaboral.php';

// Clase OfertaLaboralController
// Controlador encargado de gestionar las operaciones relacionadas con las ofertas laborales.
class OfertaLaboralController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método crear
    // Permite crear una nueva oferta laboral.
    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar que los campos obligatorios estén presentes
        $titulo = $data['titulo'] ?? null;
        $reclutador_id = $data['reclutador_id'] ?? null;

        if (!$titulo || !$reclutador_id) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            return;
        }

        // Validar que el usuario sea un reclutador
        if (!OfertaLaboral::esReclutador($data['reclutador_id'], $this->db)) {
            http_response_code(403);
            echo json_encode(["error" => "Solo usuarios con rol 'Reclutador' pueden crear ofertas"]);
            return;
        }

        // Crear la oferta laboral
        $resultado = OfertaLaboral::crear($data, $this->db);

        // Responder según el resultado
        if ($resultado) {
            http_response_code(201);
            echo json_encode(["mensaje" => "Oferta creada correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo crear la oferta"]);
        }
    }

    // Método listarTodas
    // Devuelve una lista de todas las ofertas laborales.
    public function listarTodas() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral");
            $stmt->execute();
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["ofertas" => $ofertas]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener las ofertas"]);
        }
    }

    // Método listarActivas
    // Devuelve una lista de ofertas laborales activas (estado 'Vigente').
    public function listarActivas() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE estado = 'Vigente'");
            $stmt->execute();
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ofertas)) {
                echo json_encode(["mensaje" => "No hay ofertas activas disponibles"]);
                return;
            }

            echo json_encode(["ofertas_activas" => $ofertas]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener las ofertas activas"]);
        }
    }

    // Método editar
    // Permite editar una oferta laboral existente.
    public function editar($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos requeridos
        if (!$data['titulo'] || !$data['descripcion'] || !$data['ubicacion']) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos"]);
            return;
        }

        // Actualizar la oferta laboral
        $stmt = $this->db->prepare("UPDATE OfertaLaboral 
            SET titulo = :titulo,
                descripcion = :descripcion,
                ubicacion = :ubicacion,
                salario = :salario,
                tipo_contrato = :tipo_contrato,
                fecha_cierre = :fecha_cierre
            WHERE id = :id");

        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':salario', $data['salario']);
        $stmt->bindParam(':tipo_contrato', $data['tipo_contrato']);
        $stmt->bindParam(':fecha_cierre', $data['fecha_cierre']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["mensaje" => "Oferta actualizada correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar la oferta"]);
        }
    }

    // Método desactivar
    // Cambia el estado de una oferta laboral a 'Baja'.
    public function desactivar($id) {
        $stmt = $this->db->prepare("UPDATE OfertaLaboral SET estado = 'Baja' WHERE id = :id");
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["mensaje" => "Oferta dada de baja correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo desactivar la oferta"]);
        }
    }

    // Método listarPorReclutador
    // Devuelve una lista de ofertas laborales asociadas a un reclutador específico.
    public function listarPorReclutador($id) {
        try {
            // Verificar que el usuario sea un Reclutador
            $verificacion = $this->db->prepare("SELECT rol FROM Usuario WHERE id = :id");
            $verificacion->bindParam(':id', $id);
            $verificacion->execute();
            $usuario = $verificacion->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || $usuario['rol'] !== 'Reclutador') {
                http_response_code(403);
                echo json_encode(["error" => "Error, usuario debe tener rol Reclutador"]);
                return;
            }

            // Obtener las ofertas asociadas al reclutador
            $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE reclutador_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($ofertas) === 0) {
                echo json_encode([
                    "mensaje" => "No hay ofertas asignadas al reclutador"
                ]);
                return;
            }

            echo json_encode(["ofertas" => $ofertas]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener las ofertas del reclutador"]);
        }
    }
}
?>