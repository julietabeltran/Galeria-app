<?php require __DIR__ . '/config.php'; $user = require_login(); verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            flash('No se pudo subir el archivo.', 'error');
        } elseif ($_FILES['image']['size'] > MAX_UPLOAD_BYTES) {
            flash('La imagen no debe superar 5 MB.', 'error');
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $info = getimagesize($tmp);
            $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'];
            if (!$info || !isset($allowed[$info['mime']])) {
                flash('Formato no permitido. Usa JPG, PNG, GIF o WEBP.', 'error');
            } else {
                $ext = $allowed[$info['mime']];
                $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
                $dest = user_upload_dir((int)$user['id']) . '/' . $safeName;
                if (move_uploaded_file($tmp, $dest)) {
                    $stmt = db()->prepare('INSERT INTO images (user_id, original_name, filename, mime, size, title) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$user['id'], $_FILES['image']['name'], $safeName, $info['mime'], $_FILES['image']['size'], trim($_POST['title'] ?? '')]);
                    flash('Imagen agregada correctamente.');
                } else flash('No fue posible guardar la imagen.', 'error');
            }
        }
        header('Location: index.php'); exit;
    }
    if ($action === 'delete') {
        delete_image_record((int)$_POST['image_id'], (int)$user['id']);
        flash('Imagen eliminada.');
        header('Location: index.php'); exit;
    }
}
$stmt = db()->prepare('SELECT * FROM images WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$images = $stmt->fetchAll();
require __DIR__ . '/header.php'; ?>
<section class="hero">
  <div><h1>Mi galería</h1><p>Agrega, visualiza y elimina tus imágenes. Solo tú puedes ver estos archivos.</p></div>
</section>
<section class="panel">
  <h2>Subir nueva imagen</h2>
  <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="upload">
    <label>Título opcional<input name="title" maxlength="80" placeholder="Ej. Vacaciones"></label>
    <label class="dropzone">Seleccionar imagen<input name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required id="imageInput"></label>
    <img id="preview" class="preview" alt="Vista previa" hidden>
    <button class="btn primary">Subir imagen</button>
  </form>
</section>
<section class="gallery">
<?php if (!$images): ?>
  <div class="empty">Aún no tienes imágenes. Sube la primera.</div>
<?php endif; ?>
<?php foreach ($images as $img): ?>
  <article class="card">
    <img src="uploads/user_<?= (int)$img['user_id'] ?>/<?= e($img['filename']) ?>" alt="<?= e($img['title'] ?: $img['original_name']) ?>" loading="lazy">
    <div class="card-body">
      <h3><?= e($img['title'] ?: $img['original_name']) ?></h3>
      <p><?= round($img['size']/1024, 1) ?> KB · <?= e($img['created_at']) ?></p>
      <form method="post" onsubmit="return confirm('¿Eliminar esta imagen?')">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
        <button class="btn danger">Eliminar</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
