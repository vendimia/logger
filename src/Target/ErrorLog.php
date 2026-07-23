<?php
namespace Vendimia\Logger\Target;

use Vendimia\Logger;
use Stringable;

/**
 * Writes the message using PHP method error_log();
 */
class ErrorLog extends TargetAbstract implements TargetInterface
{
    /**
     * Sets the default formatter to OneLiner without date
     */
    public function __construct()
    {
        $this->formatter = new Logger\Formatter\OneLiner($this);
    }

    public function write(string|Stringable $message, array $context = [])
    {
        // Si hay una excepción en context, añadimos más información al mensaje
        if (key_exists('exception', $context)) {
            $exception = $context['exception'];

            $exception_class = get_class($exception);

            // Si hay un mensaje, lo añadimos al final
            $old_message = $message;

            $message = "{$exception_class}: {$exception->getMessage()} on "
                . $exception->getFile() . ':' . $exception->getLine()
            ;

            if ($old_message) {
                $message .= ' - ' . $message;
            }
        }
        error_log($this->formatter->format($message, $context));
    }
}
