<?php
// ============================================================
// FILE: function_admin.php
// TUJUAN: Memproses semua formulir pengelolaan akun pengguna
//
// Ibarat "bagian administrasi kantor" — file ini mengurus semua
// urusan perubahan data, mulai dari ganti foto, edit data pribadi,
// ganti username/password, hingga menghapus akun sepenuhnya.
//
// BERISI LIMA AKSI:
//   [1] btn_editfotoakun     → Ganti foto profil
//   [2] btn_adminregister    → Tambah pengguna baru dari dalam dashboard
//   [3] btn_editdatapribadi  → Update data pribadi (nama, telp, email, dsb.)
//   [4] btn_editdataakun     → Update username dan/atau password
//   [5] btn_deleteakun       → Hapus akun permanen
//
// Semua aksi dikirim via method POST dari halaman profile.php
// ============================================================

include 'koneksi.php'; // Sambungkan ke database MySQL (variabel $koneksi tersedia)
session_start();       // Hidupkan sistem sesi agar bisa membaca data pengguna yang sedang login

$sesi_role = $_SESSION['sesi_role']; // Ambil role pengguna dari sesi (untuk keperluan validasi)
$sesi_id   = $_SESSION['sesi_id'];   // Ambil ID pengguna yang sedang login (contoh: USER001)

// Mulai buffer output — mencegah error "headers already sent" jika ada spasi tidak sengaja di file ini
ob_start();

// Keamanan: Jika ada yang mengakses file ini bukan lewat form POST, lempar kembali ke dashboard
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
	header('Location: ../dashboard');
	exit();
}

// ============================================================
// SIMPAN DATA FORM KE SESSION (SEMENTARA)
// Ini berguna sebagai cadangan jika proses gagal — data tidak hilang dari form
// Kolom yang disimpan = data pribadi pengguna dari tabel 'users'
// ============================================================
$_SESSION['form_data'] = [
	'nama_user'      => isset($_POST['nama_user'])     ? htmlspecialchars(trim($_POST['nama_user']))     : '', // Ketikan pengguna → kolom nama_user di DB
	'id_user'        => isset($_POST['id_user'])       ? htmlspecialchars(trim($_POST['id_user']))       : '', // Ketikan pengguna → kolom id_user di DB
	'alamat'         => isset($_POST['alamat'])        ? htmlspecialchars(trim($_POST['alamat']))        : '', // Ketikan pengguna → kolom alamat di DB
	'tempat_lahir'   => isset($_POST['tempat_lahir'])  ? htmlspecialchars(trim($_POST['tempat_lahir']))  : '', // Ketikan pengguna → kolom tempat_lahir di DB
	'tanggal_lahir'  => isset($_POST['tanggal_lahir']) ? htmlspecialchars(trim($_POST['tanggal_lahir'])) : '', // Ketikan pengguna → kolom tanggal_lahir di DB
	'jenis_kelamin'  => isset($_POST['jenis_kelamin']) ? htmlspecialchars(trim($_POST['jenis_kelamin'])) : '', // Ketikan pengguna → kolom jenis_kelamin di DB
	'username'       => isset($_POST['username'])      ? htmlspecialchars(trim($_POST['username']))      : '', // Ketikan pengguna → kolom username di DB
	'no_telp'        => isset($_POST['no_telp'])       ? htmlspecialchars(trim($_POST['no_telp']))       : '', // Ketikan pengguna → kolom no_telp di DB
	'email'          => isset($_POST['email'])         ? htmlspecialchars(trim($_POST['email']))         : '', // Ketikan pengguna → kolom email di DB
];

// ============================================================
// FUNGSI PEMBANTU: redirect_alert()
// Tujuan: Mengirim pengguna kembali ke halaman tertentu dengan
//         informasi status (sukses/gagal) melalui parameter URL
// ============================================================
function redirect_alert($url, $action, $status)
{
	$separator = (strpos($url, '?') !== false) ? '&' : '?'; // Tentukan pemisah URL (? atau &)
	header("Location: {$url}{$separator}action={$action}&status={$status}");
	exit;
}

