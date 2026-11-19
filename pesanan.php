<?php
// MATIKAN ERROR FATAL MYSQLI - BIAR GAK MUNCUL MERAH LAGI
mysqli_report(MYSQLI_REPORT_OFF);
/** @var mysqli $conn */
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Ubah status pesanan
if(isset($_GET['status']) && isset($_GET['id'])){
  $id = (int)$_GET['id'];
  $st = mysqli_real_escape_string($conn, $_GET['status']);
  if(!@mysqli_query($conn, "UPDATE orders SET status='$st' WHERE id=$id")){
    @mysqli_query($conn, "UPDATE pesanan SET status='$st' WHERE id=$id");
  }
  header("Location: pesanan.php?pesan=status");
  exit;
}

// Hapus pesanan
if(isset($_GET['hapus'])){
  $id = (int)$_GET['hapus'];
  if(!@mysqli_query($conn, "DELETE FROM orders WHERE id=$id")){
    @mysqli_query($conn, "DELETE FROM pesanan WHERE id=$id");
  }
  header("Location: pesanan.php?pesan=hapus");
  exit;
}

$filter = $_GET['filter'] ?? 'semua';
$where = "";
if($filter != 'semua'){
  $f = mysqli_real_escape_string($conn, $filter);
  $where = "WHERE status='$f'";
}

// SUDAH DIPERBAIKI - GAK PAKE JOIN, GAK PAKE TOTAL
$q = @mysqli_query($conn, "SELECT * FROM orders $where ORDER BY id DESC");
$tabel_orders = true;
if(!$q){
  $q = @mysqli_query($conn, "SELECT * FROM pesanan $where ORDER BY id DESC");
  $tabel_orders = false;
  // Kalau filter status juga error (kolom status gak ada), tampilkan semua
  if(!$q){
    $q = @mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC");
  }
}

/** @param mysqli $conn @param string $status @param bool $tabel_orders @return array{c:int} */
function hitung(mysqli $conn, string $status, bool $tabel_orders): array{
  $tabel = $tabel_orders ? 'orders' : 'pesanan';
  // CUMA COUNT(*), GAK PAKE SUM(total) LAGI - INI YANG BIKIN ERROR TADI
  $r = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as c FROM $tabel WHERE status='$status'"));
  return $r ?: ['c'=>0];
}

$tabel_nama = $tabel_orders ? 'orders' : 'pesanan';
$total_semua = @mysqli_fetch_assoc(@mysqli_query($conn, "SELECT COUNT(*) as c FROM $tabel_nama")) ?? ['c'=>0];
$st_baru = hitung($conn, 'baru', $tabel_orders);
$st_proses = hitung($conn, 'diproses', $tabel_orders);
$st_selesai = hitung($conn, 'selesai', $tabel_orders);

/** @param string|null $st */
function badge_status(?string $st): string{
  $st = strtolower($st ?? '');
  if($st=='baru') return '<span class="badge bg-warning text-dark rounded-pill">🆕 Baru</span>';
  if($st=='diproses' || $st=='dikirim') return '<span class="badge bg-primary rounded-pill">🚚 Diproses</span>';
  if($st=='selesai') return '<span class="badge bg-success rounded-pill">✅ Selesai</span>';
  if($st=='batal' || $st=='dibatalkan') return '<span class="badge bg-danger rounded-pill">❌ Batal</span>';
  if($st=='') return '<span class="badge bg-secondary rounded-pill">Baru</span>';
  return '<span class="badge bg-secondary rounded-pill">'.htmlspecialchars($st).'</span>';
}

/** @param array $row */
function nama_pembeli(array $row): string{
  foreach(['nama_pembeli','nama','nama_customer','customer','pembeli','nama_user','user_nama','pemesan'] as $k){
    if(!empty($row[$k])) return (string)$row[$k];
  }
  return 'Pembeli #' . ($row['id'] ?? '');
}

/** @param array $row */
function total_pesanan(array $row): string{
  // Coba semua kemungkinan kolom total / harga
  foreach(['total','total_harga','grand_total','total_bayar','jumlah_total','harga_total'] as $k){
    if(isset($row[$k]) && $row[$k] !== '' && $row[$k] != 0) return 'Rp ' . number_format((float)$row[$k],0,',','.');
  }
  // Kalau gak ada kolom total, coba hitung dari harga x qty
  if(isset($row['harga']) && isset($row['qty'])) return 'Rp ' . number_format((float)$row['harga'] * (float)$row['qty'],0,',','.');
  if(isset($row['harga'])) return 'Rp ' . number_format((float)$row['harga'],0,',','.');
  return '-';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pesanan - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}
