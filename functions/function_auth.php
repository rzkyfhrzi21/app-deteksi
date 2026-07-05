<?php
// ============================================================
// FILE: function_auth.php
// TUJUAN: Memproses formulir LOGIN dan REGISTRASI pengguna
//
// Ibarat "petugas keamanan gedung" — file ini yang memutuskan
// apakah seseorang boleh masuk (login) atau mendaftar (register).
// Semua klik tombol dari halaman auth/ diarahkan ke sini.
//
// BERISI DUA BAGIAN BESAR:
//   [A] Proses Login   → dipicu tombol btn_login
//   [B] Proses Daftar  → dipicu tombol btn_register
// ============================================================

session_start(); // Hidupkan sistem sesi agar bisa menyimpan data pengguna yang berhasil login

include 'koneksi.php'; // Sambungkan ke database MySQL (variabel $koneksi tersedia)

// ============================================================
// BAGIAN A: PROSES LOGIN
// Dipanggil ketika pengguna mengklik tombol "Masuk" di halaman login.php
// ============================================================
if (isset($_POST['btn_login'])) {

	// (1) Ambil username dan password dari formulir yang dikirim
	$username = $_POST['username'];
	$password = md5($_POST['password']); // Ubah password teks biasa → kode MD5 (agar cocok dengan yang tersimpan di DB)

	// (2) Cari baris di tabel 'users' yang cocok dengan username DAN password tersebut
	$sql_login   = mysqli_query($koneksi, "SELECT * from users where username = '$username' and password = '$password'");
	$jumlah_user = mysqli_num_rows($sql_login); // Hitung berapa baris yang cocok (harusnya 0 atau 1)
	$data_user   = mysqli_fetch_array($sql_login); // Ambil data pengguna yang ditemukan

	// (3) Jika ada 1 pengguna yang cocok, berarti login berhasil
	if ($jumlah_user > 0) {

		// (4) Simpan data penting pengguna ke dalam SESSION (semacam kartu identitas sementara di server)
		$_SESSION['sesi_id']       = $data_user['id_user'];   // Simpan ID unik pengguna  (contoh: USER001)
		$_SESSION['sesi_username'] = $data_user['username'];  // Simpan username pengguna
		$_SESSION['sesi_nama']     = $data_user['nama_user']; // Simpan nama lengkap pengguna
		$_SESSION['sesi_email']    = $data_user['email'];     // Simpan email pengguna

		if ($data_user['img_user'] == '') {
			// (5a) Jika foto profil belum diisi, arahkan ke halaman profil dulu supaya pengguna melengkapi data
			// =====================
			// CATAT AKSES LOGIN
			// =====================
			require_once 'log_akses.php'; // Rekam jejak: catat bahwa pengguna ini baru saja login ke tabel 'rekam_akses_web'

			header('Location: ../dashboard/admin?page=profile&id=' . $data_user['id_user']); // Arahkan ke halaman profil
		} else {
			// (5b) Jika foto profil sudah ada, langsung masuk ke halaman dashboard utama
			// =====================
			// CATAT AKSES LOGIN
			// =====================
			require_once 'log_akses.php'; // Rekam jejak: catat bahwa pengguna ini baru saja login ke tabel 'rekam_akses_web'

			header('Location: ../dashboard/admin'); // Arahkan ke halaman dashboard
		}
	} else {
		// (6) Jika tidak ada yang cocok, kembalikan ke halaman login dengan pesan error
		header("Location: ../auth/login?action=login&status=error");
	}
}

// ============================================================
// BAGIAN B: PROSES REGISTRASI (DAFTAR AKUN BARU)
// Dipanggil ketika pengguna mengklik tombol "Daftar" di halaman register.php
// ============================================================
if (isset($_POST['btn_register'])) {

	// (1) Ambil dan bersihkan semua isian dari formulir pendaftaran
	$nama_user           = htmlspecialchars($_POST['nama_user']);           // Nama lengkap (dibersihkan dari karakter berbahaya)
	$username            = htmlspecialchars($_POST['username']);            // Username yang dipilih
	$password            = md5($_POST['password']);                        // Password → diubah ke MD5 sebelum disimpan
	$konfirmasi_password = md5($_POST['konfirmasi_password']);             // Konfirmasi password → juga diubah ke MD5

	// (2) Cek apakah username yang diinginkan sudah dipakai orang lain di tabel 'users'
	$sql_login    = mysqli_query($koneksi, "SELECT * from users where username = '$username'");
	$jumlah_users = mysqli_num_rows($sql_login); // Jumlah akun dengan username tersebut (0 = belum ada, >0 = sudah ada)
	$data_users   = mysqli_fetch_array($sql_login);

	// (3) Validasi: Apakah password dan konfirmasi password sama?
	if ($password !== $konfirmasi_password) {
		// Jika tidak sama, kembalikan ke register dengan pesan peringatan
		header("Location: ../auth/register?action=passwordnotsame&status=warning&username=" . $username . '&nama_user=' . $nama_user);
	} else {
		// (4) Validasi: Apakah username sudah dipakai?
		if ($jumlah_users > 0) {
			// Username sudah ada di database, kembalikan dengan pesan peringatan
			header("Location: ../auth/register?action=userexist&status=warning&nama_user=" . $nama_user);
		} else {
			// (5) Semua validasi lulus! Buat ID pengguna baru secara otomatis
			$id_userprefix = 'USER'; // Semua ID pengguna diawali dengan "USER"

			// Ambil ID pengguna terakhir dari database (untuk menentukan nomor urut berikutnya)
			$query_last_id  = "SELECT id_user FROM users WHERE id_user LIKE 'USER%' ORDER BY id_user DESC LIMIT 1";
			$result_last_id = mysqli_query($koneksi, $query_last_id);

			if (mysqli_num_rows($result_last_id) > 0) {
				$last_id = mysqli_fetch_array($result_last_id);

				// Ambil angka setelah prefix "USER" (contoh: "USER005" → ambil "5")
				$last_number = (int)substr($last_id['id_user'], strlen($id_userprefix));
				$new_number  = $last_number + 1; // Tambahkan 1 untuk mendapatkan nomor urut baru
			} else {
				$new_number = 1; // Jika belum ada pengguna sama sekali, mulai dari 1
			}

			// (6) Format ID menjadi: USER001, USER002, USER003, dst. (selalu 3 digit)
			$id_user = $id_userprefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

			// (7) Simpan data pengguna baru ke tabel 'users' di database
			$query_daftar = "INSERT into users 
                                    set username    = '$username', 
                                        id_user   = '$id_user', 
                                        nama_user   = '$nama_user', 
                                        password    = '$password'";
			$daftar = mysqli_query($koneksi, $query_daftar);

			// (8) Cek apakah berhasil disimpan
			if ($daftar) {
				// Berhasil → arahkan ke login dengan pesan sukses agar pengguna bisa langsung masuk
				header("Location: ../auth/login?action=registered&status=success");
			} else {
				// Gagal → kembali ke halaman daftar tanpa pesan (log error bisa dilihat di server)
				header("Location: ../auth/register");
			}
		}
	}
}
