<?php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('config')) {
    function config($key, $default = null) {
        $config = require __DIR__ . '/../config/' . $key . '.php';
        return $config ?? $default;
    }
}

if (!function_exists('view')) {
    function view($name, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$name}.php";
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('session')) {
    function session($key = null, $value = null) {
        if ($key === null) {
            return $_SESSION;
        }
        
        if ($value === null) {
            return $_SESSION[$key] ?? null;
        }
        
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('flash')) {
    function flash($key, $message = null) {
        if ($message === null) {
            $message = session($key);
            unset($_SESSION[$key]);
            return $message;
        }
        
        session($key, $message);
    }
}

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        return '/public/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
    }
} 