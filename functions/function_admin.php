<?php

include 'koneksi.php';
session_start();

$sesi_role 	= $_SESSION['sesi_role'];
$sesi_id 	= $_SESSION['sesi_id'];

// Pastikan tidak ada output sebelum ini
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
	header('Location: ../dashboard');
	exit();
}

// Simpan semua data form ke session
$_SESSION['form_data'] = [
	'nama_user'      => isset($_POST['nama_user']) ? htmlspecialchars(trim($_POST['nama_user'])) : '',
	'id_user'      => isset($_POST['id_user']) ? htmlspecialchars(trim($_POST['id_user'])) : '',
	'alamat'         => isset($_POST['alamat']) ? htmlspecialchars(trim($_POST['alamat'])) : '',
	'tempat_lahir'   => isset($_POST['tempat_lahir']) ? htmlspecialchars(trim($_POST['tempat_lahir'])) : '',
	'tanggal_lahir'  => isset($_POST['tanggal_lahir']) ? htmlspecialchars(trim($_POST['tanggal_lahir'])) : '',
	'jenis_kelamin'  => isset($_POST['jenis_kelamin']) ? htmlspecialchars(trim($_POST['jenis_kelamin'])) : '',
	'username'       => isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '',
	'no_telp'        => isset($_POST['no_telp']) ? htmlspecialchars(trim($_POST['no_telp'])) : '',
	'email'        => isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '',
];

function redirect_alert($url, $action, $status)
{
	$separator = (strpos($url, '?') !== false) ? '&' : '?';
	header("Location: {$url}{$separator}action={$action}&status={$status}");
	exit;
}

function uploadImg()
{
	$nama_img  = $_FILES['img_user']['name'];
	$size_img  = $_FILES['img_user']['size'];
	$tmp_name  = $_FILES['img_user']['tmp_name'];

	$valid_img     = ['jpg', 'jpeg', 'png'];
	$extensi_img   = strtolower(pathinfo($nama_img, PATHINFO_EXTENSION));

	// ❌ Ekstensi tidak valid
	if (!in_array($extensi_img, $valid_img)) {
		redirect_alert('../dashboard/admin?page=profile', 'invalidext', 'warning');
	}

	// ❌ Ukuran terlalu besar
	if ($size_img > 1000000) {
		redirect_alert('../dashboard/admin?page=profile', 'filesize', 'warning');
	}

	// ✅ Lolos validasi
	$img_baru = uniqid() . '.' . $extensi_img;
	move_uploaded_file($tmp_name, '../dashboard/assets/profile/' . $img_baru);

	return $img_baru;
}

