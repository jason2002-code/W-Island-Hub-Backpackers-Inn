<?php
/**
 * EMAIL SERVICE HELPER
 * Sends HTML/text emails using PHP mail() if SMTP/sendmail is configured,
 * or safely falls back to local logging when running in local development (XAMPP).
 */

class MailHelper {
    /**
     * Send email with HTML support
     */
    public static function send($to, $subject, $htmlContent, $textContent = '') {
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: W Island Hub Backpackers Inn <wislandhub2025@gmail.com>";
        $headers[] = "Reply-To: wislandhub2025@gmail.com";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        $headerStr = implode("\r\n", $headers);

        // Attempt sending via standard PHP mail()
        $sent = false;
        try {
            // Suppress error if mail server is not configured in local environment
            $sent = @mail($to, $subject, $htmlContent, $headerStr);
        } catch (Throwable $t) {
            $sent = false;
        }

        // Always log outgoing email for debug / offline testing in local XAMPP
        self::logEmail($to, $subject, $htmlContent, $sent);

        return $sent;
    }

    /**
     * Logs email into storage for development inspection
     */
    private static function logEmail($to, $subject, $htmlContent, $sent) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mail.log';
        $entry = "[" . date('Y-m-d H:i:s') . "] TO: {$to} | SUBJECT: {$subject} | STATUS: " . ($sent ? 'SENT' : 'MAIL_FAILED_OR_LOCAL_FALLBACK') . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
