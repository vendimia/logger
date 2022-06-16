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
        $this->formatter->setOptions(show_loglevel: true);

    }

    public function write(
        string|Stringable $message,
        array $context = [],
        array $extra = []
        )
    {
        error_log($this->formatter->format($message, $context, $extra));
    }
}
