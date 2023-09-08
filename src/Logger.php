<?php
namespace Vendimia\Logger;

use Psr\Log\{
    LoggerInterface,
    InvalidArgumentException as PsrInvalidArgumentException
};
use InvalidArgumentException;
use Stringable;

/**
 * Manages logging actions
 */
class Logger implements LoggerInterface
{
    /** Message targets by priority */
    private $priority_target = [];

    /** This logger name */
    private $name = 'default';

    /** Logger list */
    private $logger_list = [];

    /** Message prefix */
    private string $prefix = '';

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
}
