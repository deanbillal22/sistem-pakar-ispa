<?php
session_start();
require_once '../../config/koneksi.php';

/**
 * Class ResetPasswordController
 * Untuk validasi token reset dan update password user
 */
class ResetPasswordController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi alur utama reset password
     */
    public function handleReset($postData)
    {
        $token              = trim($postData['token'] ?? '');
        $password           = $postData['password'] ?? '';
        $konfirmasiPassword = $postData['konfirmasi_password'] ?? '';

        // 1. Validasi Input Kosong
        if (empty($token) || empty($password) || empty($konfirmasiPassword)) {
            $this->showAlert("Semua kolom input wajib diisi!", true);
        }

        // 2. Validasi Match Password
        if ($password !== $konfirmasiPassword) {
            $this->showAlert("Konfirmasi password tidak cocok!", true);
        }

        // 3. Verifikasi Keberadaan & Masa Berlaku Token
        if (!$this->isValidToken($token)) {
            $this->showAlert("Token tidak valid atau sudah kadaluarsa!", false, "../../user/lupa_password.php");
        }

        // 4. Hash Password Baru
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        // 5. Update Password & Reset Token ke Database
        $isUpdated = $this->updateUserPassword($token, $passwordHashed);

        if ($isUpdated) {
            $this->showAlert("Password berhasil diperbarui! Silakan login dengan password baru Anda.", false, "../../login.php");
        } else {
            $this->showAlert("Gagal mengupdate password di database!", true);
        }
    }

    /**
     * Memeriksa apakah token valid dan belum kadaluarsa via Prepared Statement
     */
    private function isValidToken($token)
    {
        $now = date('Y-m-d H:i:s');
        $stmt = mysqli_prepare(
            $this->db,
            "SELECT id_user FROM tb_user WHERE reset_token = ? AND reset_token_expires_at > ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ss", $token, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    /**
     * Mengubah password dan menghapus token reset
     */
    private function updateUserPassword($token, $passwordHashed)
    {
        $stmt = mysqli_prepare(
            $this->db,
            "UPDATE tb_user SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = ?"
        );
        mysqli_stmt_bind_param($stmt, "ss", $passwordHashed, $token);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Helper JavaScript Alert & Redirect
     */
    private function showAlert($message, $isBack = false, $redirectUrl = "")
    {
        $action = $isBack ? "window.history.back();" : "window.location.href = '{$redirectUrl}';";
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

// Ambil koneksi database prosedural ($conn atau $koneksi)
$db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

if (!$db) {
    die("Koneksi database gagal!");
}

$controller = new ResetPasswordController($db); 
$controller->handleReset($_POST); // Ganti dari handleRequest ke handleReset