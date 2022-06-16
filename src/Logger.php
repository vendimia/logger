<?php
namespace Vendimia\Logger;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Stringable;

/**
 * Manages logging actions
 */
class Logger implements LoggerInterface
{
    /** Message targets */
    private $target = [];

    /** This logger name */
    private $name = 'default';

    /** Logger list */
    private $logger_list = [];

    public function __construct($name = 'default')
    {
        $this->name = $name;
    }

    public function emergency(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $extra);
    }

    public function alert(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::ALERT, $message, $context, $extra);
    }

    public function critical(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $extra);
    }

    public function error(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::ERROR, $message, $context, $extra);
    }

    public function warning(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::WARNING, $message, $context, $extra);
    }

    public function notice(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::NOTICE, $message, $context, $extra);
    }

    public function info(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::INFO, $message, $context, $extra);
    }

    public function debug(
        string|Stringable $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $this->log(LogLevel::DEBUG, $message, $context, $extra);
    }

    /**
     * Adds a log registry at a given log level.
     */
    public function log(
        $level, $message,
        array $context = [],
        array $extra = []
    ): void
    {
        $priority = LogLevel::PRIORITY[$level];
        foreach ($this->target as $target) {
            [$target_object, $target_priority] = $target;

            if ($priority <= $target_priority) {
                $target_object->getFormatter()->setMetadata(
                    logger_name: $this->name,
                    loglevel: $level,
                );
                $target_object->write($message, $context, $extra);
            }
        }
    }

    /**
     * Creates a new logger, accesible
     */
    public function newLogger(string $name)
    {
        $this->logger_list[$name] = new self($name);
    }

    /**
     * Returns a logger
     */
    public function getLogger(string $name)
    {
        if (!key_exists($name, $this->logger_list)) {
            throw new InvalidArgumentException("Logger '{$name}' doesn't exists.");
        }
    }

    /**
     * Syntax sugar for self::getLogger()
     */
    public function __invoke(string $name, array $arguments)
    {
        return $this->getLogger($name);
    }

    /**
     * Adds a target to this logger.
     */
    public function addTarget(Target\TargetInterface $target, $level = LogLevel::DEBUG)
    {
        $this->target[] = [$target, LogLevel::PRIORITY[$level]];

        return $this;
    }
}
