<?php
ob_start();
session_start();

require_once "../../config/koneksi.php";

/**
 * Class GejalaDeleteController
 * Untuk hapus data gejala
 */
class GejalaDeleteController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi proses hapus data gejala berdasarkan ID
     */
    public function delete($idGejala)
    {
        $id = (int) $idGejala;

        if ($id <= 0) {
            $this->redirect("../../admin/gejala.php");
        }

        // 1. Cek Keberadaan Data
        if ($this->isExist($id)) {
            // 2. Eksekusi Hapus
            if ($this->executeDelete($id)) {
                $this->redirect("../../admin/gejala.php?status=sukses_hapus");
            } else {
                $this->redirect("../../admin/gejala.php?status=gagal_hapus");
            }
        }

        // Fallback jika ID tidak ditemukan
        $this->redirect("../../admin/gejala.php");
    }

    /**
     * Memeriksa keberadaan ID Gejala di Database
     */
    private function isExist($id)
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_gejala FROM tb_gejala WHERE id_gejala = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    /**
     * Query Hapus Gejala via Prepared Statement
     */
    private function executeDelete($id)
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM tb_gejala WHERE id_gejala = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
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

    $controller = new GejalaDeleteController($db);
    $controller->delete($_GET['id']);
} else {
    header("Location: ../../admin/gejala.php");
    exit();
}