// ============================================================
// FUNGSI PEMBANTU: uploadImg()
// Tujuan: Memvalidasi dan memindahkan file foto yang diupload
//         ke folder penyimpanan: dashboard/assets/profile/
//
// ALUR KERJA:
// (1) Ambil informasi file dari $_FILES['img_user']
// (2) Validasi ekstensi file (hanya jpg, jpeg, png)
// (3) Validasi ukuran file (maksimal 1 MB)
// (4) Buat nama file baru yang unik agar tidak bentrok
// (5) Pindahkan file dari folder sementara ke folder profile/
// (6) Kembalikan nama file baru
// ============================================================
function uploadImg()
{
	$nama_img = $_FILES['img_user']['name'];     // (1) Nama asli file yang diupload
	$size_img = $_FILES['img_user']['size'];     // (1) Ukuran file dalam bytes
	$tmp_name = $_FILES['img_user']['tmp_name']; // (1) Path sementara file di server sebelum dipindah

	$valid_img   = ['jpg', 'jpeg', 'png'];                                         // (2) Daftar ekstensi yang diperbolehkan
	$extensi_img = strtolower(pathinfo($nama_img, PATHINFO_EXTENSION));            // (2) Ambil ekstensi file, jadikan huruf kecil semua

	// ❌ (2) Ekstensi tidak valid → kembalikan dengan peringatan
	if (!in_array($extensi_img, $valid_img)) {
		redirect_alert('../dashboard/admin?page=profile', 'invalidext', 'warning');
	}

	// ❌ (3) Ukuran terlalu besar (lebih dari 1 MB = 1.000.000 bytes) → kembalikan dengan peringatan
	if ($size_img > 1000000) {
		redirect_alert('../dashboard/admin?page=profile', 'filesize', 'warning');
	}

	// ✅ (4) Lolos validasi! Buat nama file baru yang unik menggunakan uniqid() agar tidak bentrok dengan foto lain
	$img_baru = uniqid() . '.' . $extensi_img;

	// (5) Pindahkan file dari folder sementara PHP ke folder penyimpanan foto profil
	move_uploaded_file($tmp_name, '../dashboard/assets/profile/' . $img_baru);

	return $img_baru; // (6) Kembalikan nama file baru untuk disimpan ke database
}

// ============================================================
// AKSI [1]: GANTI FOTO PROFIL
// Dipicu oleh tombol "Update Foto" (btn_editfotoakun) di halaman profile.php
//
// ALUR KERJA:
// (1) Ambil ID pengguna dan nama file foto lama dari form
// (2) Jika tidak ada file baru diupload, pertahankan foto lama
// (3) Jika ada file baru, hapus foto lama dari server lalu upload foto baru
// (4) Perbarui kolom img_user di tabel 'users' di database
// (5) Redirect dengan status sukses atau error
// ============================================================
if (isset($_POST['btn_editfotoakun'])) {
	$id_user  = htmlspecialchars($_POST['id_user']);  // (1) ID pengguna yang fotonya akan diubah
	$img_lama = htmlspecialchars($_POST['img_lama']); // (1) Nama file foto lama (untuk dihapus setelah diganti)

	if ($_FILES['img_user']['error'] == 4) {
		// (2) Error code 4 = tidak ada file yang diupload → gunakan foto lama saja
		$img_user = $img_lama;
	} else {
		// (3) Ada file baru → hapus foto lama dulu dari hardisk server agar tidak menumpuk
		unlink(filename: '../dashboard/assets/profile/' . $img_lama);
		$img_user = uploadImg(); // Lalu upload dan simpan foto baru, dapatkan nama file barunya
	}

	// Sanitize input
	$query_update = "UPDATE users SET img_user = '$img_user' WHERE id_user = '$id_user'";
	// (4) Perbarui kolom img_user di tabel 'users' untuk pengguna dengan id_user tersebut
	$update = mysqli_query($koneksi, "UPDATE users SET img_user = '$img_user' WHERE id_user = '$id_user'");

	// (5) Cek apakah update berhasil
	if ($update) {
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=success');
		exit();
	} else {
		// Ambil pesan kesalahan dari database untuk debugging
		$error = mysqli_error($koneksi);

		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=error&message=' . urlencode($error));
		exit();
	}
}

