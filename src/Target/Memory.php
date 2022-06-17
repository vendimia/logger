<?php
namespace Vendimia\Logger\Target;

use Vendimia\Logger;

/**
 * Saves the log to memory and returns the lines as an array
 */
class Memory extends TargetAbstract implements TargetInterface
{
    private $storage = [];

    public function __construct()
    {
        $this->formatter = new Logger\Formatter\OneLiner($this);
        $this->formatter->setOptions(
            date_format: 'Y-m-d H:i:s'
        );

    }

    public function write(string|Stringable $message, array $context = [])
    {
        $this->storage[] = $this->formatter->format($message, $context, $extra);
    }

    public function getMessages()
    {
        return $this->storage;
    }
}
