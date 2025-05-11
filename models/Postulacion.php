<?php

// Clase Postulacion
// Modelo encargado de gestionar las operaciones relacionadas con las postulaciones a ofertas laborales.
class Postulacion {

    // Método yaExiste
    // Verifica si ya existe una postulación para un candidato en una oferta laboral específica.
    // Parámetros:
    // - $candidato_id: ID del candidato.
    // - $oferta_id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la postulación ya existe, false en caso contrario.
    public static function yaExiste($candidato_id, $oferta_id, $db) {
        $stmt = $db->prepare("SELECT id FROM Postulacion WHERE candidato_id = :candidato_id AND oferta_laboral_id = :oferta_id");
        $stmt->bindParam(':candidato_id', $candidato_id);
        $stmt->bindParam(':oferta_id', $oferta_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método registrarPostulacion
    // Registra una nueva postulación en la base de datos.
    // Parámetros:
    // - $candidato_id: ID del candidato.
    // - $oferta_id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la operación fue exitosa, false en caso contrario.
    public static function registrarPostulacion($candidato_id, $oferta_id, $db) {
        $stmt = $db->prepare("INSERT INTO Postulacion (candidato_id, oferta_laboral_id) 
                              VALUES (:candidato_id, :oferta_id)");
        $stmt->bindParam(':candidato_id', $candidato_id);
        $stmt->bindParam(':oferta_id', $oferta_id);
        return $stmt->execute();
    }

    // Método esCandidato
    // Verifica si un usuario tiene el rol de "Candidato".
    // Parámetros:
    // - $id: ID del usuario.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si el usuario es candidato, false en caso contrario.
    public static function esCandidato($id, $db) {
        $stmt = $db->prepare("SELECT rol FROM Usuario WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($usuario && $usuario['rol'] === 'Candidato');
    }

    // Método ofertaDisponible
    // Verifica si una oferta laboral está disponible (estado 'Vigente').
    // Parámetros:
    // - $oferta_id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la oferta está disponible, false en caso contrario.
    public static function ofertaDisponible($oferta_id, $db) {
        $stmt = $db->prepare("SELECT estado FROM OfertaLaboral WHERE id = :id");
        $stmt->bindParam(':id', $oferta_id);
        $stmt->execute();
        $oferta = $stmt->fetch(PDO::FETCH_ASSOC);

        // Solo permite si el estado es exactamente 'Vigente'
        return ($oferta && $oferta['estado'] === 'Vigente');
    }

    // Método obtenerOferta
    // Obtiene los datos de una oferta laboral específica.
    // Parámetros:
    // - $oferta_id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con los datos de la oferta laboral o null si no existe.
    public static function obtenerOferta($oferta_id, $db) {
        $stmt = $db->prepare("SELECT * FROM OfertaLaboral WHERE id = :id");
        $stmt->bindParam(':id', $oferta_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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