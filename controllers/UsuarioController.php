<?php
require_once './models/Usuario.php';

// Clase UsuarioController
// Controlador encargado de gestionar las operaciones relacionadas con los usuarios, como login, registro y listado.
class UsuarioController {
    private $db; // Conexión a la base de datos

    // Constructor
    // Inicializa la conexión a la base de datos.
    public function __construct($db) {
        $this->db = $db;
    }

    // Método login
    // Permite a un usuario iniciar sesión verificando sus credenciales.
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Validar que los campos no estén vacíos
        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos"]);
            return;
        }

        // Buscar el usuario por email
        $usuario = Usuario::buscarPorEmail($email, $this->db);

        // Verificar si el usuario existe y si la contraseña es correcta
        if (!$usuario || !password_verify($password, $usuario['contraseña'])) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas"]);
            return;
        }

        // Respuesta exitosa con los datos del usuario
        echo json_encode([
            "mensaje" => "Login exitoso",
            "usuario" => [
                "id" => $usuario['id'],
                "nombre" => $usuario['nombre'],
                "rol" => $usuario['rol']
            ]
        ]);
    }

    // Método registro
    // Permite registrar un nuevo usuario en la base de datos.
    public function registro() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validación básica de campos requeridos
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $rol = $data['rol'] ?? '';

        if (!$nombre || !$apellido || !$email || !$password || !$rol) {
            http_response_code(400);
            echo json_encode(["error" => "Todos los campos son obligatorios"]);
            return;
        }

        // Validar que el nombre y apellido solo contengan letras
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre)) {
            http_response_code(400);
            echo json_encode(["error" => "El nombre solo debe contener letras"]);
            return;
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $apellido)) {
            http_response_code(400);
            echo json_encode(["error" => "El apellido solo debe contener letras"]);
            return;
        }

        // Validar el formato del email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Formato de email no válido"]);
            return;
        }

        // Verificar si el email ya existe
        $stmt = $this->db->prepare("SELECT id FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "Email duplicado"]);
            return;
        }

        // Validar el rol permitido
        $rolesPermitidos = ['Candidato', 'Reclutador'];
        if (!in_array($rol, $rolesPermitidos)) {
            http_response_code(400);
            echo json_encode(["error" => "El rol debe ser 'Candidato' o 'Reclutador'"]);
            return;
        }

        // Hashear la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar el usuario en la base de datos
        $stmt = $this->db->prepare("INSERT INTO Usuario (nombre, apellido, email, contraseña, rol) 
                                    VALUES (:nombre, :apellido, :email, :password, :rol)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);

        // Verificar si la inserción fue exitosa
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["mensaje" => "Usuario registrado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar el usuario"]);
        }
    }

    // Método listarUsuarios
    // Devuelve una lista de todos los usuarios registrados en la base de datos.
    public function listarUsuarios() {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, apellido, email, rol, estado, fecha_registro FROM Usuario");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Respuesta con la lista de usuarios
            echo json_encode([
                "usuarios" => $usuarios
            ]);
        } catch (PDOException $e) {
            // Manejo de errores en caso de fallo en la consulta
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener los usuarios"]);
        }
    }
}
?>
