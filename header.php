<?php $user = current_user(); $flash = get_flash(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">📷 <?= APP_NAME ?></a>
  <button class="menu-btn" id="menuBtn" aria-label="Abrir menú">☰</button>
  <nav id="nav">
    <?php if ($user): ?>
      <a href="index.php">Mi galería</a>
      <?php if ($user['role'] === 'admin'): ?><a href="admin.php">Administración</a><?php endif; ?>
      <span class="user-chip"><?= e($user['name']) ?> · <?= e($user['role']) ?></span>
      <a href="logout.php">Salir</a>
    <?php else: ?>
      <a href="login.php">Entrar</a>
      <a href="register.php">Registrarse</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php if ($flash): ?><div class="alert <?= e($flash[1]) ?>"><?= e($flash[0]) ?></div><?php endif; ?>
