<?php require __DIR__ . '/config.php'; verify_csrf();
if (current_user()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Completa los datos correctamente. La contraseña debe tener mínimo 8 caracteres.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'user']);
            flash('Cuenta creada. Ya puedes iniciar sesión.');
            header('Location: login.php'); exit;
        } catch (PDOException $e) { $error = 'Ese correo ya está registrado.'; }
    }
}
require __DIR__ . '/header.php'; ?>
<section class="auth-card">
  <h1>Crear cuenta</h1>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>Nombre<input name="name" required minlength="2"></label>
    <label>Correo<input name="email" type="email" required></label>
    <label>Contraseña<input name="password" type="password" required minlength="8"></label>
    <button class="btn primary">Registrarme</button>
  </form>
</section>
<?php require __DIR__ . '/footer.php'; ?>
