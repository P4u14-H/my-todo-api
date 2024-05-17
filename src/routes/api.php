<?php
// API.php

// Lade die erforderlichen Klassen und Konfigurationen
require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Todo.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/TodoController.php';

// Initialisiere die Datenbankverbindung
$db = (new Database())->getConnection();

// Erstelle Instanzen der Controller
$authController = new AuthController($db);
$todoController = new TodoController($db);

// Definiere die Routen
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parsing der Anfrage-URL
$uriSegments = explode('/', trim($requestUri, '/'));

// Die ersten beiden Segmente in der URI sind immer leer und der Name des Skripts (z.B. index.php)
// Die dritten Segment entspricht dem Endpunkt der API
$endpoint = $uriSegments[2];

// Je nach Endpunkt und HTTP-Methode wird der entsprechende Controller und die Methode aufgerufen
switch ($endpoint) {
    case 'login':
        if ($requestMethod === 'POST') {
            $authController->login();
        }
        break;
    case 'todos':
        if ($requestMethod === 'GET') {
            $todoController->read();
        } elseif ($requestMethod === 'POST') {
            $todoController->create();
        }
        break;
    case 'todos/{id}':
        $todoId = $uriSegments[3];
        if ($requestMethod === 'GET') {
            $todoController->readById($todoId);
        } elseif ($requestMethod === 'PUT') {
            $todoController->update($todoId);
        } elseif ($requestMethod === 'DELETE') {
            $todoController->delete($todoId);
        }
        break;
    default:
        // Falls der Endpunkt nicht gefunden wurde
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found"]);
        break;
}
