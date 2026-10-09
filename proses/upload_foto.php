<?php
session_start();
require_once "../config/koneksi.php";

/**
 * Class PhotoUploader
 * Untuk proses upload foto
 */
class PhotoUploader
{
    private $db;
    private $targetDir;
    private $maxSize;
    private $allowedExt;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
        $this->targetDir = "../assets/img/";
        $this->maxSize = 500000; // Maksimal 500KB
        $this->allowedExt = ['jpg', 'jpeg', 'png'];
    }

    /**
     * Eksekusi utama unggah foto
     */
    public function upload($idUser, $fileData)
    {
        // 1. Validasi Autentikasi (Cek session login atau id_user)
        if ((!isset($_SESSION['login']) && !isset($_SESSION['id_user'])) || empty($idUser)) {
            header("Location: ../login.php");
            exit;
        }

        // 2. Validasi Keberadaan dan Error Upload
        if (!isset($fileData['foto']) || $fileData['foto']['error'] !== 0) {
            $this->showAlert("Terjadi kesalahan saat upload file!");
        }

        $fileName = $fileData['foto']['name'];
        $fileTmp  = $fileData['foto']['tmp_name'];
        $fileSize = $fileData['foto']['size'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // 3. Validasi Ukuran File
        if ($fileSize > $this->maxSize) {
            $this->showAlert("Ukuran file terlalu besar! Maksimal 500KB.");
        }

        // 4. Validasi Ekstensi File
        if (!in_array($ext, $this->allowedExt)) {
            $this->showAlert("Format file harus JPG, JPEG, atau PNG!");
        }

        // 5. Buat Folder Tujuan jika Belum Ada
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0777, true);
        }

        // 6. Hapus Foto Lama (Jika Ada)
        $this->deleteOldPhoto($idUser);

        // 7. Pindahkan File Baru
        $newName = "user_" . $idUser . "_" . time() . "." . $ext;
        $targetFile = $this->targetDir . $newName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $isUpdated = $this->updateDatabasePhoto($idUser, $newName);

            if ($isUpdated) {
                $this->showAlert("Foto profil berhasil diperbarui!");
            } else {
                $this->showAlert("Gagal memperbarui database!");
            }
        } else {
            $this->showAlert("Gagal memindahkan file ke folder tujuan!");
        }
    }

    /**
     * Menghapus foto profil lama user jika tersimpan di disk
     */
    private function deleteOldPhoto($idUser)
    {
        $stmt = mysqli_prepare($this->db, "SELECT foto FROM tb_user WHERE id_user = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idUser);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $dataOld = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!empty($dataOld['foto']) && file_exists($this->targetDir . $dataOld['foto'])) {
            unlink($this->targetDir . $dataOld['foto']);
        }
    }

    /**
     * Update nama foto baru di database
     */
    private function updateDatabasePhoto($idUser, $newPhotoName)
    {
        $stmt = mysqli_prepare($this->db, "UPDATE tb_user SET foto = ? WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "si", $newPhotoName, $idUser);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Helper untuk menampilkan JavaScript Alert & Redirect
     */
    private function showAlert($message, $redirectUrl = "../user/profil.php")
    {
        echo "<script>
                alert('{$message}');
                window.location.href = '{$redirectUrl}';
              </script>";
        exit;
    }
}

/* ==========================================================
   INITIALIZATION & EXECUTION
========================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menggunakan variabel $conn bawaan koneksi.php
    $uploader = new PhotoUploader($conn);
    $uploader->upload($_SESSION['id_user'] ?? 0, $_FILES);
} else {
    header("Location: ../user/profil.php");
    exit;
}