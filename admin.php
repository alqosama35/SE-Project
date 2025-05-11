<?php
session_start();
require_once 'model/Admin.php';
require_once 'model/Feedback.php';
require_once 'model/Contact.php';
require_once 'model/Restoration.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof Admin)) {
    header('Location: login.php');
    exit();
}

$admin = $_SESSION['user'];
$action = $_GET['action'] ?? 'dashboard';

// Handle different admin actions
switch ($action) {
    case 'dashboard':
        // Get dashboard statistics
        $totalVisitors = 15250; // This should come from database
        $totalRevenue = 45250; // This should come from database
        $activeExhibits = 12; // This should come from database
        $ticketsSold = 200; // This should come from database
        
        include 'view/dashboard/index.html';
        break;

    case 'manage-users':
        // Handle user management
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'edit':
                        // Handle user edit
                        break;
                    case 'delete':
                        // Handle user deletion
                        break;
                }
            }
        }
        include 'view/dashboard/manage-users.html';
        break;

    case 'manage-content':
        // Handle content management
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'edit':
                        // Handle content edit
                        break;
                    case 'delete':
                        // Handle content deletion
                        break;
                }
            }
        }
        include 'view/dashboard/manage-content.html';
        break;

    case 'registrations':
        // Handle registration approvals
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'approve':
                        // Handle registration approval
                        break;
                    case 'reject':
                        // Handle registration rejection
                        break;
                }
            }
        }
        include 'view/dashboard/registrations.html';
        break;

    case 'system-reports':
        // Handle system reports
        $period = $_GET['period'] ?? 'daily';
        // Get report data based on period
        include 'view/dashboard/system-reports.html';
        break;

    case 'feedback':
        // Handle feedback management
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'respond':
                        // Handle feedback response
                        break;
                    case 'delete':
                        // Handle feedback deletion
                        break;
                }
            }
        }
        include 'view/dashboard/feedback.html';
        break;

    case 'logout':
        // Handle logout
        session_destroy();
        header('Location: login.php');
        exit();
        break;

    default:
        // Redirect to dashboard for unknown actions
        header('Location: admin.php?action=dashboard');
        exit();
        break;
} 