<?php

namespace Vendimia\Logger;

use Psr\Log\{
    LoggerInterface,
    InvalidArgumentException as PsrInvalidArgumentException
};

use InvalidArgumentException;
use Stringable;
use ArrayAccess;

/**
 * Manages logging actions
 */
class Logger implements LoggerInterface, ArrayAccess
{
    /** Message targets by priority */
    private array $priority_target = [];

    /** This logger name */
    private string $name = 'default';

    /** Logger list */
    private array $logger_list = [];

    /** Message prefix */
    private string $prefix = '';

    /** Preloaded message context, merged with $context argument */
    private array $context = [];

    public function __construct($name = 'default')
    {
        $this->name = $name;
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Adds a log registry at a given log level.
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!key_exists($level, LogLevel::PRIORITY)) {
            throw new PsrInvalidArgumentException("Log level '$level' unknow");
        }

        // $context es fusionado con $this->context
        $context = [
            ...$this->context,
            ...$context,
        ];

        $priority = LogLevel::PRIORITY[$level];

        // Escaneamos todos los niveles de prioridad, desde el mas bajo (alta
        // prioridad) hasta el mas alto (baja prioridad)
        $priorities = array_keys($this->priority_target);
        sort($priorities);

        foreach ($priorities as $target_priority) {
            // Solo procesamos si la prioridad del mensaje es menor o igual
            // a la propiedad del target analizado
            if ($priority > $target_priority) {
                continue;
            }
            foreach ($this->priority_target[$target_priority] as $target) {
                $target->setMetadata(
                    logger_name: $this->name,
                    loglevel: $level,
                    priority: $priority,
                );
                $target->write($this->prefix . $message, $context);
                if (!$target->getBubbling()) {
                    break 2;
                }
            }
        }
    }

    /**
     * Sets a message prefix for this logger
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Preloads $context with some arbitrary data
     */
    public function preloadContext(...$context)
    {
        $this->context = $context;
    }

    /**
     * Creates a new logger
     */
    public function createLogger(string $name)
    {
        return $this->logger_list[$name] = new self($name);
    }

    /**
     * Returns an already created logger
     */
    public function getLogger(string $name)
    {
        if (!key_exists($name, $this->logger_list)) {
            throw new InvalidArgumentException("Logger '{$name}' doesn't exists.");
        }

        return $this->logger_list[$name];
    }

    /**
     * Syntax sugar for self::getLogger()
     */
    public function __invoke(string $name)
    {
        return $this->getLogger($name);
    }

    /**
     * Adds a target to this logger.
     */
    public function addTarget(Target\TargetInterface $target, $level = LogLevel::DEBUG)
    {
        $priority = LogLevel::PRIORITY[$level];

        if (!key_exists($priority, $this->priority_target)) {
            $this->priority_target[$priority] = [];
        }
        $this->priority_target[$priority][] = $target;

        return $this;
    }

    /**
     * ArrayAccess implementation: returns if a logger exists
     */
    public function offsetExists(mixed $logger_name): bool
    {
        return key_exists($logger_name, $this->$logger_list);
    }

    /**
     * ArrayAccess implementation: returns a logger
     */
    public function &offsetGet(mixed $logger_name): mixed
    {
        return $this->logger_list[$logger_name];
    }

    /**
     * ArrayAccess implementation: Updates a logger
     */
    public function offsetSet(mixed $logger_name, mixed $logger): void
    {
        $this->logger_list[$logger_name] = $logger;
    }

    /**
     * ArrayAccess implementation: Removes a logger
     */
    public function offsetUnset(mixed $logger_name): void
    {
        unset($this->logger_list[$logger_name]);
    }
}
