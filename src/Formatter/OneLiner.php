<?php

namespace Vendimia\Logger\Formatter;
use Stringable;

/**
 * Writes the message and the context in one line, with optional date/time.
 *
 * $context array is only used for replacing placeholders.
 */
class OneLiner extends FormatterAbstract implements FormatterInterface
{
    protected $options = [
        // Other than null, adds date and time before the message, using this format
        'date_format' => null,

        // Prefix the line with [LOGLEVEL]
        'show_loglevel' => true,

        // Prefix the line with <PRIORITY>
        'show_priority' => false,
    ];

    public function format(string|Stringable $message, array $context = []): string
    {
        $message = $this->interpolatePlaceholders($message, $context);

        $parts = [];

        if ($this->options['date_format']) {
            $parts[] = date($this->options['date_format']);
        }

        if ($this->options['show_loglevel']) {
            $parts[] = '[' . strtoupper($this->getMetadata('loglevel')) . ']';
        }

        $parts[] = $message;

        // Si hay un null, lo removemso
        $parts = array_filter($parts);

        $line = join(' ', $parts);

        if ($this->options['show_priority']) {
            $line = '<' . $this->getMetadata('priority') . '>' . $line;
        }

        return $line;
    }
}
