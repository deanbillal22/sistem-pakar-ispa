<?php
session_start();

require_once '../../config/koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../PHPMailer/Exception.php';
require_once '../../PHPMailer/PHPMailer.php';
require_once '../../PHPMailer/SMTP.php';

/**
 * Class ForgotPasswordController
 * Untuk token reset password dengan PHPMailer
 */
class ForgotPasswordController
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Eksekusi alur utama permintaan lupa password
     */
    public function handleRequest($postData)
    {
        $emailRaw = trim($postData['email'] ?? '');

        // 1. Validasi Input Email
        if (empty($emailRaw)) {
            $this->showAlert("Alamat email wajib diisi!", "../../user/lupa_password.php");
        }

        // 2. Cek Keberadaan Email di Database
        if (!$this->isEmailRegistered($emailRaw)) {
            $this->showAlert("Email tidak terdaftar dalam sistem!", "../../user/lupa_password.php");
        }

        // 3. Generate Token & Expiration (1 Jam)
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 4. Update Token ke Database
        $isSaved = $this->saveResetToken($emailRaw, $token, $expiresAt);

        if (!$isSaved) {
            $this->showAlert("Gagal memproses permintaan reset password!", "../../user/lupa_password.php");
        }

        // 5. Kirim Email melalui PHPMailer
        $this->sendResetEmail($emailRaw, $token);
    }

    /**
     * Memeriksa apakah email terdaftar menggunakan Prepared Statement
     */
    private function isEmailRegistered($email)
    {
        $stmt = mysqli_prepare($this->db, "SELECT id_user FROM tb_user WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    /**
     * Menyimpan token reset password ke database
     */
    private function saveResetToken($email, $token, $expiresAt)
    {
        $stmt = mysqli_prepare(
            $this->db,
            "UPDATE tb_user SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?"
        );
        mysqli_stmt_bind_param($stmt, "sss", $token, $expiresAt, $email);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $success;
    }

    /**
     * Mengkonfigurasi dan mengirim pesan email reset password
     */
    private function sendResetEmail($recipientEmail, $token)
    {
        $mail = new PHPMailer(true);

        try {
            // Konfigurasi Server SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply.sistempakarispa@gmail.com';
            $mail->Password   = 'hqlcrssmqyrhzacp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengaturan SSL Localhost
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            // Pengirim & Penerima
            $mail->setFrom('noreply.sistempakarispa@gmail.com', 'Sistem Pakar ISPA');
            $mail->addAddress($recipientEmail);

            // Konten Email
            $linkReset = "http://localhost/sistem_pakar_ispa/user/reset_password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password - Sistem Pakar ISPA';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #eaeaea; border-radius: 10px; overflow: hidden;'>
                    <div style='background-color: #1E5E20; color: #ffffff; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0; font-size: 20px;'>Sistem Pakar ISPA</h2>
                    </div>
                    <div style='padding: 25px; color: #333333;'>
                        <h3 style='color: #1E5E20; margin-top: 0;'>Permintaan Reset Password</h3>
                        <p>Halo,</p>
                        <p>Kami menerima permintaan untuk mereset password akun Anda di Sistem Pakar ISPA Balita.</p>
                        <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>
                        <p style='text-align: center; margin: 25px 0;'>
                            <a href='{$linkReset}' style='background-color: #2E7D32; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password Saya</a>
                        </p>
                        <p style='font-size: 13px; color: #777777;'>* Link ini hanya berlaku selama 1 jam.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #999999; margin: 0;'>Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.</p>
                    </div>
                </div>
            ";

            $mail->send();
            $this->showAlert(
                "Link reset password berhasil dikirim! Silakan cek Inbox atau Spam di email Anda.",
                "../../login.php"
            );
        } catch (Exception $e) {
            $this->showAlert(
                "Gagal mengirim email. Error: {$mail->ErrorInfo}",
                "../../user/lupa_password.php"
            );
        }
    }

    /**
     * Helper JavaScript Alert & Redirect
     */
    private function showAlert($message, $redirectUrl)
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

// Ambil koneksi database prosedural ($conn atau $koneksi)
$db = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

if (!$db) {
    die("Koneksi database gagal!");
}

// Gunakan nama class yang benar: ForgotPasswordController
// Dan teruskan $_POST agar email yang diinput terbaca oleh controller
$controller = new ForgotPasswordController($db); 
$controller->handleRequest($_POST);