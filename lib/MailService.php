<?php
/**
 * Envoi d’e-mails simples (réinitialisation mot de passe).
 *
 * Utilise la fonction PHP mail() — sur YunoHost, le serveur doit pouvoir envoyer des mails.
 * Expéditeur optionnel : variable d’environnement MONCINE_MAIL_FROM (voir extra_php-fpm.conf).
 */

declare(strict_types=1);

namespace Moncine;

final class MailService
{
    public static function sendPasswordReset(string $toEmail, string $nom, string $resetUrl): bool
    {
        $app = MONCINE_APP_NAME;
        $subject = $app . ' — Réinitialisation de votre mot de passe';
        $body = "Bonjour " . $nom . ",\n\n"
            . "Vous avez demandé à réinitialiser votre mot de passe sur " . $app . ".\n"
            . "Cliquez sur le lien ci-dessous (valable 1 heure, usage unique) :\n\n"
            . $resetUrl . "\n\n"
            . "Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.\n\n"
            . "— " . $app;

        return self::send($toEmail, $subject, $body);
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $from = getenv('MONCINE_MAIL_FROM');
        if (!is_string($from) || $from === '') {
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $from = 'noreply@' . preg_replace('/[^a-z0-9.-]/i', '', $host);
        }

        $headers = [
            'From: ' . $from,
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
        ];

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        if (!$ok) {
            error_log('Moncine mail() failed for ' . $to);
        }

        return $ok;
    }
}
