<?php
/** @var mysqli $conn */
session_start();
require_once __DIR__ . '/../config/koneksi.php';

$pesan = '';
if(isset($_POST['simpan'])){
  $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
  $kategori_id = (int)$_POST['kategori_id'];
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $harga = (int)$_POST['harga'];
  $stok = (int)$_POST['stok'];
  $gambar = '';

  // Upload Foto
  if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $boleh = ['jpg','jpeg','png','webp'];
    if(in_array($ext, $boleh)){
      // Nama file dibikin rapi: pendap-123456.jpg (biar gak ketimpa & gak ada spasi)
      $gambar = strtolower(str_replace(' ', '-', $nama)) . '-' . time() . '.' . $ext;
      $target = __DIR__ . '/../uploads/' . $gambar;
      if(!is_dir(__DIR__ . '/../uploads/')) mkdir(__DIR__ . '/../uploads/', 0777, true);
      move_uploaded_file($_FILES['foto']['tmp_name'], $target);
    } else {
      $pesan = '<div class="alert alert-danger rounded-4">❌ Format foto harus JPG / PNG / WEBP!</div>';
    }
  }

  if($pesan == ''){
    // penjual_id = 1 (Admin) biar gak error kalau kolomnya wajib isi
    $q = mysqli_query($conn, "INSERT INTO products (nama_produk, kategori_id, deskripsi, harga, stok, gambar, penjual_id) VALUES ('$nama','$kategori_id','$deskripsi','$harga','$stok','$gambar','1')");
    if($q){
      header("Location: produk.php?pesan=tambah");
      exit;
    } else {
      $pesan = '<div class="alert alert-danger rounded-4">❌ Gagal simpan: '.mysqli_error($conn).'</div>';
    }
  }
}

