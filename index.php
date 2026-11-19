<?php
session_start();
include 'config/koneksi.php';
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$kat = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$where = "WHERE 1=1";
if ($keyword != '') $where .= " AND p.nama_produk LIKE '%$keyword%'";
if ($kat != 0) $where .= " AND p.kategori_id = '$kat'";
$q = mysqli_query($conn, "SELECT p.*, c.nama_kategori, u.nama as nama_penjual FROM products p JOIN categories c ON p.kategori_id=c.id JOIN users u ON p.penjual_id=u.id $where ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BengkuluMart - Marketplace Tradisional Bengkulu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body{background:#f8f9fa;font-family:'Segoe UI',sans-serif}
.navbar-brand{font-weight:800;font-size:22px;color:#16a34a!important}
.navbar-brand span{color:#f59e0b}
.hero{background:linear-gradient(135deg,#0d7a3e 0%,#16a34a 55%,#f59e0b 100%);color:white;border-radius:20px;padding:40px 30px}
.hero h1{font-weight:800;font-size:38px;line-height:1.2}
.card-produk{border:none;border-radius:20px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,.06);transition:.3s;background:white;height:100%}
.card-produk:hover{transform:translateY(-6px);box-shadow:0 15px 35px rgba(0,0,0,.12)}
.img-box{height:200px;overflow:hidden;background:#e8f5e9}
.img-box img{width:100%;height:100%;object-fit:cover;display:block}
.badge-stok{background:#dcfce7;color:#15803d;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700}
.harga{font-weight:800;font-size:19px;color:#16a34a}
.btn-lihat{background:#16a34a;color:white;border-radius:999px;font-weight:600;width:100%;padding:10px;text-decoration:none;display:block;text-align:center}
.btn-lihat:hover{background:#15803d;color:white}
.fitur{background:white;border-radius:16px;padding:18px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.05)}
</style>
</head>
<body>

<nav class="navbar bg-white shadow-sm sticky-top"><div class="container">
<a class="navbar-brand" href="index.php">🌿 Bengkulu<span>Mart</span></a>
<form class="d-flex flex-grow-1 mx-3" method="GET">
<input class="form-control rounded-pill" name="cari" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari Pendap, Lemang, Besurek...">
<button class="btn btn-success rounded-pill ms-2 px-4" type="submit"><i class="bi bi-search"></i></button>
</form>
<a href="cart.php" class="btn btn-outline-success rounded-pill">🛒 Keranjang</a>
<a href="admin/produk.php" class="btn btn-dark rounded-pill ms-2">Admin</a>
</div></nav>

<div class="container mt-4">
<div class="hero">
<span class="badge bg-white text-success mb-2">✨ Asli Bengkulu • Langsung dari Pengrajin</span>
<h1>Oleh-Oleh & Kuliner<br>Khas Bengkulu.</h1>
<p class="opacity-75" style="max-width:500px">Pendap, Lemang Tapai, Kain Besurek, Baju Adat — dikirim fresh dari Manna & Bengkulu.</p>
<div class="d-flex gap-2 mt-3">
<a href="#produk" class="btn btn-light text-success fw-bold rounded-pill px-4">Belanja Sekarang →</a>
<a href="admin/produk.php" class="btn btn-outline-light rounded-pill px-4">Jadi Penjual</a>
</div>
<div class="mt-4" style="font-size:90px">🍲🧵👘</div>
</div>

<div class="row mt-4">
<div class="col-md-4 mb-3"><div class="fitur">🚚 <b>Gratis Ongkir</b><br><small class="text-muted">Untuk wilayah Manna & Bengkulu</small></div></div>
<div class="col-md-4 mb-3"><div class="fitur">💯 <b>100% Asli</b><br><small class="text-muted">Langsung dari pengrajin lokal</small></div></div>
<div class="col-md-4 mb-3"><div class="fitur">💳 <b>COD / QRIS</b><br><small class="text-muted">Bayar di tempat atau transfer</small></div></div>
</div>

<div class="d-flex gap-2 mt-2 flex-wrap">
<a href="index.php" class="btn btn-outline-secondary rounded-pill <?= $kat==0?'active':'' ?>">Semua</a>
<?php $kc=mysqli_query($conn,"SELECT * FROM categories"); while($c=mysqli_fetch_assoc($kc)){ $act=($kat==$c['id'])?'active':''; ?>
<a href="index.php?kategori=<?= $c['id'] ?>" class="btn btn-outline-secondary rounded-pill <?= $act ?>"><?= htmlspecialchars($c['nama_kategori']) ?></a>
<?php } ?>
</div>

<h4 class="fw-bold mt-4" id="produk">Daftar Produk</h4>

<div class="row mt-3">

  <!-- 1. PENDAP - CODINGAN GAMBAR ADA DI SINI -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/pendap.jpeg" alt="Pendap" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 25</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Pendap</h5>
        <p class="text-muted" style="font-size:13px">Pendap ikan khas Bengkulu, dibungkus daun talas</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 35.000</span>
          <span>⭐ 4.9</span>
        </div>
        <a href="detail.php?id=1" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

  <!-- 2. LEMANG - CODINGAN GAMBAR ADA DI SINI, SAMA PERSIS KAYAK CONTOH KAMU -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/lemang.jpeg" alt="Lemang" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 30</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Lemang</h5>
        <p class="text-muted" style="font-size:13px">Lemang Tapai ketan bambu khas Bengkulu</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 20.000</span>
          <span>⭐ 4.9</span>
        </div>
        <a href="detail.php?id=2" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

  <!-- 3. BESUREK - CODINGAN GAMBAR ADA DI SINI -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/besurek.jpeg" alt="Kain Besurek" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 10</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Kain Besurek</h5>
        <p class="text-muted" style="font-size:13px">Kain Besurek motif Rafflesia khas Bengkulu</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 120.000</span>
          <span>⭐ 4.9</span>
        </div>
        <a href="detail.php?id=3" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

  <!-- 4. BAJU - CODINGAN GAMBAR ADA DI SINI -->
  <div class="col-md-4 mb-4">
    <div class="card-produk">
      <div class="img-box">
        <img src="uploads/baju.jpeg" alt="Baju Adat Bengkulu" style="width:100%;height:100%;object-fit:cover">
      </div>
      <div class="p-3">
        <span class="badge-stok">Stok Tersedia: 15</span>
        <h5 class="mt-2 mb-1" style="font-weight:700">Baju Adat Bengkulu</h5>
        <p class="text-muted" style="font-size:13px">Baju adat Bengkulu untuk acara resmi</p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="harga">Rp 150.000</span>
          <span>⭐ 4.8</span>
        </div>
        <a href="detail.php?id=4" class="btn btn-lihat mt-3">Lihat Detail →</a>
      </div>
    </div>
  </div>

</div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5"><b>🌿 BengkuluMart</b><br><small>Dibuat untuk UMKM Bengkulu • © 2026</small></footer>
</body>
</html>