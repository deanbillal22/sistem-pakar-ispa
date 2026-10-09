<?php
session_start();
require_once "../config/koneksi.php";

/**
 * Class ProfileController
 * Untuk update informasi profil user
 */
class ProfileController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi proses update profil
     */
    public function updateProfile($data)
{
    // Validasi Autentikasi
    if (!isset($_SESSION['login'])) {
        header("Location: ../login.php");
        exit;
    }

    // Filter dan Sanitasi Input
    $idUser      = (int)($data['id_user'] ?? 0);
    $namaLengkap = trim($data['nama_lengkap'] ?? '');
    $username    = trim($data['username'] ?? '');
    $email       = trim($data['email'] ?? '');
    $noHp        = trim($data['no_hp'] ?? '');
    $alamat      = trim($data['alamat'] ?? '');

    // === VALIDASI NOMOR HP (Hanya Angka) ===
    if (!empty($noHp) && !ctype_digit($noHp)) {
        $this->showAlert("Nomor HP hanya boleh berisi angka!", true);
    }

    // Validasi Username Unik
    if ($this->isUsernameExist($username, $idUser)) {
        $this->showAlert("Username sudah digunakan!", true);
    }

    // Validasi Email Unik
    if ($this->isEmailExist($email, $idUser)) {
        $this->showAlert("Email sudah digunakan!", true);
    }

    // Eksekusi Update ke Database
    $isUpdated = $this->executeUpdate($idUser, $namaLengkap, $username, $email, $noHp, $alamat);

    if ($isUpdated) {
        $_SESSION['nama'] = $namaLengkap;
        $this->showAlert("Profil berhasil diperbarui.", false, "../user/profil.php");
    } else {
        $this->showAlert("Profil gagal diperbarui.", true);
    }
}

    /**
     * Cek keberadaan username pada user lain
     */
    private function isUsernameExist($username, $idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_user FROM tb_user WHERE username = ? AND id_user != ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "si", $username, $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    /**
     * Cek keberadaan email pada user lain
     */
    private function isEmailExist($email, $idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_user FROM tb_user WHERE email = ? AND id_user != ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "si", $email, $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    /**
     * Query update data profil
     */
    private function executeUpdate($idUser, $namaLengkap, $username, $email, $noHp, $alamat)
    {
        $stmt = mysqli_prepare(
            $this->db,
            "UPDATE tb_user SET nama_lengkap = ?, username = ?, email = ?, no_hp = ?, alamat = ? WHERE id_user = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssssi", $namaLengkap, $username, $email, $noHp, $alamat, $idUser);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Helper untuk menampilkan Alert & Redirect
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
    // Ambil koneksi dari file config/koneksi.php ($conn atau $koneksi)
    $db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if (!$db) {
        die("Koneksi database gagal!");
    }

    $profileController = new ProfileController($db);
    $profileController->updateProfile($_POST);
} else {
    header("Location: ../user/profil.php");
    exit;
}