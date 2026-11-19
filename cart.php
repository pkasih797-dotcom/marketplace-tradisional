<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// 1. TAMBAH KE KERANJANG - dari detail.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $qty = (int)($_POST['qty'] ?? 1);
    if ($qty < 1) $qty = 1;
    if ($id > 0) {
        if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] += $qty;
        else $_SESSION['cart'][$id] = $qty;
    }
    header('Location: cart.php');
    exit;
}

// 2. HAPUS 1 BARANG
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit;
}

// 3. KOSONGKAN SEMUA
if (isset($_GET['kosongkan'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit;
}

// 4. AMBIL DATA PRODUK YANG ADA DI KERANJANG
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $q = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
    while ($r = mysqli_fetch_assoc($q)) {
        $r['qty'] = $_SESSION['cart'][$r['id']];
        $r['subtotal'] = $r['harga'] * $r['qty'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
}

function foto_produk($nama) {
    $nama = strtolower(trim($nama));
    if (str_contains($nama, 'pendap')) $kunci = 'pendap';
    elseif (str_contains($nama, 'lemang') || str_contains($nama, 'tapai')) $kunci = 'lemang';
    elseif (str_contains($nama, 'baju') || str_contains($nama, 'pengantin') || str_contains($nama, 'adat')) $kunci = 'baju';
    elseif (str_contains($nama, 'batik') || str_contains($nama, 'besurek')) $kunci = 'batik';
    else $kunci = 'pendap';
    foreach (['uploads/', '', 'images/'] as $folder) {
        foreach ([$kunci, ucfirst($kunci)] as $n) {
            foreach (['jpg','jpeg','png','webp','JPG'] as $ext) {
                $f = $folder . $n . '.' . $ext;
                if (file_exists(__DIR__ . '/' . $f)) return $f;
            }
        }
    }
    return "https://via.placeholder.com/200x200/15803d/ffffff?text=" . urlencode($nama);
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keranjang - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}.header{background:white;padding:16px 0}.logo{font-weight:800;font-size:22px;text-decoration:none;color:#0d7a3e}.logo span{color:#f59e0b}.card{background:white;border-radius:20px;padding:20px}.foto{width:80px;height:80px;object-fit:cover;border-radius:12px}</style>
</head><body>
<div class="header"><div class="container d-flex justify-content-between"><a href="index.php" class="logo">🌿 Bengkulu<span>Mart</span></a><a href="index.php">← Belanja Lagi</a></div></div>
<div class="container py-4"><h3 class="fw-bold">🛒 Keranjang Belanja</h3>
<?php if(empty($items)): ?>
<div class="card text-center py-5"><h5>Keranjang masih kosong</h5><p class="text-muted">Belum ada barang yang ditambahkan</p><a href="index.php" class="btn btn-success">Cari Produk</a></div>
<?php else: ?>
<div class="card">
<?php foreach($items as $it): ?>
<div class="d-flex align-items-center gap-3 border-bottom py-3">
<img class="foto" src="<?= foto_produk($it['nama_produk']) ?>">
<div class="flex-grow-1"><h6 class="mb-0"><?= $it['nama_produk'] ?></h6><small class="text-muted">Rp <?= number_format($it['harga'],0,',','.') ?> x <?= $it['qty'] ?></small></div>
<b>Rp <?= number_format($it['subtotal'],0,',','.') ?></b>
<a href="cart.php?hapus=<?= $it['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</a>
</div>
<?php endforeach; ?>
<div class="d-flex justify-content-between mt-3"><h5>Total:</h5><h5 class="text-success fw-bold">Rp <?= number_format($total,0,',','.') ?></h5></div>
<a href="cart.php?kosongkan=1" class="btn btn-outline-secondary mt-2">Kosongkan Keranjang</a>
<a href="checkout.php" class="btn btn-success mt-2 w-100 py-2 fw-bold">Checkout Sekarang</a>
</div>
<?php endif; ?>
</div>
</body></html>