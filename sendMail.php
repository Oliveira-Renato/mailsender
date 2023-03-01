<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';

class EmailSender {
    private $mailer;
    private $config;

    public function __construct($config) {
        $this->mailer = new PHPMailer(true);
        $this->mailer->setLanguage('br');
        $this->mailer->CharSet='UTF-8';     
        $this->config = $config;
    }

    public function sendEmail($to, $subject, $body) {
        // Validate input
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        if (strlen($subject) > 255) {
            throw new Exception('Subject too long');
        }

        // Load email content from template
        $loader = new \Twig\Loader\FilesystemLoader('./templates');
        $twig = new \Twig\Environment($loader);
        $content = $twig->render('email_template.html', ['body' => $body]);

        // Set up email
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp_host'];
        $this->mailer->SMTPAuth = true;
        //$this->mailer->SMTPDebug = 2;
        $this->mailer->Username = $this->config['smtp_username'];
        $this->mailer->Password = $this->config['smtp_password'];
        $this->mailer->SMTPSecure = $this->config['smtp_secure'];
        $this->mailer->Port = $this->config['smtp_port'];

        $this->mailer->setFrom($this->config['sender_email'], $this->config['sender_name']);
        $this->mailer->addAddress($to);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $content;

        try {
            // Send email
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new Exception('Email could not be sent. ' . $this->mailer->ErrorInfo);
        }
    }
}

// Load configuration from file
$config = parse_ini_file('config.ini');

// Send email
$emailSender = new EmailSender($config);
try {
    $emailSender->sendEmail('send_email_here', 'Test email', 'Hello world!');
    echo 'Email sent successfully';
} catch (Exception $e) {
    echo 'Error sending email: ' . $e->getMessage();
}
?>
