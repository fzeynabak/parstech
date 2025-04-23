<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class Mailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // تنظیمات سرور
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USER;
            $this->mailer->Password = SMTP_PASS;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            
            // تنظیمات عمومی
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            
            // تنظیمات دیباگ در محیط توسعه
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            }
        } catch (Exception $e) {
            error_log("خطا در تنظیم میلر: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function sendMail($to, $toName, $subject, $body) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->getEmailTemplate($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("خطا در ارسال ایمیل: " . $e->getMessage());
            return false;
        }
    }
    
    private function getEmailTemplate($content) {
        return "
            <!DOCTYPE html>
            <html lang='fa' dir='rtl'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>
                    body {
                        font-family: Tahoma, Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .header {
                        background: #f8f9fa;
                        padding: 20px;
                        text-align: center;
                        border-radius: 5px 5px 0 0;
                    }
                    .content {
                        background: #fff;
                        padding: 20px;
                        border-radius: 0 0 5px 5px;
                    }
                    .footer {
                        text-align: center;
                        padding: 20px;
                        font-size: 12px;
                        color: #666;
                    }
                    .btn {
                        display: inline-block;
                        padding: 10px 20px;
                        background: #007bff;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 5px;
                        margin: 10px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <img src='" . BASE_URL . "/assets/images/logo.png' alt='پارس تک' height='50'>
                    </div>
                    <div class='content'>
                        $content
                    </div>
                    <div class='footer'>
                        <p>این ایمیل به صورت خودکار ارسال شده است. لطفاً به آن پاسخ ندهید.</p>
                        <p>&copy; " . date('Y') . " پارس تک. تمامی حقوق محفوظ است.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
?>