<?php
// Clase OfertaLaboral
// Modelo encargado de gestionar las operaciones relacionadas con las ofertas laborales.
class OfertaLaboral {

    // Método esReclutador
    // Verifica si un usuario tiene el rol de "Reclutador".
    // Parámetros:
    // - $id: ID del usuario.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si el usuario es reclutador, false en caso contrario.
    public static function esReclutador($id, $db) {
        $stmt = $db->prepare("SELECT id FROM Usuario WHERE id = :id AND rol = 'Reclutador'");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método crear
    // Crea una nueva oferta laboral en la base de datos.
    // Parámetros:
    // - $data: Array con los datos de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la operación fue exitosa, false en caso contrario.
    public static function crear($data, $db) {
        $stmt = $db->prepare("INSERT INTO OfertaLaboral (titulo, descripcion, ubicacion, salario, tipo_contrato, fecha_cierre, reclutador_id) 
                              VALUES (:titulo, :descripcion, :ubicacion, :salario, :tipo_contrato, :fecha_cierre, :reclutador_id)");
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':salario', $data['salario']);
        $stmt->bindParam(':tipo_contrato', $data['tipo_contrato']);
        $stmt->bindParam(':fecha_cierre', $data['fecha_cierre']);
        $stmt->bindParam(':reclutador_id', $data['reclutador_id']);
        return $stmt->execute();
    }

    // Método listarActivas
    // Devuelve una lista de todas las ofertas laborales activas (estado 'Vigente').
    // Parámetros:
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con las ofertas activas.
    public static function listarActivas($db) {
        $stmt = $db->query("SELECT * FROM OfertaLaboral WHERE estado = 'Vigente'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método editar
    // Actualiza los datos de una oferta laboral específica.
    // Parámetros:
    // - $id: ID de la oferta laboral.
    // - $data: Array con los datos actualizados.
    // - $db: Conexión a la base de datos.
    public function editar($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos requeridos
        if (!$data['titulo'] || !$data['descripcion'] || !$data['ubicacion']) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos"]);
            return;
        }

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
    // Parámetros:
    // - $id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
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

    // Método obtenerPostulacionesPorCandidato
    // Devuelve una lista de postulaciones realizadas por un candidato específico.
    // Parámetros:
    // - $id: ID del candidato.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con las postulaciones del candidato o null si no es candidato.
    public static function obtenerPostulacionesPorCandidato($id, $db) {
        // Verificar que el usuario sea candidato
        $stmtCheck = $db->prepare("SELECT rol FROM Usuario WHERE id = :id");
        $stmtCheck->bindParam(':id', $id);
        $stmtCheck->execute();
        $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || $usuario['rol'] !== 'Candidato') {
            return null;
        }

        // Obtener postulaciones
        $stmt = $db->prepare("
            SELECT O.titulo, P.estado_postulacion, P.comentario, P.fecha_postulacion
            FROM Postulacion P
            JOIN OfertaLaboral O ON P.oferta_laboral_id = O.id
            WHERE P.candidato_id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>