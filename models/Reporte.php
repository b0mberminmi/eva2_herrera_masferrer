<?php

// Clase Reporte
// Modelo encargado de gestionar los reportes relacionados con postulaciones y ofertas laborales.
class Reporte {

    // Método obtenerPostulantesPorOferta
    // Obtiene una lista de postulantes asociados a una oferta laboral específica.
    // Parámetros:
    // - $id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con los datos de la oferta y los postulantes, o null si la oferta no existe.
    public static function obtenerPostulantesPorOferta($id, $db) {
        // Obtener nombre y fecha de la oferta
        $ofertaStmt = $db->prepare("SELECT titulo, fecha_publicacion FROM OfertaLaboral WHERE id = :id");
        $ofertaStmt->bindParam(':id', $id);
        $ofertaStmt->execute();
        $oferta = $ofertaStmt->fetch(PDO::FETCH_ASSOC);

        if (!$oferta) {
            return null; // La oferta no existe
        }

        // Obtener postulantes asociados a la oferta
        $stmt = $db->prepare("
            SELECT U.id, U.nombre, U.apellido, P.estado_postulacion, P.comentario
            FROM Postulacion P
            JOIN Usuario U ON P.candidato_id = U.id
            WHERE P.oferta_laboral_id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $postulantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "oferta" => [
                "titulo" => $oferta['titulo'],
                "fecha_publicacion" => $oferta['fecha_publicacion']
            ],
            "postulantes" => $postulantes
        ];
    }

    // Método contarPostulacionesPorOferta
    // Cuenta el número total de postulaciones realizadas para una oferta laboral específica.
    // Parámetros:
    // - $id: ID de la oferta laboral.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con el título de la oferta, la fecha de publicación y el total de postulaciones, o null si la oferta no existe.
    public static function contarPostulacionesPorOferta($id, $db) {
        // Obtener título y fecha de la oferta
        $stmtOferta = $db->prepare("SELECT titulo, fecha_publicacion FROM OfertaLaboral WHERE id = :id");
        $stmtOferta->bindParam(':id', $id);
        $stmtOferta->execute();
        $oferta = $stmtOferta->fetch(PDO::FETCH_ASSOC);

        if (!$oferta) {
            return null; // La oferta no existe
        }

        // Contar postulaciones asociadas a la oferta
        $stmtCount = $db->prepare("SELECT COUNT(*) AS total FROM Postulacion WHERE oferta_laboral_id = :id");
        $stmtCount->bindParam(':id', $id);
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC);

        return [
            "titulo" => $oferta['titulo'],
            "fecha_publicacion" => $oferta['fecha_publicacion'],
            "total_postulaciones" => $total['total']
        ];
    }

    // Método obtenerNombreReclutador
    // Obtiene el nombre y apellido de un reclutador específico.
    // Parámetros:
    // - $id: ID del reclutador.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con el nombre y apellido del reclutador, o null si no existe.
    public static function obtenerNombreReclutador($id, $db) {
        $stmt = $db->prepare("SELECT nombre, apellido FROM Usuario WHERE id = :id AND rol = 'Reclutador'");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método obtenerPostulacionesPorReclutador
    // Obtiene una lista de postulaciones asociadas a un reclutador específico.
    // Parámetros:
    // - $reclutador_id: ID del reclutador.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con las postulaciones asociadas al reclutador.
    public static function obtenerPostulacionesPorReclutador($reclutador_id, $db) {
        $stmt = $db->prepare("
            SELECT P.id, U.nombre, U.apellido, O.titulo, P.estado_postulacion
            FROM Postulacion P
            JOIN Usuario U ON P.candidato_id = U.id
            JOIN OfertaLaboral O ON P.oferta_laboral_id = O.id
            WHERE O.reclutador_id = :id
        ");
        $stmt->bindParam(':id', $reclutador_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>