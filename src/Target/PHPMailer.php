<?php
namespace Vendimia\Logger\Target;

use Vendimia\Logger;
use Vendimia\Logger\Formatter;
use PHPMailer\PHPMailer\PHPMailer as PM;
use Stringable;

/**
 * Sends an email using PHPMailer.
 *
 * Require an already PHPMailer object, ready to send an email.
 */
class PHPMailer extends TargetAbstract implements TargetInterface
{
    private $addresses;
    private $mailer;

    protected $options = [
        'show_loglevel' => true,
    ];

    /**
     * @param object $mailer A PHPMailer preconfigured instance.
     */
    public function __construct(PM $mailer)
    {
        $this->mailer = $mailer;
        $this->formatter = new Formatter\SimpleHtml($this);
    }

    public function write(string|Stringable $message, array $context = [])
    {
        $body = $this->formatter->format($message, $context);
        $subject = $this->formatter->interpolatePlaceholders($message, $context);

        if ($this->getOption('show_loglevel')) {
            $subject = '[' . strtoupper($this->getMetadata('loglevel')) . '] '
                . $subject;

        }

        // Creamos el AltBody con Formatter\SimpleText
        $simple_text_formatter = new Formatter\SimpleText($this);

        // $message debe ser un string, siempre.
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;
        $this->mailer->AltBody = $simple_text_formatter->format($message, $context);
        $this->mailer->send();
    }
}
