<?php

// Habilitar CORS y configurar headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Incluir dependencias
require_once './controllers/UsuarioController.php';
require_once './controllers/OfertaLaboralController.php';
require_once './controllers/PostulacionController.php';
require_once './controllers/EstadoPostulacionController.php';
require_once './controllers/ComentarioController.php';
require_once './controllers/ReporteController.php';
require_once './config/database.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear instancias de controladores
$usuarioController = new UsuarioController($db);
$ofertaController = new OfertaLaboralController($db);
$postulacionController = new PostulacionController($db);
$estadoPostulacionController = new EstadoPostulacionController($db);
$comentarioController = new ComentarioController($db);
$reporteController = new ReporteController($db);

// Obtener ruta y método
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Procesar la URI para obtener la ruta
$uri = preg_replace('/^.*index\.php/', '', $requestUri);
$uri = explode('?', $uri)[0];
$uri = trim($uri, "/");

// Enrutador principal
switch ($uri) {
    // Ruta principal
    case '':
        echo json_encode([
            "message" => "Bienvenido a la API de Cliente Feliz",
            "endpoints" => [
                "Usuario",
                "POST /login",
                "POST /usuario/registro",
                "GET /usuario/listar",

                "Ofertas",
                "POST /ofertas/crear",
                "GET /ofertas/todas",
                "GET /ofertas/activas",
                "PUT /ofertas/editar/{id}",
                "PATCH|PUT /ofertas/desactivar/{id}",
                "GET /ofertas/reclutador/{id}",

                "Postulaciones",
                "POST /postulacion",
                "GET /postulaciones/candidato/{id}",

                "Estados de Postulación",
                "GET /estado-postulacion",
                "PUT /estado-postulacion/cambiar/{id}",

                "Comentarios",
                "PATCH /comentario/agregar/{id}",
                "PATCH /comentario/eliminar/{id}",

                "Reportes",
                "GET /reporte/postulantes/oferta/{id}",
                "GET /reporte/total/oferta/{id}",
                "GET /reporte/postulaciones/reclutador/{id}"
            ]
        ]);
        break;

    // Rutas relacionadas con usuarios
    case 'login':
        if ($method === 'POST') {
            $usuarioController->login();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case 'usuario/registro':
        if ($method === 'POST') {
            $usuarioController->registro();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case 'usuario/listar':
        if ($method === 'GET') {
            $usuarioController->listarUsuarios();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Rutas relacionadas con ofertas laborales
    case 'ofertas/crear':
        if ($method === 'POST') {
            $ofertaController->crear();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case 'ofertas/todas':
        if ($method === 'GET') {
            $ofertaController->listarTodas();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case 'ofertas/activas':
        if ($method === 'GET') {
            $ofertaController->listarActivas();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case preg_match('/^ofertas\/editar\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'PUT') {
            $ofertaController->editar($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case preg_match('/^ofertas\/desactivar\/(\d+)$/', $uri, $matches) ? true : false:
        if (in_array($method, ['PATCH', 'PUT'])) {
            $ofertaController->desactivar($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case preg_match('/^ofertas\/reclutador\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'GET') {
            $ofertaController->listarPorReclutador($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Rutas relacionadas con postulaciones
    case 'postulacion':
        if ($method === 'POST') {
            $postulacionController->registrarPostulacion();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case preg_match('/^postulaciones\/candidato\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'GET') {
            $postulacionController->verPostulacionesPorCandidato($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Rutas relacionadas con estados de postulaciones
    case preg_match('/^estado-postulacion\/cambiar\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'PUT') {
            $estadoPostulacionController->cambiarEstado($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case 'estado-postulacion':
        if ($method === 'GET') {
            $estadoPostulacionController->obtenerEstados();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Rutas relacionadas con comentarios
    case preg_match('/^comentario\/agregar\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'PATCH') {
            $comentarioController->agregar($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case preg_match('/^comentario\/eliminar\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'PATCH') {
            $comentarioController->eliminar($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Rutas relacionadas con reportes
    case preg_match('/^reporte\/postulantes\/oferta\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'GET') {
            $reporteController->postulantesPorOferta($matches[1]);
        }
        break;

    case preg_match('/^reporte\/total\/oferta\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'GET') {
            $reporteController->totalPostulacionesPorOferta($matches[1]);
        }
        break;

    case preg_match('/^reporte\/postulaciones\/reclutador\/(\d+)$/', $uri, $matches) ? true : false:
        if ($method === 'GET') {
            $reporteController->postulacionesPorReclutador($matches[1]);
        }
        break;

    // Ruta no encontrada
    default:
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
        break;
}
?>