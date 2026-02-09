<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class MailerService
{
    public static function sendOtp($email, $otp)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stevegramatica2@gmail.com';
            $mail->Password   = 'mcpmgwefbneexbij'; // 16-digit App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('stevegramatica2@gmail.com', 'Service Portal');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body = "
                <div style='font-family:Arial;padding:20px'>
                    <h2>Email Verification</h2>
                    <p>Your OTP code is:</p>
                    <h1 style='letter-spacing:8px;color:#0d9488'>$otp</h1>
                    <p>This OTP expires in 5 minutes.</p>
                </div>
            ";

            // Enable debugging temporarily
            $mail->SMTPDebug = 2; 
            $mail->Debugoutput = function($str, $level) {
                Log::debug("SMTP Debug: $str");
            };

            $mail->send();
            return true;

        } catch (Exception $e) {
            Log::error('OTP Mail Error: ' . $e->getMessage());
            Log::error('PHPMailer info: ' . $mail->ErrorInfo);
            return false;
        }
    }
}