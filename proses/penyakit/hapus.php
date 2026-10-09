<?php
ob_start();
session_start();

require_once "../../config/koneksi.php";

/**
 * Class PenyakitDeleteController
 * Untuk hapus data penyakit
 */
class PenyakitDeleteController
{
    private $db;
    private $imagePath;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
        $this->imagePath = "../../assets/images/penyakit/";
    }

    /**
     * Eksekusi proses hapus data penyakit
     */
    public function delete($idPenyakit)
    {
        $id = (int) $idPenyakit;

        if ($id <= 0) {
            $this->redirect("../../admin/penyakit.php");
        }

        // 1. Ambil data gambar penyakit
        $gambar = $this->getGambarFilename($id);

        if ($gambar !== null) {
            // 2. Hapus file gambar jika ada 
            $this->deleteImageFile($gambar);

            // 3. Hapus record dari database
            if ($this->executeDelete($id)) {
                $this->redirect("../../admin/penyakit.php?status=sukses_hapus");
            } else {
                $this->redirect("../../admin/penyakit.php?status=gagal_hapus");
            }
        }

        // Fallback jika ID tidak ditemukan
        $this->redirect("../../admin/penyakit.php");
    }

    /**
     * Mengambil nama file gambar penyakit dari database via Prepared Statement
     */
    private function getGambarFilename($id)
    {
        $stmt = mysqli_prepare($this->db, "SELECT gambar FROM tb_penyakit WHERE id_penyakit = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($data = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $data['gambar'];
        }

        mysqli_stmt_close($stmt);
        return null;
    }

    /**
     * Menghapus file gambar fisik dari server jika valid
     */
    private function deleteImageFile($filename)
    {
        if (!empty($filename) && $filename !== 'default.png') {
            $targetFile = $this->imagePath . $filename;
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
        }
    }

    /**
     * Query Hapus Penyakit via Prepared Statement
     */
    private function executeDelete($id)
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM tb_penyakit WHERE id_penyakit = ?");
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
    // Ambil koneksi database prosedural dari koneksi.php ($conn / $koneksi)
    $db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if (!$db) {
        die("Koneksi database gagal!");
    }

    $controller = new PenyakitDeleteController($db);
    $controller->delete($_GET['id']);
} else {
    header("Location: ../../admin/penyakit.php");
    exit();
}