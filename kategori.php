<?php
// Biar gak Fatal Error kalau nama kolom beda
mysqli_report(MYSQLI_REPORT_OFF);
/** @var mysqli $conn */
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Tambah kategori
if(isset($_POST['tambah'])){
  $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
  $desk = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
  // Coba dengan deskripsi, kalau gagal coba tanpa deskripsi
  if(!@mysqli_query($conn, "INSERT INTO categories (nama_kategori, deskripsi) VALUES ('$nama','$desk')")){
    @mysqli_query($conn, "INSERT INTO categories (nama_kategori) VALUES ('$nama')");
  }
  header("Location: kategori.php?pesan=tambah");
  exit;
}

// Hapus kategori
if(isset($_GET['hapus'])){
  $id = (int)$_GET['hapus'];
  @mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
  header("Location: kategori.php?pesan=hapus");
  exit;
}

// Ambil semua kategori
$q = @mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");
if(!$q){
  $q = @mysqli_query($conn, "SELECT * FROM kategori ORDER BY id ASC");
}

/** @param mysqli $conn @param int $kategori_id @return int */
function jumlah_produk(mysqli $conn, int $kategori_id): int{
  $r = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE kategori_id=$kategori_id"));
  if($r) return (int)$r['c'];
  $r2 = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as c FROM produk WHERE kategori_id=$kategori_id"));
  return $r2 ? (int)$r2['c'] : 0;
}

/** @param array $row @return string */
function nama_kategori(array $row): string{
  foreach(['nama_kategori','nama','kategori','name'] as $k){
    if(!empty($row[$k])) return (string)$row[$k];
  }
  return 'Kategori #' . ($row['id'] ?? '');
}

/** @param array $row @return string */
function desk_kategori(array $row): string{
  foreach(['deskripsi','keterangan','desc'] as $k){
    if(!empty($row[$k])) return (string)$row[$k];
  }
  return '-';
}

/** @param array $row @return string */
function icon_kategori(array $row): string{
  $nama = strtolower(nama_kategori($row));
  if(str_contains($nama, 'makan') || str_contains($nama, 'pendap') || str_contains($nama, 'lemang')) return '🍲';
  if(str_contains($nama, 'kerajinan') || str_contains($nama, 'besurek') || str_contains($nama, 'kain')) return '🧵';
  if(str_contains($nama, 'pakaian') || str_contains($nama, 'baju') || str_contains($nama, 'adat')) return '👘';
  if(str_contains($nama, 'minum')) return '🥤';
  return '🏷️';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kategori - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}
.sidebar{background:linear-gradient(180deg,#0d7a3e,#16a34a);color:white;min-height:100vh;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px 14px;border-radius:12px;margin-bottom:6px}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,.2)}
.form-card{background:white;border-radius:22px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,.05)}
.form-control{border-radius:14px;padding:11px 14px;border:1.5px solid #e5e7eb}
.form-control:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15)}
.btn-simpan{background:linear-gradient(135deg,#16a34a,#f59e0b);color:white;border:none;border-radius:999px;padding:12px;font-weight:700;width:100%}
.kat-card{background:white;border-radius:20px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);display:flex;gap:14px;align-items:center}
.kat-icon{width:58px;height:58px;border-radius:16px;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0}
</style>
</head>
<body>
<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar d-none d-md-block">
  <h4 class="fw-bold">🌿 Bengkulu<span style="color:#fde68a">Mart</span></h4>
  <small class="opacity-75">Admin Panel</small>
  <hr style="border-color:rgba(255,255,255,.3)">
  <a href="../index.php">🏠 Lihat Toko</a>
  <a href="produk.php">📦 Data Produk</a>
  <a href="tambah.php">➕ Tambah Produk</a>
  <a href="pesanan.php">🧾 Pesanan</a>
  <a href="kategori.php" class="active">🏷️ Kategori</a>
</div>

<div class="col-md-10 p-4">
  <h3 style="font-weight:800">Kategori Produk</h3>
  <p class="text-muted">Atur kategori Pendap, Lemang, Besurek & Baju Adat</p>

  <?php if(isset($_GET['pesan']) && $_GET['pesan']=='tambah'){ ?><div class="alert alert-success rounded-4">✅ Kategori berhasil ditambah!</div><?php } ?>
  <?php if(isset($_GET['pesan']) && $_GET['pesan']=='hapus'){ ?><div class="alert alert-success rounded-4">✅ Kategori berhasil dihapus!</div><?php } ?>

  <div class="row">
    <div class="col-md-4">
      <div class="form-card">
        <h5 class="fw-bold mb-3">➕ Tambah Kategori</h5>
        <form method="POST">
          <label class="fw-bold small mb-1">Nama Kategori</label>
          <input type="text" name="nama_kategori" class="form-control mb-3" placeholder="Contoh: Makanan Tradisional" required>
          <label class="fw-bold small mb-1">Deskripsi (Opsional)</label>
          <input type="text" name="deskripsi" class="form-control mb-3" placeholder="Contoh: Pendap, Lemang, dll">
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <button type="button" onclick="isiKategori('Makanan Tradisional','Pendap, Lemang, dll')" class="btn btn-sm btn-outline-success rounded-pill">🍲 Makanan</button>
            <button type="button" onclick="isiKategori('Kerajinan','Besurek, Kain, dll')" class="btn btn-sm btn-outline-success rounded-pill">🧵 Kerajinan</button>
            <button type="button" onclick="isiKategori('Pakaian','Baju Adat Bengkulu')" class="btn btn-sm btn-outline-success rounded-pill">👘 Pakaian</button>
          </div>
          <button type="submit" name="tambah" class="btn btn-simpan">✅ Simpan Kategori</button>
        </form>
      </div>
      <div class="form-card mt-3" style="background:linear-gradient(135deg,#0d7a3e,#16a34a);color:white">
        <h6 class="fw-bold">💡 Tips Kategori</h6>
        <small>Jangan bikin terlalu banyak kategori.<br>3 aja cukup: Makanan, Kerajinan, Pakaian.<br>Biar pembeli gak bingung.</small>
      </div>
    </div>

    <div class="col-md-8">
      <?php if(!$q || mysqli_num_rows($q)==0){ ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
          <div style="font-size:60px">🏷️</div>
          <h5 class="fw-bold mt-2">Belum Ada Kategori</h5>
          <p class="text-muted">Tambah kategori dulu di kiri, misal Makanan Tradisional.</p>
        </div>
      <?php } else { ?>
        <div class="row">
        <?php while($row=mysqli_fetch_assoc($q)){ ?>
          <div class="col-md-6 mb-3">
            <div class="kat-card">
              <div class="kat-icon"><?= icon_kategori($row) ?></div>
              <div class="flex-grow-1">
                <b><?= htmlspecialchars(nama_kategori($row)) ?></b><br>
                <small class="text-muted"><?= htmlspecialchars(desk_kategori($row)) ?></small><br>
                <span class="badge bg-success rounded-pill mt-1"><?= jumlah_produk($conn, (int)$row['id']) ?> Produk</span>
              </div>
              <a href="kategori.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus kategori <?= htmlspecialchars(nama_kategori($row)) ?>?')" class="btn btn-sm btn-danger rounded-pill">🗑️</a>
            </div>
          </div>
        <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
</div></div>

<script>
function isiKategori(nama, desk){
  document.querySelector('[name=nama_kategori]').value = nama;
  document.querySelector('[name=deskripsi]').value = desk;
}
</script>
</body>
</html>