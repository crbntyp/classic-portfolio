<?php
/**
 * Brevo (Sendinblue) Email Service
 * Handles sending transactional emails via Brevo API
 *
 * Setup:
 * 1. Sign up at https://brevo.com
 * 2. Get your API key from Settings > SMTP & API
 * 3. Add your API key to config.php
 */

class EmailService {
    private $apiKey;
    private $apiUrl = 'https://api.brevo.com/v3/smtp/email';
    private $fromEmail;
    private $fromName;

    public function __construct() {
        // Load API key from config
        if (file_exists(__DIR__ . '/email-config.php')) {
            require_once __DIR__ . '/email-config.php';
            $this->apiKey = BREVO_API_KEY ?? '';
            $this->fromEmail = BREVO_FROM_EMAIL ?? 'noreply@carbontype.co';
            $this->fromName = BREVO_FROM_NAME ?? 'Carbon Type';
        } else {
            throw new Exception('Email configuration file not found. Please create includes/email-config.php');
        }

        if (empty($this->apiKey)) {
            throw new Exception('Brevo API key not configured');
        }
    }

    /**
     * Send an email via Brevo API
     */
    public function send($to, $subject, $htmlContent, $textContent = '') {
        $data = [
            'sender' => [
                'name' => $this->fromName,
                'email' => $this->fromEmail
            ],
            'to' => [
                [
                    'email' => $to
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        if (!empty($textContent)) {
            $data['textContent'] = $textContent;
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $this->apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            $error = json_decode($response, true);
            throw new Exception('Email sending failed: ' . ($error['message'] ?? 'Unknown error'));
        }

        return json_decode($response, true);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($toEmail, $username, $resetToken) {
        $resetUrl = "https://carbontype.co/reset-password.php?token=" . urlencode($resetToken);

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #343434; color: #fff; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { display: inline-block; padding: 12px 30px; background: #0f0; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                </div>
                <div class='content'>
                    <p>Hi {$username},</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #666;'>{$resetUrl}</p>
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Carbon Type. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Hi {$username},\n\n" .
                      "We received a request to reset your password.\n\n" .
                      "Click this link to reset your password:\n{$resetUrl}\n\n" .
                      "This link will expire in 1 hour.\n\n" .
                      "If you didn't request a password reset, you can safely ignore this email.\n\n" .
                      "- Carbon Type Team";

        return $this->send($toEmail, 'Password Reset Request', $htmlContent, $textContent);
    }

    /**
     * Send welcome email when account is created
     */
    public function sendWelcome($toEmail, $username) {
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #343434; color: #fff; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #999; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Carbon Type!</h1>
                </div>
                <div class='content'>
                    <p>Hi {$username},</p>
                    <p>Your account has been created successfully!</p>
                    <p>You can now log in at <a href='https://carbontype.co'>carbontype.co</a></p>
                    <p>If you have any questions, feel free to reach out.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Carbon Type. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Hi {$username},\n\n" .
                      "Your account has been created successfully!\n\n" .
                      "You can now log in at https://carbontype.co\n\n" .
                      "If you have any questions, feel free to reach out.\n\n" .
                      "- Carbon Type Team";

        return $this->send($toEmail, 'Welcome to Carbon Type', $htmlContent, $textContent);
    }
}
