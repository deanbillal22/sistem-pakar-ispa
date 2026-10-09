<?php
session_start();

// Panggil file koneksi (menggunakan class Database yang telah kita buat)
require_once '../config/koneksi.php';

class AuthController {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    // Method utama untuk memproses autentikasi pengguna
    public function login($usernameInput, $passwordInput) {
        $usernameInput = trim($usernameInput);

        // Validasi input kosong
        if (empty($usernameInput) || empty($passwordInput)) {
            $this->redirectWithError('Username/Email dan Password wajib diisi!');
        }

        // Ambil data user menggunakan Prepared Statement (Sangat Aman & OOP)
        $user = $this->getUserByUsernameOrEmail($usernameInput);

        if ($user) {
            // Verifikasi Password (BCRYPT, MD5, atau Plaintext)
            if ($this->verifyPassword($passwordInput, $user['password'])) {
                $this->createSession($user);
                $this->redirectByRole($user['role'] ?? 'user');
            } else {
                $this->redirectWithError('Password yang Anda masukkan salah!');
            }
        } else {
            $this->redirectWithError('Email atau Username tidak terdaftar!');
        }
    }

    // Encapsulation: Query database khusus mencari user
    private function getUserByUsernameOrEmail($username) {
        $stmt = $this->db->prepare("SELECT * FROM tb_user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Encapsulation: Logika verifikasi password
    private function verifyPassword($inputPassword, $databasePassword) {
        if (password_verify($inputPassword, $databasePassword)) {
            return true;
        } elseif (md5($inputPassword) === $databasePassword) {
            return true;
        } elseif ($inputPassword === $databasePassword) {
            return true;
        }
        return false;
    }

    // Encapsulation: Menyimpan session user
    private function createSession($user) {
        $_SESSION['login']    = true;
        $_SESSION['id_user']  = $user['id_user'];
        $_SESSION['nama']     = $user['nama_lengkap'] ?? $user['username'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role'] ?? 'user';
    }

    // Helper: Pengalihan halaman berdasar role
    private function redirectByRole($role) {
        if ($role === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit;
    }

    // Helper: Pengalihan halaman jika error
    private function redirectWithError($message) {
        $_SESSION['error'] = $message;
        header("Location: ../login.php");
        exit;
    }
}

// Inisialisasi Objek dan Eksekusi Controller jika method REQUEST POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Variabel $conn didapatkan dari file koneksi.php versi OOP
    $auth = new AuthController($conn);
    $auth->login($_POST['username'] ?? '', $_POST['password'] ?? '');
} else {
    header("Location: ../login.php");
    exit;
}
?>