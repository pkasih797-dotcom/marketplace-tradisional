<?php
require_once __DIR__ . '/config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
if (!$row) { die('Produk tidak ditemukan'); }

// CARI FOTO - NYARI KE SEMUA TEMPAT & SEMUA NAMA YANG MUNGKIN
function cari_foto($nama_produk) {
    $nama_produk = strtolower(trim($nama_produk));
    if (str_contains($nama_produk, 'pendap')) $kunci = 'pendap';
    elseif (str_contains($nama_produk, 'lemang') || str_contains($nama_produk, 'tapai')) $kunci = 'lemang';
    elseif (str_contains($nama_produk, 'baju') || str_contains($nama_produk, 'pengantin') || str_contains($nama_produk, 'adat')) $kunci = 'baju';
    elseif (str_contains($nama_produk, 'batik') || str_contains($nama_produk, 'besurek')) $kunci = 'batik';
    else $kunci = 'pendap';

    // Coba semua kemungkinan lokasi + nama + ekstensi
    $coba = [];
    foreach (['uploads/', '', 'images/', 'img/', 'foto/'] as $folder) {
        foreach ([$kunci, ucfirst($kunci), strtoupper($kunci)] as $n) {
            foreach (['jpg','jpeg','png','webp','JPG','JPEG','PNG'] as $ext) {
                $coba[] = $folder . $n . '.' . $ext;
                $coba[] = $folder . $n . ' (1).' . $ext;
            }
        }
    }
    foreach ($coba as $file) {
        if (file_exists(__DIR__ . '/' . $file)) return $file;
    }
    return false;
}

$foto = cari_foto($row['nama_produk']);
// Kalau file beneran gak ada sama sekali, tampilkan placeholder biar gak ikon rusak, + kasih tau file apa yang dicari
if (!$foto) {
    $label = urlencode($row['nama_produk']);
    $foto = "https://via.placeholder.com/800x600/15803d/ffffff?text=" . $label;
    $pesan_error = "File foto untuk <b>" . $row['nama_produk'] . "</b> belum ketemu di folder uploads. Simpan foto dari WhatsApp ke folder <b>uploads/</b> dengan nama <b>pendap.jpg / lemang.jpg / baju.jpg / batik.jpg</b>";
} else {
    $pesan_error = "";
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $row['nama_produk'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}.header{background:white;padding:16px 0}.logo{font-weight:800;font-size:22px;text-decoration:none;color:#0d7a3e}.logo span{color:#f59e0b}.foto-box{border-radius:20px;overflow:hidden;background:white;height:450px}.foto-box img{width:100%;height:100%;object-fit:cover}.detail-card{background:white;border-radius:20px;padding:28px}.harga{color:#15803d;font-weight:800;font-size:32px}.btn-tambah{background:#15803d;color:white;border:none;border-radius:10px;padding:14px;font-weight:700;width:100%}</style>
</head><body>
<div class="header"><div class="container d-flex justify-content-between"><a href="index.php" class="logo">🌿 Bengkulu<span>Mart</span></a><a href="cart.php">🛒 Keranjang</a></div></div>
<div class="container py-4"><a href="index.php">← Kembali</a>
<?php if($pesan_error): ?><div class="alert alert-danger mt-3"><?= $pesan_error ?></div><?php endif; ?>
<div class="row mt-3"><div class="col-md-6"><div class="foto-box"><img src="<?= $foto ?>" alt="<?= $row['nama_produk'] ?>"></div><small class="text-muted">File yang dipakai: <?= $foto ?></small></div>
<div class="col-md-6"><div class="detail-card"><span style="background:#0d7a3e;color:white;padding:6px 14px;border-radius:999px">Produk Bengkulu</span><h2 class="mt-3"><?= $row['nama_produk'] ?></h2><div class="harga">Rp <?= number_format($row['harga'],0,',','.') ?></div><hr><p><?= $row['deskripsi'] ?></p><p>Stok: <b><?= $row['stok'] ?></b></p><form method="POST" action="cart.php"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="btn-tambah">+ Tambah Keranjang</button></form></div></div></div></div>
</body></html>