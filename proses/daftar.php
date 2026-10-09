<?php
session_start();

// Panggil file koneksi 
class DatabaseLoader {
    public static function getConnection() {
        $paths = [
            '../config/koneksi.php',
            '../koneksi.php',
            '../config/database.php'
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return $conn ?? $koneksi ?? null;
            }
        }
        return null;
    }
}

class RegisterController {
    private $db;
    private $tableName = "tb_user";

    public function __construct($connection) {
        $this->db = $connection;
    }

    // Method Utama Pendaftaran User
    public function register($data) {
        $namaLengkap        = trim($data['nama_lengkap'] ?? '');
        $email              = trim($data['email'] ?? '');
        $username           = trim($data['username'] ?? '');
        $password           = $data['password'] ?? '';
        $konfirmasiPassword = $data['konfirmasi_password'] ?? '';

        // Validasi 1: Data Kosong
        if (empty($namaLengkap) || empty($email) || empty($username) || empty($password) || empty($konfirmasiPassword)) {
            $this->redirectWithError("Semua field wajib diisi!");
        }

        // Validasi 2: Kesamaan Password
        if ($password !== $konfirmasiPassword) {
            $this->redirectWithError("Konfirmasi password tidak cocok!");
        }

        // Validasi 3: Cek Koneksi DB
        if (!$this->db) {
            $this->redirectWithError("Koneksi ke database gagal. Periksa kembali file koneksi Anda.");
        }

        // Validasi 4: Duplikasi Username / Email
        if ($this->isUserExist($username, $email)) {
            $this->redirectWithError("Username atau Email sudah terdaftar! Gunakan yang lain.");
        }

        // Proses Simpan Data User
        if ($this->saveUser($namaLengkap, $username, $email, $password)) {
            $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: ../login.php");
            exit();
        } else {
            $this->redirectWithError("Gagal menyimpan data ke database.");
        }
    }

    // Encapsulation: Cek Keberadaan User (Prepared Statement)
    private function isUserExist($username, $email) {
        $query = "SELECT id_user FROM {$this->tableName} WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
            return $exists;
        }
        return false;
    }

    // Encapsulation: Insert Data User Baru
    private function saveUser($namaLengkap, $username, $email, $password) {
        $passwordHashed = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user';
        $foto = 'default.png';

        $query = "INSERT INTO {$this->tableName} (nama_lengkap, username, email, password, foto, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);

        if ($stmt) {
            $stmt->bind_param("ssssss", $namaLengkap, $username, $email, $passwordHashed, $foto, $role);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
        return false;
    }

    // Helper: Redirect dengan pesan Error
    private function redirectWithError($message) {
        $_SESSION['error'] = $message;
        header("Location: ../register.php");
        exit();
    }
}

// Eksekusi Pendaftaran jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = DatabaseLoader::getConnection();
    $registerController = new RegisterController($db);
    $registerController->register($_POST);
} else {
    header("Location: ../register.php");
    exit();
}
?>