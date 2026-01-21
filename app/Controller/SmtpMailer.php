<?php

/**
 * SmtpMailer.php
 * Classe simple per enviar correus via SMTP sense necessitat de PHPMailer
 */

class SmtpMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $from_email;
    private $from_name;
    private $socket;
    private $timeout = 10;

    public function __construct($host, $port, $username, $password, $from_email, $from_name) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->from_email = $from_email;
        $this->from_name = $from_name;
    }

    /**
     * Enviar email
     */
    public function send($to, $subject, $html_body, $plain_text = null) {
        try {
            // Connectar al servidor SMTP
            $this->connect();
            
            // Saludar al servidor
            $this->read();
            $this->write("EHLO localhost\r\n");
            $response = $this->read();

            // STARTTLS
            $this->write("STARTTLS\r\n");
            $response = $this->read();
            
            if (strpos($response, '220') === false) {
                throw new Exception('STARTTLS failed: ' . $response);
            }

            // Activar criptografia
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Failed to enable TLS');
            }

            // Saludar de novo després de TLS
            $this->write("EHLO localhost\r\n");
            $response = $this->read();

            // Autenticació
            $this->write("AUTH LOGIN\r\n");
            $response = $this->read();
            
            if (strpos($response, '334') === false) {
                throw new Exception('AUTH LOGIN failed: ' . $response);
            }

            // Enviar usuari codificat en base64
            $this->write(base64_encode($this->username) . "\r\n");
            $response = $this->read();

            // Enviar contrasenya codificada en base64
            $this->write(base64_encode($this->password) . "\r\n");
            $response = $this->read();

            if (strpos($response, '235') === false) {
                throw new Exception('Authentication failed: ' . $response);
            }

            // FROM
            $this->write("MAIL FROM: <" . $this->from_email . ">\r\n");
            $response = $this->read();

            // TO
            $this->write("RCPT TO: <" . $to . ">\r\n");
            $response = $this->read();

            // DATA
            $this->write("DATA\r\n");
            $response = $this->read();

            // Construir el missatge
            $boundary = 'boundary_' . md5(time());
            $message = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
            $message .= "To: " . $to . "\r\n";
            $message .= "Subject: " . $this->encodeSubject($subject) . "\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";
            $message .= "\r\n";
            
            // Part de text pla
            if ($plain_text) {
                $message .= "--" . $boundary . "\r\n";
                $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
                $message .= "Content-Transfer-Encoding: 8bit\r\n";
                $message .= "\r\n";
                $message .= $plain_text . "\r\n";
            }
            
            // Part HTML
            $message .= "--" . $boundary . "\r\n";
            $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n";
            $message .= "\r\n";
            $message .= $html_body . "\r\n";
            
            $message .= "--" . $boundary . "--\r\n";

            // Enviar el missatge
            $this->write($message . "\r\n.\r\n");
            $response = $this->read();

            if (strpos($response, '250') === false) {
                throw new Exception('Failed to send message: ' . $response);
            }

            // QUIT
            $this->write("QUIT\r\n");
            $this->read();

            // Tancar la connexió
            $this->disconnect();

            return true;

        } catch (Exception $e) {
            error_log('SmtpMailer Error: ' . $e->getMessage());
            $this->disconnect();
            throw $e;
        }
    }

    /**
     * Connectar al servidor SMTP
     */
    private function connect() {
        $this->socket = stream_socket_client(
            "tcp://" . $this->host . ":" . $this->port,
            $errno,
            $errstr,
            $this->timeout
        );

        if (!$this->socket) {
            throw new Exception("Connection failed: $errstr ($errno)");
        }

        stream_set_timeout($this->socket, $this->timeout);
    }

    /**
     * Tancar la connexió
     */
    private function disconnect() {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    /**
     * Llegir resposta del servidor
     */
    private function read() {
        $response = '';
        while (true) {
            $line = fgets($this->socket, 1024);
            if (!$line) break;
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }

    /**
     * Enviar comanda al servidor
     */
    private function write($data) {
        fwrite($this->socket, $data);
    }

    /**
     * Codificar assumpte per a headers de email
     */
    private function encodeSubject($subject) {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
}

?>
