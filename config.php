<?php
session_start();

define('APP_NAME', 'Galería PHP');
define('DB_FILE', __DIR__ . '/data/app.sqlite');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);

if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0775, true);
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

function init_db(): void {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    db()->exec($sql);
    $count = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Administrador', 'admin@demo.com', password_hash('Admin123!', PASSWORD_DEFAULT), 'admin']);
        $stmt->execute(['Usuario Demo', 'user@demo.com', password_hash('User123!', PASSWORD_DEFAULT), 'user']);
    }
}
init_db();

function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_admin(): array {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado');
    }
    return $user;
}

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(419);
            exit('Token CSRF inválido');
        }
    }
}

function flash(string $message, string $type='ok'): void { $_SESSION['flash'] = [$message, $type]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function user_upload_dir(int $user_id): string {
    $dir = UPLOAD_DIR . '/user_' . $user_id;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    return $dir;
}

function delete_image_record(int $image_id, ?int $owner_id = null): bool {
    $sql = 'SELECT * FROM images WHERE id = ?';
    $params = [$image_id];
    if ($owner_id !== null) { $sql .= ' AND user_id = ?'; $params[] = $owner_id; }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $image = $stmt->fetch();
    if (!$image) return false;
    $path = user_upload_dir((int)$image['user_id']) . '/' . $image['filename'];
    if (is_file($path)) unlink($path);
    db()->prepare('DELETE FROM images WHERE id = ?')->execute([$image_id]);
    return true;
}