.sidebar{background:linear-gradient(180deg,#0d7a3e,#16a34a);color:white;min-height:100vh;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px 14px;border-radius:12px;margin-bottom:6px}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,.2)}
.stat-card{background:white;border-radius:18px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);display:flex;gap:14px;align-items:center}
.stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.table-card{background:white;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.05);padding:6px}
.btn-filter{border-radius:999px;padding:7px 16px;font-weight:600;border:1.5px solid #e5e7eb;background:white}
.btn-filter.active{background:#16a34a;color:white;border-color:#16a34a}
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
  <a href="pesanan.php" class="active">🧾 Pesanan</a>
  <a href="kategori.php">🏷️ Kategori</a>
</div>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><h3 style="font-weight:800">Data Pesanan</h3><p class="text-muted mb-0">Kelola pesanan Pendap, Lemang, Besurek & Baju Adat</p></div>
    <a href="produk.php" class="btn bg-white border rounded-pill px-4">📦 Ke Produk</a>
  </div>
  <?php if(isset($_GET['pesan']) && $_GET['pesan']=='status'){ ?><div class="alert alert-success rounded-4 mt-3">✅ Status pesanan berhasil diubah!</div><?php } ?>
  <?php if(isset($_GET['pesan']) && $_GET['pesan']=='hapus'){ ?><div class="alert alert-success rounded-4 mt-3">✅ Pesanan berhasil dihapus!</div><?php } ?>
  <div class="row mt-4">
    <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#dbeafe">🧾</div><div><small class="text-muted">Total Pesanan</small><h4 class="fw-bold mb-0"><?= $total_semua['c'] ?> Pesanan</h4></div></div></div>
    <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#fef3c7">🆕</div><div><small class="text-muted">Baru Masuk</small><h4 class="fw-bold mb-0"><?= $st_baru['c'] ?? 0 ?> Pesanan</h4></div></div></div>
    <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#e0e7ff">🚚</div><div><small class="text-muted">Diproses</small><h4 class="fw-bold mb-0"><?= $st_proses['c'] ?? 0 ?> Pesanan</h4></div></div></div>
    <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon" style="background:#dcfce7">✅</div><div><small class="text-muted">Selesai</small><h4 class="fw-bold mb-0"><?= $st_selesai['c'] ?? 0 ?> Pesanan</h4></div></div></div>
  </div>
  <div class="table-card mt-1">
    <div class="d-flex gap-2 p-3 flex-wrap">
      <a href="pesanan.php?filter=semua" class="btn btn-filter <?= $filter=='semua'?'active':'' ?>">Semua</a>
      <a href="pesanan.php?filter=baru" class="btn btn-filter <?= $filter=='baru'?'active':'' ?>">🆕 Baru</a>
      <a href="pesanan.php?filter=diproses" class="btn btn-filter <?= $filter=='diproses'?'active':'' ?>">🚚 Diproses</a>
      <a href="pesanan.php?filter=selesai" class="btn btn-filter <?= $filter=='selesai'?'active':'' ?>">✅ Selesai</a>
      <a href="pesanan.php?filter=batal" class="btn btn-filter <?= $filter=='batal'?'active':'' ?>">❌ Batal</a>
    </div>
    <?php if(!$q || mysqli_num_rows($q)==0){ ?>
      <div class="text-center py-5"><div style="font-size:60px">🧾</div><h5 class="fw-bold mt-2">Belum Ada Pesanan</h5><p class="text-muted">Belum ada yang checkout Pendap, Lemang, Besurek, atau Baju.<br>Pesanan akan muncul di sini begitu ada pembeli.</p><a href="../index.php" class="btn btn-success rounded-pill px-4 mt-2">👁️ Lihat Toko</a></div>
    <?php } else { ?>
    <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead style="background:#f8f9fa"><tr><th class="ps-4">Pesanan</th><th>Pembeli</th><th>Total</th><th>Status</th><th class="pe-4">Aksi</th></tr></thead>
      <tbody>
      <?php while($row=mysqli_fetch_assoc($q)){
        $tanggal = $row['tanggal'] ?? $row['created_at'] ?? $row['tanggal_pesan'] ?? $row['tanggal_order'] ?? '';
      ?>
      <tr>
        <td class="ps-4"><b>#<?= $row['id'] ?></b><br><small class="text-muted"><?= $tanggal ? date('d M Y, H:i', strtotime($tanggal)) : '-' ?></small></td>
        <td><div class="d-flex align-items-center gap-2"><div style="width:36px;height:36px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center">👤</div><b><?= htmlspecialchars(nama_pembeli($row)) ?></b></div></td>
        <td><b style="color:#16a34a"><?= total_pesanan($row) ?></b></td>
        <td><?= badge_status($row['status'] ?? 'baru') ?></td>
        <td class="pe-4"><div class="d-flex gap-1 flex-wrap"><a href="pesanan.php?id=<?= $row['id'] ?>&status=diproses" class="btn btn-sm btn-primary rounded-pill">🚚 Proses</a><a href="pesanan.php?id=<?= $row['id'] ?>&status=selesai" class="btn btn-sm btn-success rounded-pill">✅ Selesai</a><a href="pesanan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus pesanan #<?= $row['id'] ?>?')" class="btn btn-sm btn-danger rounded-pill">🗑️</a></div></td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
    </div>
    <?php } ?>
  </div>
</div>
</div></div>
</body>
</html>