<?php
namespace Vendimia\Logger\Target;

use Vendimia\Logger;
use Stringable;

/**
 * Writes the log to a PHP Stream, like a file or stdout.
 */
class Stream extends TargetAbstract implements TargetInterface
{
    private $stream;
    private $mode;

    public function __construct($stream, $mode = 'a')
    {
        $this->formatter = new Logger\Formatter\OneLiner($this);
        $this->formatter->setOptions(
            date_format: 'Y-m-d H:i:s'
        );

        $this->stream = $stream;
        $this->mode = $mode;
    }

    public function write(string|Stringable $message, array $context = [])
    {
        $f = fopen($this->stream, $this->mode);
        fwrite($f, $this->formatter->format($message, $context) . PHP_EOL);
        fclose($f);
    }
}
