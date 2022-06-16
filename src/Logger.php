<?php
namespace Vendimia\Logger;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;

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

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Adds a log registry at a given log level.
     */
    public function log($level, $message, array $context = [], array $extra = [])
    {
        // Añadimos el nombre de este logger al $extra
        $extra['logger.name'] = $this->name;
        $extra['logger.level'] = $level;

        $priority = LogLevel::PRIORITY[$level];
        foreach ($this->target as $target) {
            list($target_object, $target_priority) = $target;

            if ($priority <= $target_priority) {
                $target_object->write($message, $context);
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
