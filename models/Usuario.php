<?php
// Clase Usuario
// Modelo encargado de gestionar las operaciones relacionadas con los usuarios.
class Usuario {

    // Método buscarPorEmail
    // Busca un usuario en la base de datos utilizando su correo electrónico.
    // Parámetros:
    // - $email: Correo electrónico del usuario.
    // - $db: Conexión a la base de datos.
    // Retorna:
    // - Un array con los datos del usuario si existe, o false si no se encuentra.
    public static function buscarPorEmail($email, $db) {
        $stmt = $db->prepare("SELECT * FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>