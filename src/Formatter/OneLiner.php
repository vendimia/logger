<?php
namespace Vendimia\Logger\Formatter;

/**
 * Writes the message and the context in one line, with optional date/time
 */
class OneLiner extends FormatterAbstract implements FormatterInterface
{
    private $prefix;

    protected $options = [
        // Other than null, adds date and time before the message, using this format
        'date_format' => null,
        'show_loglevel' => true,
    ];

    public function format(string|Stringable $message, array $context = [], array $extra = []): string
    {
        $message = $this->interpolateContext($message, $context);

        $parts = [];

        if ($this->options['date_format']) {
            $parts[] = date($this->options['date_format']);
        }

        if ($this->options['show_loglevel']) {
            $parts[] = '[' . strtoupper($this->metadata['loglevel']) . ']';
        }

        //$parts[] = $logname;
        $parts[] = $this->prefix . $message;

        if ($extra) {
            $parts[] = '(' . json_encode($extra) . ')';
        }


        // Si hay un null, lo removemso
        $parts = array_filter($parts);

        return join (' ', $parts);
    }
}
