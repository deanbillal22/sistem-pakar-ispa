<?php
session_start();
require_once "../../config/koneksi.php";

/**
 * Class UserDeleteController
 * untuk hapus data user
 */
class UserDeleteController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi proses hapus data pengguna berdasarkan ID
     */
    public function delete($idUser)
    {
        $id = (int) $idUser;

        if ($id <= 0) {
            $this->redirect("../../admin/pengguna.php");
        }

        if ($this->executeDelete($id)) {
            $this->showAlert("Data pengguna berhasil dihapus!", "../../admin/pengguna.php");
        } else {
            $this->showAlert("Gagal menghapus data pengguna!", "../../admin/pengguna.php");
        }
    }

    /**
     * Query Hapus Pengguna via Prepared Statement
     */
    private function executeDelete($id)
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM tb_user WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Helper JavaScript Alert & Redirect
     */
    private function showAlert($message, $redirectUrl)
    {
        echo "<script>
                alert('{$message}');
                window.location.href='{$redirectUrl}';
              </script>";
        exit();
    }

    /**
     * Helper Redirect Header
     */
    private function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }
}

/* ==========================================================
   INITIALIZATION & EXECUTION
========================================================== */

if (isset($_GET['id'])) {
    // Ambil koneksi database prosedural ($conn atau $koneksi)
    $db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if (!$db) {
        die("Koneksi database gagal!");
    }

    $controller = new UserDeleteController($db);
    $controller->delete($_GET['id']);
} else {
    header("Location: ../../admin/gejala.php");
    exit();
}