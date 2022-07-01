<?php

namespace Vendimia\Logger\Formatter;

use Vendimia\Logger\Target\TargetInterface;
use InvalidArgumentException;
use Throwable;
use Stringable;

abstract class FormatterAbstract implements FormatterInterface
{
    protected $options = [];

    public function __construct(
        private TargetInterface $target,
    )
    {

    }

    /**
     * Gets metadata from $target
     */
    public function getMetadata($key)
    {
        return $this->target->getMetadata($key);
    }

    /**
     * Sets this formatter options.
     *
     * Only can be set already existing options.
     */
    public function setOptions(...$options): void
    {
        foreach ($options as $option => $value) {
            if (!key_exists($option, $this->options)) {
                throw new InvalidArgumentException("Option '{$option}' invalid in " . get_class($this) . ' formatter');
            }
            $this->options[$option] = $value;
        }
    }

    public function getOption($option)
    {
        return $this->options[$option];
    }

    /**
     * Replace $context values in $message placeholders
     */
    public function interpolatePlaceholders(string|Stringable $message, array $context = []): string
    {
        $replace = [];

        foreach ($context as $key => $value) {

            // Para throwables, solo usamos el mensaje de la excepción
            if ($value instanceof Throwable) {
                $value = get_class($value) . ': ' .  $value->getMessage();
            }

            // Solo reemplazamos valores stringables
            if (is_numeric($value) || is_string($value) || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string)$value;
            }
        }

        return strtr($message, $replace);
    }
}