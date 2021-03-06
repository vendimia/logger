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
    private $prefix;

    protected $options = [
        // Other than null, adds date and time before the message, using this format
        'date_format' => null,
        'show_loglevel' => true,
    ];

    /**
     * OneLiner doesn't require string escaping
     */
    public function escape(string $string): string
    {
        return $string;
    }

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

        return join (' ', $parts);
    }
}