// ============================================================
// AKSI [2]: TAMBAH PENGGUNA BARU (dari dalam Dashboard Admin)
// Dipicu oleh tombol "Tambah" (btn_adminregister)
//
// ALUR KERJA:
// (1) Ambil dan bersihkan semua isian dari formulir
// (2) Cek apakah username sudah digunakan
// (3) Validasi kecocokan password dan konfirmasi password
// (4) Generate ID pengguna baru secara otomatis (USER001, USER002, ...)
// (5) Upload foto profil
// (6) Simpan semua data ke tabel 'users' di database
// ============================================================
if (isset($_POST['btn_adminregister'])) {

	// (1) Sanitize input — bersihkan setiap isian dari karakter berbahaya (mencegah XSS)
	$nama_user           = htmlspecialchars(trim($_POST['nama_user']));           // → kolom nama_user
	$alamat              = htmlspecialchars(trim($_POST['alamat']));              // → kolom alamat
	$tempat_lahir        = htmlspecialchars(trim($_POST['tempat_lahir']));        // → kolom tempat_lahir
	$tanggal_lahir       = htmlspecialchars(trim($_POST['tanggal_lahir']));       // → kolom tanggal_lahir
	$jenis_kelamin       = htmlspecialchars(trim($_POST['jenis_kelamin']));       // → kolom jenis_kelamin
	$username            = htmlspecialchars(trim($_POST['username']));            // → kolom username
	$no_telp             = htmlspecialchars(trim($_POST['no_telp']));             // → kolom no_telp
	$password            = htmlspecialchars(trim($_POST['password']));            // Password biasa (belum di-hash)
	$konfirmasi_password = htmlspecialchars(trim($_POST['konfirmasi_password'])); // Konfirmasi password
	$email               = htmlspecialchars(trim($_POST['email']));               // → kolom email

	// (2) Cek apakah username sudah dipakai di tabel 'users'
	$cek_user_query  = "SELECT COUNT(*) FROM users WHERE username = '$username'";
	$cek_user_result = mysqli_query($koneksi, $cek_user_query);
	$cek_user        = mysqli_fetch_array($cek_user_result)[0]; // Hasilnya angka: 0 = bebas, >0 = sudah ada

	if ($cek_user > 0) {
		// Username sudah dipakai → kembalikan dengan peringatan
		header("Location: ../dashboard/admin?page=registrasi&action=userexist&status=warning");
		exit();
	} else if ($password !== $konfirmasi_password) {
		// (3) Password tidak cocok → kembalikan dengan peringatan
		header("Location: ../dashboard/admin?page=registrasi&action=passwordnotsame&status=warning");
		exit();
	} else {
		// (4) Generate ID pengguna baru: USER001, USER002, dst.
		$id_userprefix = 'USER';

		// Ambil ID pengguna terakhir di database untuk menentukan nomor urut berikutnya
		$query_last_id  = "SELECT id_user FROM users WHERE id_user LIKE 'USER%' ORDER BY id_user DESC LIMIT 1";
		$result_last_id = mysqli_query($koneksi, $query_last_id);

		if (mysqli_num_rows($result_last_id) > 0) {
			$last_id = mysqli_fetch_array($result_last_id);

			// Ambil angka setelah prefix USER (contoh: "USER005" → ambil angka "5")
			$last_number = (int)substr($last_id['id_user'], strlen($id_userprefix));
			$new_number  = $last_number + 1; // Tambah 1 untuk nomor urut baru
		} else {
			$new_number = 1; // Belum ada pengguna → mulai dari 1
		}

		// Format ID → USER001, USER002, USER003, ... (selalu 3 digit dengan nol di depan)
		$id_user = $id_userprefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

		// Jika validasi berhasil, hapus session form sebelumnya agar tidak bentrok
		unset($_SESSION['form_data']);

		// (5) Upload foto profil ke folder dashboard/assets/profile/
		$img_user        = uploadImg();
		$hashed_password = md5($password); // Hash password dengan MD5 sebelum disimpan ke DB

		// (6) Simpan semua data pengguna baru ke tabel 'users'
		$query_tambah = "INSERT INTO users (id_user, email, img_user, nama_user, no_telp, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, username, password) 
                         VALUES ('$id_user', '$email', '$img_user', '$nama_user', '$no_telp', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$username', '$hashed_password')";

		if (mysqli_query($koneksi, $query_tambah)) {
			// Berhasil
			header('Location: ../dashboard/admin?page=registrasi&action=adduser&status=success');
		} else {
			// Gagal
			header('Location: ../dashboard/admin?page=registrasi&action=adduser&status=error');
		}
		exit();
	}
}

// ============================================================
// AKSI [3]: UPDATE DATA PRIBADI
// Dipicu oleh tombol "Simpan Data Pribadi" (btn_editdatapribadi) di halaman profile.php
//
// ALUR KERJA:
// (1) Ambil dan bersihkan semua isian dari formulir data pribadi
// (2) Jalankan query UPDATE ke tabel 'users'
// (3) Redirect dengan status sukses atau error
// ============================================================
if (isset($_POST['btn_editdatapribadi'])) {
	$id_user = htmlspecialchars(trim($_POST['id_user'])); // (1) ID pengguna yang datanya akan diperbarui

	// (1) Sanitize setiap isian dari form data pribadi
	$nama_user     = htmlspecialchars(trim($_POST['nama_user']));     // → kolom nama_user
	$no_telp       = htmlspecialchars(trim($_POST['no_telp']));       // → kolom no_telp
	$gol_darah     = htmlspecialchars(trim($_POST['gol_darah']));     // → kolom gol_darah (tidak ditampilkan di form, nilainya kosong)
	$jenis_kelamin = htmlspecialchars(trim($_POST['jenis_kelamin'])); // → kolom jenis_kelamin
	$tempat_lahir  = htmlspecialchars(trim($_POST['tempat_lahir']));  // → kolom tempat_lahir
	$tanggal_lahir = htmlspecialchars(trim($_POST['tanggal_lahir'])); // → kolom tanggal_lahir
	$alamat        = htmlspecialchars(trim($_POST['alamat']));        // → kolom alamat
	$email         = htmlspecialchars(trim($_POST['email']));         // → kolom email

	// (2) Jalankan UPDATE ke tabel 'users' — perbarui semua kolom data pribadi sekaligus
	$query_update = "UPDATE users SET nama_user = '$nama_user', email = '$email', no_telp = '$no_telp', jenis_kelamin = '$jenis_kelamin', tempat_lahir = '$tempat_lahir', tanggal_lahir = '$tanggal_lahir',  alamat = '$alamat' WHERE id_user = '$id_user'";
	$update       = mysqli_query($koneksi, $query_update);

	// (3) Eksekusi query dan cek hasilnya
	if ($update) {
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=success');
		exit();
	} else {
		// Ambil pesan kesalahan dari database
		$error = mysqli_error($koneksi);

		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=error&message=' . urlencode($error));
		exit();
	}
}

// ============================================================
// AKSI [4]: UPDATE DATA AKUN (Username & Password)
// Dipicu oleh tombol "Simpan Data Akun" (btn_editdataakun) di halaman profile.php
//
// ALUR KERJA:
// (1) Ambil username baru, password baru, dan konfirmasi password dari form
// (2) Cek apakah username baru sudah dipakai orang lain
// (3) Validasi kecocokan password baru dan konfirmasinya
// (4) Update ke DB — jika password diisi, update username + password;
//     jika password dikosongkan, update username saja
// ============================================================
if (isset($_POST['btn_editdataakun'])) {
	$id_user      = htmlspecialchars(trim($_POST['id_user']));      // ID pengguna yang akunnya akan diperbarui
	$sesi_username = htmlspecialchars(trim($_POST['sesi_username'])); // Username yang sedang aktif di sesi login

	// (1) Sanitize input
	$username_lama       = htmlspecialchars(trim($_POST['username_lama']));       // Username lama (sebelum diubah)
	$username            = htmlspecialchars(trim($_POST['username']));            // Username baru yang diinginkan
	$password            = htmlspecialchars(trim($_POST['password']));            // Password baru (boleh kosong jika tidak mau ganti)
	$konfirmasi_password = htmlspecialchars(trim($_POST['konfirmasi_password'])); // Konfirmasi password baru

	// (2) Cek apakah username baru sudah dipakai orang lain di tabel 'users'
	$cek_user_query  = "SELECT COUNT(*) FROM users WHERE username = '$username'";
	$cek_user_result = mysqli_query($koneksi, $cek_user_query);
	$cek_user        = mysqli_fetch_array($cek_user_result)[0];

	// (3) Validasi password
	if ($password !== $konfirmasi_password) {
		// Password baru tidak sama dengan konfirmasi → kembalikan dengan peringatan
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=passwordnotsame&status=warning');
		exit();
	} else if ($cek_user > 0 && $username_lama !== $username) {
		// Username sudah dipakai orang lain (dan bukan username sendiri) → kembalikan dengan peringatan
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=userexist&status=warning');
		exit();
	} else {
		// (4) Proses update ke database
		if (!empty($password)) {
			// Jika password terisi dan sama dengan konfirmasi, hash password lalu update keduanya
			$hashed_password = md5($password);
			$query_update    = "UPDATE users SET username = '$username', password = '$hashed_password' WHERE id_user = '$id_user'";
		} else {
			// Jika password tidak diubah (dikosongkan), update username saja
			$query_update = "UPDATE users SET username = '$username' WHERE id_user = '$id_user'";
		}

		$update = mysqli_query($koneksi, $query_update);

		// Eksekusi query dan cek hasilnya
		if ($update) {
			header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=success');
			exit();
		} else {
			// Ambil pesan kesalahan dari database
			$error = mysqli_error($koneksi);
			header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=edituser&status=error&message=' . urlencode($error));
			exit();
		}
	}
}

// ============================================================
// AKSI [5]: HAPUS AKUN SECARA PERMANEN
// Dipicu oleh tombol "Hapus Akun" (btn_deleteakun) di halaman profile.php
//
// ALUR KERJA:
// (1) Ambil ID pengguna dan nama file foto dari form
// (2) Hapus baris data pengguna dari tabel 'users' di database
// (3) Hapus file foto fisiknya dari folder profile/ di server
// (4) Jika menghapus akun sendiri → logout otomatis
//     Jika menghapus akun orang lain → kembali ke halaman daftar pengguna
// ============================================================
if (isset($_POST['btn_deleteakun'])) {
	$id_user  = htmlspecialchars($_POST['id_user']);  // (1) ID pengguna yang akan dihapus
	$img_lama = htmlspecialchars($_POST['img_user']); // (1) Nama file foto profil untuk ikut dihapus dari server

	// (2) Hapus baris data pengguna dari tabel 'users' berdasarkan id_user
	$query      = "DELETE FROM users WHERE id_user = '$id_user'";
	$query_hapus = mysqli_query($koneksi, $query);

	// (3) Eksekusi hapus & logout saat menghapus akun sendiri (Semua role)
	if ($query_hapus && $id_user == $sesi_id) {
		// (3a) Hapus file foto dari hardisk server agar tidak membuang ruang penyimpanan
		unlink('../dashboard/assets/profile/' . $img_lama);
		// (4a) Pengguna menghapus akunnya sendiri → logout otomatis
		header('Location: ../auth/logout.php');
	} else if ($query_hapus && $id_user !== $sesi_id) // Eksekusi hapus & ke data pendonor saat menghapus akun lain (Role admin)
	{
		// (3b) Hapus file foto dari hardisk server
		unlink('../dashboard/assets/profile/' . $img_lama);
		// (4b) Admin menghapus akun orang lain → kembali ke halaman daftar pengguna
		header('Location: ../dashboard/admin?page=data pendonor&action=deleteuser&status=success');
	} else {
		// Hapus gagal → kembalikan dengan pesan error
		header('Location: ../dashboard/admin?page=profile&action=deleteuser&status=error&message=' . urlencode($error));
	}
}

// Pastikan tidak ada output setelah ini — kirim buffer output ke browser
ob_end_flush();
