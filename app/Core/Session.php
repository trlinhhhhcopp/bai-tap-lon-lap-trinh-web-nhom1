<?php
namespace App\Core;

class Session {
    public static function start(): void {
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../../config/app.php';
            session_name($config['session_name']);
            @session_start();
        }
    }

    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        @session_destroy();
    }

    // Flash Message
    public static function setFlash(string $type, string $message): void {
        self::set('_flash', ['type' => $type, 'message' => $message]);
    }

    public static function getFlash(): ?array {
        $flash = self::get('_flash');
        self::remove('_flash');
        return $flash;
    }

    // Auth helpers
    public static function setUser(array $user): void {
        self::set('user', $user);
    }

    public static function user(): ?array {
        return self::get('user');
    }

    public static function check(): bool {
        return self::has('user') && !empty(self::get('user'));
    }

    public static function id(): ?int {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    public static function role(): ?string {
        $user = self::user();
        return $user ? $user['role'] : null;
    }
}

