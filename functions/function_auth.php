<?php

session_start();

include 'koneksi.php';

if (isset($_POST['btn_login'])) {
	$username 		= $_POST['username'];
	$password 		= md5($_POST['password']);

	$sql_login 		= mysqli_query($koneksi, "SELECT * from users where username = '$username' and password = '$password'");
	$jumlah_user 	= mysqli_num_rows($sql_login);
	$data_user		= mysqli_fetch_array($sql_login);

	if ($jumlah_user > 0) {
		$_SESSION['sesi_id']		= $data_user['id_user'];
		$_SESSION['sesi_username']	= $data_user['username'];
		$_SESSION['sesi_nama']		= $data_user['nama_user'];
		$_SESSION['sesi_email']		= $data_user['email'];

		if ($data_user['img_user'] == '') {
			// =====================
			// CATAT AKSES LOGIN
			// =====================
			require_once 'log_akses.php';

			header('Location: ../dashboard/admin?page=profile&id=' . $data_user['id_user']);
		} else {
			// =====================
			// CATAT AKSES LOGIN
			// =====================
			require_once 'log_akses.php';

			header('Location: ../dashboard/admin');
		}
	} else {
		header("Location: ../auth/login?action=login&status=error");
	}
}

if (isset($_POST['btn_register'])) {
	$nama_user          	= htmlspecialchars($_POST['nama_user']);
	$username          		= htmlspecialchars($_POST['username']);
	$password               = md5($_POST['password']);
	$konfirmasi_password    = md5($_POST['konfirmasi_password']);

	$sql_login          = mysqli_query($koneksi, "SELECT * from users where username = '$username'");
	$jumlah_users       = mysqli_num_rows($sql_login);
	$data_users         = mysqli_fetch_array($sql_login);

	if ($password !== $konfirmasi_password) {
		header("Location: ../auth/register?action=passwordnotsame&status=warning&username=" . $username . '&nama_user=' . $nama_user);
	} else {
		if ($jumlah_users > 0) {
			header("Location: ../auth/register?action=userexist&status=warning&nama_user=" . $nama_user);
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

			$query_daftar    = "INSERT into users 
                                    set username    = '$username', 
                                        id_user   = '$id_user', 
                                        nama_user   = '$nama_user', 
                                        password    = '$password'";
			$daftar         = mysqli_query($koneksi, $query_daftar);

			if ($daftar) {
				header("Location: ../auth/login?action=registered&status=success");
			} else {
				header("Location: ../auth/register");
			}
		}
	}
}
