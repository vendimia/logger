<?php

namespace Vendimia\Logger\Formatter;
use Stringable;

interface FormatterInterface
{
    /**
     * Escapes the string as needed by the formatter
     */
    public function escape(string $string): string;

    /**
     * Format $message interpolating $context values into placeholders.
     *
     * $extra array, if not empty, may be added to the result.
     */
    public function format(string|Stringable $message, array $context = []): string;
}
