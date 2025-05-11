<?php
// Clase EstadosPostulacion
// Modelo encargado de gestionar las operaciones relacionadas con los estados de las postulaciones.
class EstadosPostulacion {

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

    // Método actualizarEstado
    // Actualiza el estado de una postulación en la base de datos.
    // Parámetros:
    // - $id: ID de la postulación.
    // - $estado: Nuevo estado de la postulación.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - true si la operación fue exitosa, false en caso contrario.
    public static function actualizarEstado($id, $estado, $db) {
        $stmt = $db->prepare("UPDATE Postulacion SET estado_postulacion = :estado WHERE id = :id");
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Método obtenerEstadosPermitidos
    // Devuelve una lista de todos los estados válidos para las postulaciones.
    // Retorna:
    // - Un array con los estados permitidos.
    public static function obtenerEstadosPermitidos() {
        return [
            "Postulando",
            "Revisando",
            "Entrevista Psicológica",
            "Entrevista Personal",
            "Seleccionado",
            "Descartado"
        ];
    }
}
?>