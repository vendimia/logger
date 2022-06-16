<?php
namespace Vendimia\Logger\Formatter;

interface FormatterInterface
{
    /**
     * Format $message interpolating $context values into placeholders.
     *
     * $extra array, if not empty, may be added to the result.
     */
    public function format(string|Stringable $message, array $context = [], array $extra = []): string;
}
