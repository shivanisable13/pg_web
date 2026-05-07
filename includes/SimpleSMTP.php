<?php
/**
 * SimpleSMTP - A lightweight SMTP client for Gmail
 */
class SimpleSMTP {
    public static function send($to, $subject, $message, $from_email, $from_name, $user, $pass) {
        $host = 'ssl://smtp.gmail.com';
        $port = 465;
        $timeout = 30;

        $socket = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            error_log("SMTP Connection Error: $errstr ($errno)");
            return false;
        }

        $response = fgets($socket, 512);

        // HELO
        fputs($socket, "HELO localhost\r\n");
        $response = fgets($socket, 512);

        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 512);

        fputs($socket, base64_encode($user) . "\r\n");
        $response = fgets($socket, 512);

        fputs($socket, base64_encode($pass) . "\r\n");
        $response = fgets($socket, 512);

        if (substr($response, 0, 3) != '235') {
            error_log("SMTP Auth Error: " . $response);
            return false;
        }

        // MAIL FROM
        fputs($socket, "MAIL FROM: <$from_email>\r\n");
        $response = fgets($socket, 512);

        // RCPT TO
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 512);

        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 512);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Date: " . date('r') . "\r\n";

        fputs($socket, $headers . "\r\n" . $message . "\r\n.\r\n");
        $response = fgets($socket, 512);

        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }
}
?>
