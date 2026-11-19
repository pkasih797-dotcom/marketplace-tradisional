<?php
/** @var mysqli $conn */
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Hapus produk
if(isset($_GET['hapus'])){
  $id = (int)$_GET['hapus'];
  $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM products WHERE id=$id"));
  if($cek && !empty($cek['gambar'])){
    $f_del = basename($cek['gambar']);
    if(file_exists(__DIR__ . '/../uploads/' . $f_del)) unlink(__DIR__ . '/../uploads/' . $f_del);
  }
  mysqli_query($conn, "DELETE FROM products WHERE id=$id");
  header("Location: produk.php?pesan=hapus");
  exit;
}

$cari = $_GET['cari'] ?? '';
$where = "";
if($cari != ''){
  $cari_safe = mysqli_real_escape_string($conn, $cari);
  $where = "WHERE p.nama_produk LIKE '%$cari_safe%' OR c.nama_kategori LIKE '%$cari_safe%'";
}
$q = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.kategori_id=c.id $where ORDER BY p.id DESC");

$total_produk = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$total_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as s FROM products"))['s'] ?? 0;
$total_nilai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(harga*stok) as n FROM products"))['n'] ?? 0;

/** @param array $row */
function foto_admin($row){
  $g = trim($row['gambar'] ?? '');
  if(str_starts_with($g, 'http')) return $g;
  $base = basename($g);
  if($base != '' && file_exists(__DIR__ . '/../uploads/' . $base)){
    return '../uploads/' . rawurlencode($base);
  }
  $nama = strtolower(pathinfo($base, PATHINFO_FILENAME));
  if($nama == '') $nama = strtolower(explode(' ', trim($row['nama_produk']))[0]);
  $files = glob(__DIR__ . '/../uploads/*');
  if($files){
    foreach($files as $file){
      $fn = strtolower(pathinfo($file, PATHINFO_FILENAME));
      if($fn == $nama || str_contains($fn, $nama) || str_contains($nama, $fn)){
        return '../uploads/' . rawurlencode(basename($file));
      }
    }
  }
  $map = [
    'pendap' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&q=80',
    'lemang' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=200&q=80',
    'besurek' => 'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?w=200&q=80',
    'baju' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=200&q=80',
  ];
  foreach($map as $k => $url){
    if(str_contains(strtolower($row['nama_produk']), $k)) return $url;
  }
  return 'https://via.placeholder.com/200x200?text=' . urlencode($row['nama_produk']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Produk - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}
.sidebar{background:linear-gradient(180deg,#0d7a3e,#16a34a);color:white;min-height:100vh;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px 14px;border-radius:12px;margin-bottom:6px}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,.2)}
.stat-card{background:white;border-radius:18px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);display:flex;gap:14px;align-items:center}
.stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.table-card{background:white;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.05);padding:6px}
.produk-img{width:64px;height:64px;object-fit:cover;border-radius:12px;background:#eee}
.badge-kat{background:#dcfce7;color:#15803d;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700}
.btn-tambah{background:linear-gradient(135deg,#16a34a,#f59e0b);color:white;border:none;border-radius:999px;padding:10px 22px;font-weight:700}
.btn-tambah:hover{color:white;opacity:.9}
</style>
</head>
<body>
<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar d-none d-md-block">
  <h4 class="fw-bold">🌿 Bengkulu<span style="color:#fde68a">Mart</span></h4>
  <small class="opacity-75">Admin Panel</small>
  <hr style="border-color:rgba(255,255,255,.3)">
  <a href="../index.php">🏠 Lihat Toko</a>
  <a href="produk.php" class="active">📦 Data Produk</a>
  <a href="tambah.php">➕ Tambah Produk</a>
  <a href="pesanan.php">🧾 Pesanan</a>
  <a href="kategori.php">🏷️ Kategori</a>
</div>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h3 style="font-weight:800">Data Produk</h3><p class="text-muted mb-0">Kelola Pendap, Lemang, Besurek & Baju Adat Bengkulu</p></div>
    <div class="d-flex gap-2"><a href="../index.php" class="btn bg-white border rounded-pill px-4">👁️ Lihat Toko</a><a href="tambah.php" class="btn btn-tambah">+ Tambah Produk</a></div>
  </div>
  <?php if(isset($_GET['pesan']) && $_GET['pesan']=='hapus'){ ?><div class="alert alert-success rounded-4 mt-3">✅ Produk berhasil dihapus!</div><?php } ?>
  <div class="row mt-4">
    <div class="col-md-4 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#dcfce7">📦</div><div><small class="text-muted">Total Produk</small><h4 class="fw-bold mb-0"><?= $total_produk ?> Produk</h4></div></div></div>
    <div class="col-md-4 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#fef3c7">📊</div><div><small class="text-muted">Total Stok</small><h4 class="fw-bold mb-0"><?= $total_stok ?> Pcs</h4></div></div></div>
    <div class="col-md-4 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#dbeafe">💰</div><div><small class="text-muted">Nilai Aset Toko</small><h4 class="fw-bold mb-0">Rp <?= number_format($total_nilai,0,',','.') ?></h4></div></div></div>
  </div>
  <div class="table-card mt-1">
    <form method="GET" class="d-flex gap-2 p-3">
      <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" class="form-control" placeholder="🔍 Cari Pendap, Lemang, Besurek, Baju..." style="border-radius:999px">
      <button class="btn btn-success rounded-pill px-4">Cari</button>
    </form>
    <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead style="background:#f8f9fa"><tr><th class="ps-4">Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th class="pe-4">Aksi</th></tr></thead>
      <tbody>
      <?php while($row=mysqli_fetch_assoc($q)){ ?>
      <tr>
        <td class="ps-4"><div class="d-flex align-items-center gap-3">
            <img src="<?= foto_admin($row) ?>" class="produk-img" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
            <div><b><?= htmlspecialchars($row['nama_produk']) ?></b><br><small class="text-muted">ID: <?= $row['id'] ?> • <?= htmlspecialchars(substr($row['deskripsi'] ?? '',0,35)) ?>...</small></div>
        </div></td>
        <td><span class="badge-kat"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
        <td><b style="color:#16a34a">Rp <?= number_format($row['harga'],0,',','.') ?></b></td>
        <td><?php if($row['stok']<=10){ echo '<span class="badge bg-danger rounded-pill">⚠️ Rendah: '.$row['stok'].'</span>'; } else { echo '<span class="badge bg-success rounded-pill">✅ '.$row['stok'].' Pcs</span>'; } ?></td>
        <td class="pe-4"><div class="d-flex gap-1"><a href="../detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border rounded-pill">👁️</a><a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">✏️ Edit</a><a href="produk.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">🗑️ Hapus</a></div></td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
</div></div>
</body>
</html>