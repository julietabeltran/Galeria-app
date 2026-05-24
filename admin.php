<?php require __DIR__ . '/config.php'; $admin = require_admin(); verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_image') {
        delete_image_record((int)$_POST['image_id']);
        flash('Imagen eliminada por administración.');
    }
    if ($action === 'update_user') {
        $id = (int)$_POST['user_id'];
        $name = trim($_POST['name']);
        $email = strtolower(trim($_POST['email']));
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        if ($id === (int)$admin['id'] && $role !== 'admin') {
            flash('No puedes quitarte tu propio rol de administrador.', 'error');
        } else {
            db()->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?')->execute([$name, $email, $role, $id]);
            flash('Usuario actualizado.');
        }
    }
    if ($action === 'delete_user') {
        $id = (int)$_POST['user_id'];
        if ($id === (int)$admin['id']) flash('No puedes eliminar tu propia cuenta.', 'error');
        else {
            $stmt = db()->prepare('SELECT id FROM images WHERE user_id=?');
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $img) delete_image_record((int)$img['id']);
            db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
            @rmdir(user_upload_dir($id));
            flash('Usuario eliminado.');
        }
    }
    header('Location: admin.php'); exit;
}
$users = db()->query('SELECT u.*, COUNT(i.id) total_images FROM users u LEFT JOIN images i ON i.user_id=u.id GROUP BY u.id ORDER BY u.created_at DESC')->fetchAll();
$images = db()->query('SELECT i.*, u.name user_name, u.email user_email FROM images i JOIN users u ON u.id=i.user_id ORDER BY i.created_at DESC')->fetchAll();
require __DIR__ . '/header.php'; ?>
<h1>Panel de administración</h1>
<section class="panel table-wrap">
  <h2>Usuarios</h2>
  <table>
    <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Imágenes</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="update_user">
          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          <td><input name="name" value="<?= e($u['name']) ?>" required></td>
          <td><input name="email" type="email" value="<?= e($u['email']) ?>" required></td>
          <td><select name="role"><option value="user" <?= $u['role']==='user'?'selected':'' ?>>Usuario</option><option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option></select></td>
          <td><?= (int)$u['total_images'] ?></td>
          <td class="actions"><button class="btn">Guardar</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Eliminar usuario y todas sus imágenes?')">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          <button class="btn danger">Borrar</button></td>
        </form>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<section class="gallery admin-gallery">
<?php foreach ($images as $img): ?>
  <article class="card">
    <img src="uploads/user_<?= (int)$img['user_id'] ?>/<?= e($img['filename']) ?>" alt="">
    <div class="card-body"><h3><?= e($img['title'] ?: $img['original_name']) ?></h3><p><?= e($img['user_name']) ?> · <?= e($img['user_email']) ?></p>
      <form method="post" onsubmit="return confirm('¿Eliminar esta imagen?')"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_image"><input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button class="btn danger">Eliminar</button></form>
    </div>
  </article>
<?php endforeach; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