$kategori = mysqli_query($conn, "SELECT * FROM categories ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Produk - BengkuluMart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body{background:#f6f8f7;font-family:'Segoe UI',sans-serif}
.sidebar{background:linear-gradient(180deg,#0d7a3e,#16a34a);color:white;min-height:100vh;padding:24px 18px;position:sticky;top:0}
.sidebar a{color:white;text-decoration:none;display:block;padding:10px 14px;border-radius:12px;margin-bottom:6px}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,.2)}
.form-card{background:white;border-radius:22px;padding:28px;box-shadow:0 4px 20px rgba(0,0,0,.05)}
.form-control, .form-select{border-radius:14px;padding:11px 14px;border:1.5px solid #e5e7eb}
.form-control:focus, .form-select:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15)}
label{font-weight:700;font-size:13px;margin-bottom:6px}
.btn-simpan{background:linear-gradient(135deg,#16a34a,#f59e0b);color:white;border:none;border-radius:999px;padding:13px;font-weight:700;width:100%}
.btn-simpan:hover{color:white;opacity:.9}
.upload-box{border:2px dashed #cbd5e1;border-radius:16px;padding:18px;text-align:center;cursor:pointer;background:#f8fafc;transition:.2s}
.upload-box:hover{border-color:#16a34a;background:#f0fdf4}
#preview{width:100%;height:180px;object-fit:cover;border-radius:14px;display:none}
.chip{cursor:pointer}
.chip input{display:none}
.chip span{display:inline-block;padding:6px 14px;border-radius:999px;border:1.5px solid #e5e7eb;background:white;font-size:13px;font-weight:600}
.chip input:checked + span{background:#16a34a;color:white;border-color:#16a34a}
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
  <a href="tambah.php" class="active">➕ Tambah Produk</a>
  <a href="pesanan.php">🧾 Pesanan</a>
  <a href="kategori.php">🏷️ Kategori</a>
</div>

<div class="col-md-10 p-4">
  <a href="produk.php" class="btn bg-white border rounded-pill px-4 mb-3">← Kembali</a>
  <h3 style="font-weight:800">Tambah Produk + Foto</h3>
  <p class="text-muted">Tambah Pendap, Lemang, Besurek atau Baju Adat baru ke BengkuluMart</p>

  <?= $pesan ?>

  <div class="row">
    <div class="col-md-7">
      <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
          <label>Nama Produk</label>
          <input type="text" name="nama_produk" class="form-control mb-3" placeholder="Contoh: Pendap Khas Bengkulu" required>

          <label>Kategori</label>
          <select name="kategori_id" class="form-select mb-3" required>
            <option value="">-- Pilih Kategori --</option>
            <?php while($k=mysqli_fetch_assoc($kategori)){ ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php } ?>
          </select>
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <span class="text-muted small w-100">Pintas kategori:</span>
            <label class="chip"><input type="radio" name="kategori_id" value="1"><span>🍲 Makanan</span></label>
            <label class="chip"><input type="radio" name="kategori_id" value="2"><span>🧵 Kerajinan</span></label>
            <label class="chip"><input type="radio" name="kategori_id" value="3"><span>👘 Pakaian</span></label>
          </div>

          <label>Deskripsi</label>
          <textarea name="deskripsi" class="form-control mb-3" rows="3" placeholder="Ceritakan produknya... Misal: Pendap ikan dibungkus daun talas, pedas gurih khas Bengkulu" required></textarea>

          <div class="row">
            <div class="col-6"><label>Harga (Rp)</label><input type="number" name="harga" class="form-control mb-3" placeholder="35000" required></div>
            <div class="col-6"><label>Stok</label><input type="number" name="stok" class="form-control mb-3" placeholder="25" required></div>
          </div>

          <label>Foto Produk (JPG / PNG)</label>
          <label class="upload-box w-100" for="fotoInput">
            <img id="preview" src="#" alt="Preview">
            <div id="uploadText">
              <div style="font-size:40px">📸</div>
              <b>Klik untuk pilih foto</b><br>
              <small class="text-muted">Foto Pendap, Lemang, Besurek, Baju - Max 2MB</small>
            </div>
            <input type="file" name="foto" id="fotoInput" accept="image/*" hidden required>
          </label>

          <button type="submit" name="simpan" class="btn btn-simpan mt-4">✅ Simpan Produk</button>
        </form>
      </div>
    </div>

    <div class="col-md-5">
      <div class="form-card" style="background:linear-gradient(135deg,#0d7a3e,#16a34a);color:white">
        <h5 class="fw-bold">👀 Preview di Toko</h5>
        <p class="opacity-75 small">Gini nanti tampilannya di halaman depan BengkuluMart</p>
        <div style="background:white;border-radius:16px;overflow:hidden;color:#111">
          <img id="preview2" src="https://via.placeholder.com/400x200?text=Foto+Produk" style="width:100%;height:180px;object-fit:cover">
          <div class="p-3">
            <span class="badge bg-success rounded-pill">Stok Tersedia</span>
            <h6 class="fw-bold mt-2 mb-1" id="prevNama">Nama Produk</h6>
            <small class="text-muted" id="prevDesk">Deskripsi produk akan muncul di sini...</small>
            <div class="d-flex justify-content-between mt-2"><b style="color:#16a34a" id="prevHarga">Rp 0</b><span>⭐ 4.9</span></div>
          </div>
        </div>
        <div class="mt-3 small">
          <b>💡 Tips biar laku:</b><br>
          • Foto terang, background bersih<br>
          • Nama jelas: "Pendap" bukan "Produk 1"<br>
          • Deskripsi cerita rasa / bahan
        </div>
      </div>
    </div>
  </div>
</div>
</div></div>

<script>
const fotoInput = document.getElementById('fotoInput');
fotoInput.addEventListener('change', function(){
  const file = this.files[0];
  if(file){
    const url = URL.createObjectURL(file);
    const p1 = document.getElementById('preview');
    p1.src = url; p1.style.display = 'block';
    document.getElementById('uploadText').style.display = 'none';
    document.getElementById('preview2').src = url;
  }
});
// Live preview nama, deskripsi, harga
document.querySelector('[name=nama_produk]').addEventListener('input', e => {
  document.getElementById('prevNama').innerText = e.target.value || 'Nama Produk';
});
document.querySelector('[name=deskripsi]').addEventListener('input', e => {
  document.getElementById('prevDesk').innerText = e.target.value || 'Deskripsi produk akan muncul di sini...';
});
document.querySelector('[name=harga]').addEventListener('input', e => {
  document.getElementById('prevHarga').innerText = 'Rp ' + (parseInt(e.target.value)||0).toLocaleString('id-ID');
});
// Chip kategori -> sinkron ke select
document.querySelectorAll('.chip input').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelector('select[name=kategori_id]').value = r.value;
  });
});
document.querySelector('select[name=kategori_id]').addEventListener('change', e => {
  document.querySelectorAll('.chip input').forEach(r => r.checked = (r.value == e.target.value));
});
</script>
</body>
</html>