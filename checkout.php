<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// AMBIL DATA PRODUK DI KERANJANG
$items = [];
$total = 0;
$ongkir = 15000;
$ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
$q = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
while ($r = mysqli_fetch_assoc($q)) {
    $r['qty'] = $_SESSION['cart'][$r['id']];
    $r['subtotal'] = $r['harga'] * $r['qty'];
    $total += $r['subtotal'];
    $items[] = $r;
}
$grand_total = $total + $ongkir;

// PROSES CHECKOUT - PAS KLIK BUAT PESANAN
$sukses = false;
$kode_pesanan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
    $hp = mysqli_real_escape_string($conn, $_POST['hp'] ?? '');
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
    $pembayaran = mysqli_real_escape_string($conn, $_POST['pembayaran'] ?? 'COD');
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    $kode_pesanan = "BM-" . date("YmdHis") . rand(10,99);

    // Bikin tabel orders kalau belum ada, biar gak error
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50),
        nama VARCHAR(100),
        hp VARCHAR(30),
        alamat TEXT,
        pembayaran VARCHAR(30),
        catatan TEXT,
        total INT,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(50),
        produk VARCHAR(100),
        harga INT,
        qty INT,
        subtotal INT
    )");

    mysqli_query($conn, "INSERT INTO orders (kode,nama,hp,alamat,pembayaran,catatan,total) VALUES ('$kode_pesanan','$nama','$hp','$alamat','$pembayaran','$catatan','$grand_total')");
    foreach ($items as $it) {
        $pn = mysqli_real_escape_string($conn, $it['nama_produk']);
        mysqli_query($conn, "INSERT INTO order_items (kode,produk,harga,qty,subtotal) VALUES ('$kode_pesanan','$pn','{$it['harga']}','{$it['qty']}','{$it['subtotal']}')");
        // Kurangi stok
        mysqli_query($conn, "UPDATE products SET stok = stok - {$it['qty']} WHERE id = {$it['id']}");
    }
    $_SESSION['cart'] = [];
    $_SESSION['last_order'] = $kode_pesanan;
    $sukses = true;
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
<title>Checkout - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}.header{background:white;padding:16px 0}.logo{font-weight:800;font-size:22px;text-decoration:none;color:#0d7a3e}.logo span{color:#f59e0b}.card{background:white;border-radius:20px;padding:24px}.foto{width:60px;height:60px;object-fit:cover;border-radius:10px}.btn-bayar{background:#15803d;color:white;border:none;border-radius:12px;padding:14px;font-weight:700;width:100%}.form-control,.form-select{border-radius:10px;padding:12px}</style>
</head><body>
<div class="header"><div class="container d-flex justify-content-between"><a href="index.php" class="logo">🌿 Bengkulu<span>Mart</span></a><a href="cart.php">← Kembali ke Keranjang</a></div></div>
<div class="container py-4">

<?php if($sukses): ?>
<!-- HALAMAN SUKSES -->
<div class="card text-center py-5">
<div style="font-size:60px">✅</div>
<h3 class="fw-bold mt-2">Pesanan Berhasil Dibuat!</h3>
<p class="text-muted">Terima kasih, pesanan kamu sudah kami terima.</p>
<div class="alert alert-success d-inline-block">Kode Pesanan: <b><?= $kode_pesanan ?></b><br>Total yang dibayar: <b>Rp <?= number_format($grand_total,0,',','.') ?></b></div>
<p class="small text-muted">Simpan kode pesanan ini untuk cek status pesanan.<br>Kami akan hubungi kamu via WhatsApp untuk konfirmasi.</p>
<a href="index.php" class="btn btn-success px-4 py-2">Belanja Lagi</a>
<a href="https://wa.me/6281234567890?text=Halo%20saya%20sudah%20pesan%20<?= $kode_pesanan ?>" target="_blank" class="btn btn-outline-success px-4 py-2">Konfirmasi via WhatsApp</a>
</div>

<?php else: ?>
<!-- FORM CHECKOUT -->
<h3 class="fw-bold mb-3">Checkout</h3>
<div class="row g-3">
<div class="col-md-7"><div class="card">
<h5 class="fw-bold">Data Penerima</h5>
<form method="POST" action="checkout.php">
<div class="mb-3"><label>Nama Lengkap *</label><input type="text" name="nama" class="form-control" placeholder="Contoh: Ujang" required></div>
<div class="mb-3"><label>No. WhatsApp / HP *</label><input type="text" name="hp" class="form-control" placeholder="Contoh: 0812-3456-7890" required></div>
<div class="mb-3"><label>Alamat Lengkap *</label><textarea name="alamat" class="form-control" rows="3" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota Bengkulu" required></textarea></div>
<div class="mb-3"><label>Metode Pembayaran *</label>
<select name="pembayaran" class="form-select" required>
<option value="COD">COD - Bayar di Tempat</option>
<option value="Transfer Bank">Transfer Bank - BCA / BRI</option>
<option value="QRIS">QRIS</option>
<option value="DANA / OVO">DANA / OVO</option>
</select></div>
<div class="mb-3"><label>Catatan (Opsional)</label><input type="text" name="catatan" class="form-control" placeholder="Contoh: Tolong dibungkus rapi, pedasnya dipisah"></div>
<button type="submit" name="checkout" class="btn-bayar">Buat Pesanan - Rp <?= number_format($grand_total,0,',','.') ?></button>
</form>
</div></div>

<div class="col-md-5"><div class="card">
<h5 class="fw-bold">Ringkasan Pesanan</h5>
<?php foreach($items as $it): ?>
<div class="d-flex gap-3 align-items-center border-bottom py-2">
<img class="foto" src="<?= foto_produk($it['nama_produk']) ?>">
<div class="flex-grow-1"><b><?= $it['nama_produk'] ?></b><br><small class="text-muted"><?= $it['qty'] ?> x Rp <?= number_format($it['harga'],0,',','.') ?></small></div>
<b>Rp <?= number_format($it['subtotal'],0,',','.') ?></b>
</div>
<?php endforeach; ?>
<div class="d-flex justify-content-between mt-3"><span>Subtotal</span><span>Rp <?= number_format($total,0,',','.') ?></span></div>
<div class="d-flex justify-content-between"><span>Ongkir (Bengkulu)</span><span>Rp <?= number_format($ongkir,0,',','.') ?></span></div>
<hr>
<div class="d-flex justify-content-between"><h5>Total</h5><h5 class="text-success fw-bold">Rp <?= number_format($grand_total,0,',','.') ?></h5></div>
</div></div>
</div>
<?php endif; ?>

</div>
</body></html>