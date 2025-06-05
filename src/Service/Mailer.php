<?php

namespace Milos\Dentists\Service;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = 'sandbox.smtp.mailtrap.io';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAILTRAP_USERNAME'];
        $this->mailer->Password = $_ENV['MAILTRAP_PASSWORD'];
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port = 587;
    }

    public function send(string $recipientAddress, string $recipientName, string $subject, string $html, string $altBody): void
    {
        $this->mailer->setFrom($_ENV['EMAIL_FROM_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $this->mailer->addAddress($recipientAddress, $recipientName);

        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $this->buildTemplate($html);
        $this->mailer->AltBody = $altBody;

        $this->mailer->send();
    }

    private function buildTemplate(string $html): string
    {
        ob_start();
        include ROOT_PATH . '/src/View/template/email_template.php';
        $template = ob_get_clean();

        return str_replace('{{ CONTENT }}', $html, $template);
    }
}