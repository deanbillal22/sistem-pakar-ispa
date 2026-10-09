<?php

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const CHARSET_UTF8 = 'utf-8';
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority;
    public $CharSet = 'utf-8';
    public $ContentType = 'text/plain';
    public $Encoding = '8bit';
    public $ErrorInfo = '';
    public $From = 'root@localhost';
    public $FromName = 'Root User';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    
    public $Mailer = 'smtp';
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPSecure = '';
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $Timeout = 30;
    public $SMTPOptions = [];

    protected $to = [];
    protected $smtp;

    public function __construct($exceptions = null)
    {
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '')
    {
        $this->From = trim($address);
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = [trim($address), $name];
        return true;
    }

    public function isHTML($ishtml = true)
    {
        if ($ishtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            throw $e;
        }
    }

    protected function preSend()
    {
        if (empty($this->to)) {
            throw new Exception('You must provide at least one recipient email address.');
        }
        return true;
    }

    protected function postSend()
    {
        $this->smtp = new SMTP();
        if (!$this->smtp->connect($this->Host, $this->Port, $this->Timeout, $this->SMTPOptions)) {
            throw new Exception('SMTP connect() failed.');
        }
        if (!$this->smtp->hello('localhost')) {
            throw new Exception('EHLO failed.');
        }
        if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
            if (!$this->smtp->startTLS()) {
                throw new Exception('StartTLS failed.');
            }
            if (!$this->smtp->hello('localhost')) {
                throw new Exception('EHLO failed after StartTLS.');
            }
        }
        if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                throw new Exception('SMTP Authentication failed.');
            }
        }
        if (!$this->smtp->mail($this->From)) {
            throw new Exception('MAIL FROM failed.');
        }
        foreach ($this->to as $toarr) {
            if (!$this->smtp->recipient($toarr[0])) {
                throw new Exception('RCPT TO failed for ' . $toarr[0]);
            }
        }
        $header = "From: {$this->FromName} <{$this->From}>\r\n";
        $header .= "To: " . $this->to[0][0] . "\r\n";
        $header .= "Subject: {$this->Subject}\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n\r\n";

        if (!$this->smtp->data($header . $this->Body)) {
            throw new Exception('DATA command failed.');
        }
        $this->smtp->quit();
        return true;
    }
}