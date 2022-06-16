<?php
namespace Vendimia\Logger;

use Psr;

/**
 * Import loglevels from PSR specification.
 */
class LogLevel extends Psr\Log\LogLevel
{
    /**
     * Level numeric priorities.
     */
    const PRIORITY = [
        self::EMERGENCY => 0,
        self::ALERT => 1,
        self::CRITICAL => 2,
        self::ERROR => 3,
        self::WARNING => 4,
        self::NOTICE => 5,
        self::INFO => 6,
        self::DEBUG => 7,
    ];
}
