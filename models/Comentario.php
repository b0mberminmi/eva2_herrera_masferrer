<?php
// Clase Comentario
// Modelo encargado de gestionar las operaciones relacionadas con los comentarios en las postulaciones.
class Comentario {
    
    // Método agregarComentario
    // Agrega un comentario a una postulación específica.
    // Parámetros:
    // - $id: ID de la postulación.
    // - $comentario: Texto del comentario a agregar.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la operación fue exitosa, false en caso contrario.
    public static function agregarComentario($id, $comentario, $db) {
        $stmt = $db->prepare("UPDATE Postulacion SET comentario = :comentario WHERE id = :id");
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Método eliminarComentario
    // Elimina el comentario asociado a una postulación específica.
    // Parámetros:
    // - $id: ID de la postulación.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la operación fue exitosa, false en caso contrario.
    public static function eliminarComentario($id, $db) {
        $stmt = $db->prepare("UPDATE Postulacion SET comentario = NULL WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Método existePostulacion
    // Verifica si una postulación existe en la base de datos.
    // Parámetros:
    // - $id: ID de la postulación.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la postulación existe, false en caso contrario.
    public static function existePostulacion($id, $db) {
        $stmt = $db->prepare("SELECT id FROM Postulacion WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>