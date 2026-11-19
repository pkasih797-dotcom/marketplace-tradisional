<?php
/** @var mysqli $conn */
include '../config/koneksi.php';
$id = (int) $_GET['id'];
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id='$id'"));
if (!$p) { echo "Produk tidak ditemukan"; exit; }

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = (int) $_POST['kategori_id'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    // --- INI YANG DITAMBAH, YANG BIKIN FOTO BISA MUNCUL ---
    $gambar_baru = $p['gambar']; // Kalau gak upload foto baru, pakai foto lama

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0 && $_FILES['foto']['size'] > 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $folder = __DIR__ . '/../uploads/';
            if (!is_dir($folder)) mkdir($folder, 0777, true);

            // Pakai nama lama biar nyambung sama database: pendap.jpg tetap pendap.jpg
            if (!empty($p['gambar'])) {
                $gambar_baru = basename($p['gambar']);
            } else {
                $gambar_baru = 'produk-' . $id . '.' . $ext;
            }
            move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $gambar_baru);
        }
    }
    // --- SAMPAI SINI TAMBAHANNYA ---

    // Query-nya ditambah gambar='$gambar_baru'
    mysqli_query($conn, "UPDATE products SET nama_produk='$nama', kategori_id='$kategori', deskripsi='$deskripsi', harga='$harga', stok='$stok', gambar='$gambar_baru' WHERE id='$id'");
    echo "<script>alert('Berhasil diupdate!'); window.location='produk.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Edit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-4" style="max-width:600px;">
<a href="produk.php" class="btn btn-secondary btn-sm">Kembali</a><h3 class="mt-3">Edit: <?php echo $p['nama_produk']; ?></h3>

<!-- Foto sekarang -->
<?php
$foto_path = '../uploads/' . basename($p['gambar'] ?? '');
$foto_ada = !empty($p['gambar']) && file_exists(__DIR__ . '/../uploads/' . basename($p['gambar']));
?>
<div class="card mb-3"><div class="card-body text-center">
  <?php if ($foto_ada) { ?>
    <img src="<?php echo $foto_path; ?>" style="max-width:200px;max-height:200px;object-fit:cover;border-radius:12px">
    <br><small class="text-success">✅ Foto sudah ada: <?php echo $p['gambar']; ?></small>
  <?php } else { ?>
    <div style="font-size:40px">📸</div>
    <small class="text-danger">❌ Foto belum ada! Di database: <b><?php echo $p['gambar']; ?></b> tapi file-nya gak ada di uploads/.<br>Upload di bawah ini.</small>
  <?php } ?>
</div></div>

<!-- YANG DIPERBAIKI: tambah enctype="multipart/form-data" -->
<form method="POST" enctype="multipart/form-data">
<input type="text" name="nama_produk" value="<?php echo $p['nama_produk']; ?>" class="form-control mb-3" required>
<select name="kategori_id" class="form-control mb-3">
<?php $k = mysqli_query($conn, "SELECT * FROM categories"); while ($c = mysqli_fetch_assoc($k)) { $sel = ($c['id'] == $p['kategori_id']) ? 'selected' : ''; echo "<option value='{$c['id']}' $sel>{$c['nama_kategori']}</option>"; } ?>
</select>
<textarea name="deskripsi" class="form-control mb-3" required><?php echo $p['deskripsi']; ?></textarea>
<input type="number" name="harga" value="<?php echo $p['harga']; ?>" class="form-control mb-3" required>
<input type="number" name="stok" value="<?php echo $p['stok']; ?>" class="form-control mb-3" required>

<!-- YANG DITAMBAH: INPUT FOTO -->
<label class="fw-bold small">Foto Produk (Pilih Foto Pendap Asli Kamu)</label>
<input type="file" name="foto" accept="image/*" class="form-control mb-3">

<button name="update" class="btn btn-warning w-100">Update</button>
</form></div></body></html>