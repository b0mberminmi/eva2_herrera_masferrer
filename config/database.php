<?php

// Clase Database
// Modelo encargado de gestionar la conexión a la base de datos.
class Database {
    // Propiedades de configuración de la base de datos
    private $host = 'localhost'; // Dirección del servidor de la base de datos
    private $db_name = 'cliente_feliz'; // Nombre de la base de datos
    private $username = 'root'; // Usuario de la base de datos
    private $password = ''; // Contraseña del usuario de la base de datos
    public $conn; // Conexión a la base de datos

    // Método getConnection
    // Establece la conexión a la base de datos utilizando PDO.
    // Retorna:
    // - Un objeto PDO si la conexión es exitosa.
    // - null si ocurre un error durante la conexión.
    public function getConnection() {
        $this->conn = null; // Inicializar la conexión como null
        try {
            // Crear una nueva conexión PDO
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            // Configurar el modo de error de PDO para excepciones
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Manejar errores de conexión
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn; // Retornar la conexión
    }
}
?>