if (isset($_POST['btn_editfotoakun'])) {
	$id_user 		= htmlspecialchars($_POST['id_user']);
	$img_lama      	= htmlspecialchars($_POST['img_lama']);

	if ($_FILES['img_user']['error'] == 4) {
		$img_user	= $img_lama;
	} else {
		unlink(filename: '../dashboard/assets/profile/' . $img_lama);
		$img_user	= uploadImg();
	}

	// Sanitize input
	$query_update 	= "UPDATE users SET img_user = '$img_user' WHERE id_user = '$id_user'";
	$update 		= mysqli_query($koneksi, "UPDATE users SET img_user = '$img_user' WHERE id_user = '$id_user'");

	// Eksekusi query
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

if (isset($_POST['btn_adminregister'])) {

	// Sanitize input
	$nama_user          	= htmlspecialchars(trim($_POST['nama_user']));
	$alamat             	= htmlspecialchars(trim($_POST['alamat']));
	$tempat_lahir       	= htmlspecialchars(trim($_POST['tempat_lahir']));
	$tanggal_lahir      	= htmlspecialchars(trim($_POST['tanggal_lahir']));
	$jenis_kelamin      	= htmlspecialchars(trim($_POST['jenis_kelamin']));
	$username         		= htmlspecialchars(trim($_POST['username']));
	$no_telp            	= htmlspecialchars(trim($_POST['no_telp']));
	$password           	= htmlspecialchars(trim($_POST['password']));
	$konfirmasi_password 	= htmlspecialchars(trim($_POST['konfirmasi_password']));
	$email 					= htmlspecialchars(trim($_POST['email']));

	// Validasi username
	$cek_user_query 	= "SELECT COUNT(*) FROM users WHERE username = '$username'";
	$cek_user_result 	= mysqli_query($koneksi, $cek_user_query);
	$cek_user 			= mysqli_fetch_array($cek_user_result)[0];

	if ($cek_user > 0) {
		header("Location: ../dashboard/admin?page=registrasi&action=userexist&status=warning");
		exit();
	} else if ($password !== $konfirmasi_password) {
		header("Location: ../dashboard/admin?page=registrasi&action=passwordnotsame&status=warning");
		exit();
	} else {
		// Generate ID User
		$id_userprefix = 'USER';

		$query_last_id = "SELECT id_user FROM users WHERE id_user LIKE 'USER%' ORDER BY id_user DESC LIMIT 1";
		$result_last_id = mysqli_query($koneksi, $query_last_id);

		if (mysqli_num_rows($result_last_id) > 0) {
			$last_id = mysqli_fetch_array($result_last_id);

			// Ambil angka setelah prefix USER
			$last_number = (int)substr($last_id['id_user'], strlen($id_userprefix));
			$new_number = $last_number + 1;
		} else {
			$new_number = 1;
		}

		// Format ID USER001, USER002...
		$id_user = $id_userprefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

		// Jika validasi berhasil, hapus session form sebelumnya
		unset($_SESSION['form_data']);

		// Proses insert ke database
		$img_user			= uploadImg();
		$hashed_password 	= md5($password);
		$query_tambah 		= "INSERT INTO users (id_user, email, img_user, nama_user, no_telp, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, username, password) 
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

if (isset($_POST['btn_editdatapribadi'])) {
	$id_user 		= htmlspecialchars(trim($_POST['id_user']));

	// Sanitize input
	$nama_user      = htmlspecialchars(trim($_POST['nama_user']));
	$no_telp        = htmlspecialchars(trim($_POST['no_telp']));
	$gol_darah      = htmlspecialchars(trim($_POST['gol_darah']));
	$jenis_kelamin  = htmlspecialchars(trim($_POST['jenis_kelamin']));
	$tempat_lahir   = htmlspecialchars(trim($_POST['tempat_lahir']));
	$tanggal_lahir  = htmlspecialchars(trim($_POST['tanggal_lahir']));
	$alamat         = htmlspecialchars(trim($_POST['alamat']));
	$email         	= htmlspecialchars(trim($_POST['email']));


	$query_update 	= "UPDATE users SET nama_user = '$nama_user', email = '$email', no_telp = '$no_telp', jenis_kelamin = '$jenis_kelamin', tempat_lahir = '$tempat_lahir', tanggal_lahir = '$tanggal_lahir',  alamat = '$alamat' WHERE id_user = '$id_user'";
	$update 		= mysqli_query($koneksi, $query_update);

	// Eksekusi query

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

if (isset($_POST['btn_editdataakun'])) {
	$id_user 		= htmlspecialchars(trim($_POST['id_user']));
	$sesi_username 	= htmlspecialchars(trim($_POST['sesi_username']));

	// Sanitize input	
	$username_lama 	= htmlspecialchars(trim($_POST['username_lama']));
	$username 		= htmlspecialchars(trim($_POST['username']));
	$password 		= htmlspecialchars(trim($_POST['password']));
	$konfirmasi_password = htmlspecialchars(trim($_POST['konfirmasi_password']));

	// Validasi username
	$cek_user_query 	= "SELECT COUNT(*) FROM users WHERE username = '$username'";
	$cek_user_result 	= mysqli_query($koneksi, $cek_user_query);
	$cek_user 			= mysqli_fetch_array($cek_user_result)[0];

	// Validasi password
	if ($password !== $konfirmasi_password) {
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=passwordnotsame&status=warning');
		exit();
	} else if ($cek_user > 0 && $username_lama !== $username) {
		header('Location: ../dashboard/admin?page=profile&id=' . $id_user . '&action=userexist&status=warning');
		exit();
	} else {
		// Proses update ke database
		if (!empty($password)) {
			// Jika password terisi dan sama dengan konfirmasi, hash password
			$hashed_password = md5($password);
			$query_update = "UPDATE users SET username = '$username', password = '$hashed_password' WHERE id_user = '$id_user'";
		} else {
			// Jika password tidak diubah, update tanpa mengubah password
			$query_update = "UPDATE users SET username = '$username' WHERE id_user = '$id_user'";
		}

		$update = mysqli_query($koneksi, $query_update);

		// Eksekusi query
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

if (isset($_POST['btn_deleteakun'])) {
	$id_user    	= htmlspecialchars($_POST['id_user']);
	$img_lama  		= htmlspecialchars($_POST['img_user']);

	$query 			= "DELETE FROM users WHERE id_user = '$id_user'";
	$query_hapus	= mysqli_query($koneksi, $query);

	// Eksekusi hapus & logout saat menghapus akun sendiri (Semua role)
	if ($query_hapus && $id_user == $sesi_id) {
		unlink('../dashboard/assets/profile/' . $img_lama);
		header('Location: ../auth/logout.php');
	} else if ($query_hapus && $id_user !== $sesi_id) // Eksekusi hapus & ke data pendonor saat menghapus akun lain (Role admin)
	{
		unlink('../dashboard/assets/profile/' . $img_lama);
		header('Location: ../dashboard/admin?page=data pendonor&action=deleteuser&status=success');
	} else {
		header('Location: ../dashboard/admin?page=profile&action=deleteuser&status=error&message=' . urlencode($error));
	}
}

// Pastikan tidak ada output setelah ini
ob_end_flush();
