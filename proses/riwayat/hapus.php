<?php
session_start();
require_once "../../config/koneksi.php";

/**
 * Class RiwayatDeleteController
 * untuk hapus data riwayat diagnosis
 */
class RiwayatDeleteController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function delete($idHasil)
    {
        $id = (int) $idHasil;

        if ($id <= 0) {
            $this->redirect("../../admin/riwayat.php");
        }

        if ($this->executeDelete($id)) {
            $this->showAlert("Data riwayat diagnosis dan konsultasi berhasil dihapus!", "../../admin/riwayat.php");
        } else {
            $this->showAlert("Gagal menghapus data riwayat!", "../../admin/riwayat.php");
        }
    }

    /**
     * Query Hapus dari tb_hasil_diagnosis dan tb_konsultasi sekaligus
     */
    private function executeDelete($id)
    {
        // 1. Ambil dulu id_konsultasi berdasarkan id_hasil (jika ada relasi ID)
        // Atau jika id_hasil bernilai sama dengan id_konsultasi (relasi 1 ke 1)
        $idKonsultasi = $this->getIdKonsultasi($id);

        // 2. Hapus data dari tb_hasil_diagnosis dulu (tabel anak)
        $stmt1 = mysqli_prepare($this->db, "DELETE FROM tb_hasil_diagnosis WHERE id_hasil = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        $success1 = mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // 3. Hapus data dari tb_konsultasi (tabel induk) jika ID-nya ketemu
        $success2 = true;
        if ($idKonsultasi) {
            $stmt2 = mysqli_prepare($this->db, "DELETE FROM tb_konsultasi WHERE id_konsultasi = ?");
            mysqli_stmt_bind_param($stmt2, "i", $idKonsultasi);
            $success2 = mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }

        return $success1 && $success2;
    }

    /**
     * Helper untuk mengambil relasi id_konsultasi dari tb_hasil_diagnosis
     */
    private function getIdKonsultasi($idHasil)
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_konsultasi FROM tb_hasil_diagnosis WHERE id_hasil = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idHasil);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $idKonsultasi = null;
        if ($row = mysqli_fetch_assoc($result)) {
            $idKonsultasi = $row['id_konsultasi'];
        }
        mysqli_stmt_close($stmt);

        return $idKonsultasi;
    }

    private function showAlert($message, $redirectUrl)
    {
        echo "<script>
                alert('{$message}');
                window.location.href = '{$redirectUrl}';
              </script>";
        exit();
    }

    private function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }
}

/* ==========================================================
   INITIALIZATION & EXECUTION
========================================================== */

if (isset($_GET['id_hasil'])) {
    // Ambil koneksi database prosedural ($conn atau $koneksi)
    $db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if (!$db) {
        die("Koneksi database gagal!");
    }

    $controller = new RiwayatDeleteController($db);
    $controller->delete($_GET['id_hasil']);
} else {
    header("Location: ../../admin/riwayat.php");
    exit();
}