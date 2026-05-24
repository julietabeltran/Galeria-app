<?php require __DIR__ . '/config.php'; verify_csrf();
if (current_user()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        flash('Bienvenido/a, ' . $user['name']);
        header('Location: index.php'); exit;
    }
    $error = 'Correo o contraseña incorrectos.';
}
require __DIR__ . '/header.php'; ?>
<section class="auth-card">
  <h1>Iniciar sesión</h1>
  <p class="muted">Demo admin: admin@demo.com / Admin123! · Demo usuario: user@demo.com / User123!</p>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>Correo<input name="email" type="email" required autocomplete="email"></label>
    <label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label>
    <button class="btn primary">Entrar</button>
  </form>
</section>
<?php require __DIR__ . '/footer.php'; ?>
