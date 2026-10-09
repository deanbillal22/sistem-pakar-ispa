<?php

class Database {
    private $host     = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "expert_system_ispa";
    public $conn;

    // Method constructor untuk otomatis membuat koneksi saat object dipanggil
    public function __construct() {
        $this->connectDB();
    }

    // Method untuk koneksi ke database
    private function connectDB() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        // Periksa error pada koneksi
        if ($this->conn->connect_error) {
            die("Koneksi Database Gagal : " . $this->conn->connect_error);
        }
    }

    // Method untuk mendapatkan objek koneksi
    public function getConnection() {
        return $this->conn;
    }
}

// Inisialisasi object dari class Database
$db = new Database();
$conn = $db->getConnection();

?>