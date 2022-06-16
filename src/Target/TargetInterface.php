<?php

namespace Vendimia\Logger\Target;

use Stringable;

interface TargetInterface
{
    /**
     * Write a log message to this target.
     *
     * This method should format first the message using a
     * Vendimia\Logger\Formatter instance.
     */
    public function write(string|Stringable $message, array $context = [], array $extra = []);
}
