<?php

namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.9.1';
    const DEFAULT_PORT = 25;
    const MAX_LINE_LENGTH = 998;
    const MAX_REPLY_TIMEOUT = 300;
    const DEBUG_OFF = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;

    protected $smtp_conn;
    protected $error = [];
    protected $helo_rply;
    protected $server_caps;
    protected $last_reply = '';

    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        $this->error = [];
        if ($this->connected()) {
            return true;
        }
        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }
        $socket_context = stream_context_create($options);
        $errno = 0;
        $errstr = '';
        $this->smtp_conn = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );
        if (!is_resource($this->smtp_conn)) {
            $this->error = [
                'error' => 'Failed to connect to server',
                'detail' => $errstr,
                'errno' => $errno,
            ];
            return false;
        }
        stream_set_timeout($this->smtp_conn, $timeout);
        $this->last_reply = $this->get_lines();
        return true;
    }

    public function connected()
    {
        if (is_resource($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                $this->close();
                return false;
            }
            return true;
        }
        return false;
    }

    public function close()
    {
        $this->error = [];
        $this->server_caps = null;
        $this->helo_rply = null;
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }

    public function authenticate($username, $password, $authtype = null)
    {
        if (!$this->connected()) {
            return false;
        }
        if (!$this->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334)) {
            return false;
        }
        if (!$this->sendCommand('Username', base64_encode($username), 334)) {
            return false;
        }
        if (!$this->sendCommand('Password', base64_encode($password), 235)) {
            return false;
        }
        return true;
    }

    public function sendCommand($commandlabel, $command, $expect)
    {
        if (!$this->connected()) {
            return false;
        }
        fputs($this->smtp_conn, $command . "\r\n");
        $this->last_reply = $this->get_lines();
        $code = (int) substr($this->last_reply, 0, 3);
        if (is_array($expect)) {
            if (!in_array($code, $expect, true)) {
                return false;
            }
        } else {
            if ($code !== $expect) {
                return false;
            }
        }
        return true;
    }

    public function mail($from)
    {
        return $this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $from . '>', 250);
    }

    public function recipient($toaddress)
    {
        return $this->sendCommand('RCPT TO', 'RCPT TO:<' . $toaddress . '>', [250, 251]);
    }

    public function data($msg_data)
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }
        $msg_data = str_replace(["\r\n", "\r"], "\n", $msg_data);
        $lines = explode("\n", $msg_data);
        foreach ($lines as $line) {
            if (strpos($line, '.') === 0) {
                $line = '.' . $line;
            }
            fputs($this->smtp_conn, $line . "\r\n");
        }
        return $this->sendCommand('DATA END', '.', 250);
    }

    public function hello($host = '')
    {
        return $this->sendCommand('EHLO', 'EHLO ' . $host, 250) || $this->sendCommand('HELO', 'HELO ' . $host, 250);
    }

    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }
        return @stream_socket_enable_crypto($this->smtp_conn, true, $crypto_method);
    }

    public function quit()
    {
        $this->sendCommand('QUIT', 'QUIT', 221);
        $this->close();
    }

    protected function get_lines()
    {
        $data = '';
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = @fgets($this->smtp_conn, 515);
            $data .= $str;
            if (isset($str[3]) && $str[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    public function getError()
    {
        return $this->error;
    }
}