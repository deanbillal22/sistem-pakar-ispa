<?php
session_start();
require_once "../../config/koneksi.php";

/**
 * Class RuleDeleteController
 * Untuk hapus data basis aturan (rule) 
 */
class RuleDeleteController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi penghapusan data rule dan detailnya secara terisolasi (Transaction)
     */
    public function delete($idRule)
    {
        $id = (int) $idRule;

        if ($id <= 0) {
            $this->redirect("../../admin/rule.php");
        }

        // Mulai Database Transaction
        mysqli_begin_transaction($this->db);

        try {
            // 1. Hapus relasi detail di tb_rule_detail
            $stmtDetail = mysqli_prepare($this->db, "DELETE FROM tb_rule_detail WHERE id_rule = ?");
            mysqli_stmt_bind_param($stmtDetail, "i", $id);
            mysqli_stmt_execute($stmtDetail);
            mysqli_stmt_close($stmtDetail);

            // 2. Hapus data utama di tb_rule
            $stmtMain = mysqli_prepare($this->db, "DELETE FROM tb_rule WHERE id_rule = ?");
            mysqli_stmt_bind_param($stmtMain, "i", $id);
            mysqli_stmt_execute($stmtMain);
            mysqli_stmt_close($stmtMain);

            // Commit perubahan jika seluruh query berhasil
            mysqli_commit($this->db);
            $this->redirect("../../admin/rule.php?status=sukses_hapus");

        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan query
            mysqli_rollback($this->db);
            $this->redirect("../../admin/rule.php?status=gagal_hapus");
        }
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

    $controller = new RuleDeleteController($db);
    $controller->delete($_GET['id']);
} else {
    header("Location: ../../admin/gejala.php");
    exit();
}