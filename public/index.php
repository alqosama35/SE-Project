<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Database;
use App\Models\Visitor;
use App\Controllers\VisitorController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Start session
session_start();

// Initialize database connection
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle routing
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$uri = parse_url($uri, PHP_URL_PATH);

// Basic routing
switch ($uri) {
    case '/':
        $controller = new VisitorController();
        $controller->index();
        break;
        
    case '/visitors':
        $controller = new VisitorController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->store();
        }
        break;
        
    case '/visitors/create':
        $controller = new VisitorController();
        $controller->create();
        break;
        
    default:
        if (preg_match('/^\/visitors\/(\d+)$/', $uri, $matches)) {
            $controller = new VisitorController();
            $id = $matches[1];
            
            if ($method === 'GET') {
                $controller->show($id);
            } elseif ($method === 'POST') {
                $controller->update($id);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Page not found";
        }
        break;
} 