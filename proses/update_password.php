<?php
session_start();
require_once "../config/koneksi.php";

/**
 * Class PasswordController
 * Untuk ganti password user
 */
class PasswordController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi proses ganti password
     */
    public function updatePassword($idUser, $data)
    {
        // Validasi Autentikasi
        if (!isset($_SESSION['login']) || empty($idUser)) {
            header("Location: ../login.php");
            exit;
        }

        $passwordLama = $data['password_lama'] ?? '';
        $passwordBaru = $data['password_baru'] ?? '';
        $konfirmasi   = $data['konfirmasi_password'] ?? '';

        // Ambil data password user dari database
        $user = $this->getUserPassword((int)$idUser);

        if (!$user) {
            $this->showAlert("User tidak ditemukan!", true);
        }

        // Verifikasi Password Lama
        if (!password_verify($passwordLama, $user['password'])) {
            $this->showAlert("Password lama tidak sesuai!", true);
        }

        // Validasi Konfirmasi Password
        if ($passwordBaru !== $konfirmasi) {
            $this->showAlert("Konfirmasi password tidak sama!", true);
        }

        // Validasi Panjang Password Minimal
        if (strlen($passwordBaru) < 6) {
            $this->showAlert("Password minimal 6 karakter!", true);
        }

        // Hash Password Baru
        $passwordBaruHash = password_hash($passwordBaru, PASSWORD_DEFAULT);

        // Simpan Password Baru ke Database
        $isUpdated = $this->executeUpdatePassword((int)$idUser, $passwordBaruHash);

        if ($isUpdated) {
            $this->showAlert("Password berhasil diubah.", false, "../user/profil.php");
        } else {
            $this->showAlert("Gagal mengubah password.", true);
        }
    }

    /**
     * Ambil password lama user dari database via Prepared Statement
     */
    private function getUserPassword($idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT password FROM tb_user WHERE id_user = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idUser);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $user;
    }

    /**
     * Query update password
     */
    private function executeUpdatePassword($idUser, $passwordHash)
    {
        $stmt = mysqli_prepare($this->db, "UPDATE tb_user SET password = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "si", $passwordHash, $idUser);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Helper untuk Alert & Redirect
     */
    private function showAlert($message, $isBack = false, $redirectUrl = "")
    {
        $action = $isBack ? "window.history.back();" : "window.location='{$redirectUrl}';";
        echo "<script>
                alert('{$message}');
                {$action}
              </script>";
        exit;
    }
}

/* ==========================================================
   INITIALIZATION & EXECUTION
========================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil koneksi database 
    $db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if (!$db) {
        die("Koneksi database gagal!");
    }

    // Ambil ID User dari session login
    $idUser = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0;

    // Panggil method dengan 2 argumen: ($idUser, $_POST)
    $passwordController = new PasswordController($db);
    $passwordController->updatePassword($idUser, $_POST);
} else {
    header("Location: ../user/profil.php");
    exit;
}