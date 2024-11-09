<?php

class AuthHelper {

    public static function isAuthenticated() {
        if (!isset($_SESSION['user_token'])) {
            return false;
        }
        return true;
    }

    public static function loginUser($user) {

        if (!$user || !$user["hasAccess"]){
            return false;
        }

        $_SESSION['user_token'] = [
            'user_id' => $user["id"],
            'username' => $user["username"],
            'rol_id' => $user["rol_id"],
            'email' => $user["email"],
            'name' => $user["name"],
            'profile_picture' => $user["profile_picture"],
            'hasAccess' => $user["hasAccess"],
        ];

        return true;
    }

    public static function logout() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, //segun el chat esto destruye todas las cookis de los parametro deberia verificarlo con info mas real
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function getUser() {
        return $_SESSION['user_token'] ?? null;
    }

    public static function getRolId() {
        return $_SESSION['user_token']["rol_id"] ?? null;
    }
}
