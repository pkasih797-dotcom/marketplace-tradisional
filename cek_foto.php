<?php
/** @var mysqli $conn */
include 'config/koneksi.php';
echo "<h2>🔍 CEK FOTO PENDAP</h2><hr>";

$id = (int)($_GET['id'] ?? 1);
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id='$id'"));
if(!$p){ echo "Produk id=$id gak ada"; exit; }

echo "<b>Nama:</b> " . $p['nama_produk'] . "<br>";
echo "<b>Isi kolom gambar di database:</b> <b style='color:red'>" . ($p['gambar'] ?: '(KOSONG)') . "</b><br><br>";

$nama_file = basename($p['gambar'] ?? '');
echo "<b>Nama file yang dicari:</b> $nama_file<br><br>";

// Cek semua kemungkinan lokasi
$lokasi = [
  'uploads/' . $nama_file => __DIR__ . '/uploads/' . $nama_file,
  'admin/uploads/' . $nama_file => __DIR__ . '/admin/uploads/' . $nama_file,
  'images/' . $nama_file => __DIR__ . '/images/' . $nama_file,
  'img/' . $nama_file => __DIR__ . '/img/' . $nama_file,
  $nama_file => __DIR__ . '/' . $nama_file,
];

echo "<h3>1. File-nya ada di mana?</h3>";
$ketemu = false;
foreach($lokasi as $label => $path){
  $ada = file_exists($path) ? "✅ ADA" : "❌ GAK ADA";
  if(file_exists($path)) $ketemu = true;
  echo "$ada - $label<br>";
}
echo "<br>";

echo "<h3>2. Folder uploads/ gimana?</h3>";
echo "Folder uploads/ ada? " . (is_dir(__DIR__ . '/uploads') ? "✅ ADA" : "❌ GAK ADA - INI MASALAHNYA") . "<br>";
echo "Folder uploads/ bisa ditulis? " . (is_writable(__DIR__ . '/uploads') ? "✅ BISA" : "❌ GAK BISA DITULIS") . "<br><br>";

echo "<h3>3. Isi folder uploads/ sekarang:</h3>";
if(is_dir(__DIR__ . '/uploads')){
  $files = scandir(__DIR__ . '/uploads');
  $ada_file = false;
  foreach($files as $f){
    if($f == '.' || $f == '..') continue;
    echo "- $f<br>";
    $ada_file = true;
  }
  if(!$ada_file) echo "<b style='color:red'>KOSONG! Berarti upload-nya gagal total, file gak pernah masuk.</b>";
} else {
  echo "<b style='color:red'>Folder uploads/ belum ada! Bikin dulu.</b>";
}
echo "<br><hr>";

echo "<h3>4. Kalau file ketemu, harusnya gambar ini muncul:</h3>";
if($ketemu){
  foreach($lokasi as $label => $path){
    if(file_exists($path)){
      echo "<img src='$label' style='max-width:300px;border:2px solid green;border-radius:12px'><br><br>Path yang bener: <b>$label</b>";
      break;
    }
  }
} else {
  echo "<b style='color:red'>File $nama_file gak ada di mana-mana. Makanya di detail.php gak muncul.</b><br><br>";
  echo "Solusi: Buka admin/edit.php, upload ulang foto-nya, terus refresh halaman ini lagi.";
}
?>
<br><br><a href="detail.php?id=<?= $id ?>">Lihat Detail</a> | <a href="admin/edit.php?id=<?= $id ?>">Edit Produk</a>
