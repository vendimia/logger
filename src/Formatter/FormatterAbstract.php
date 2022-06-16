<?php

namespace Vendimia\Logger\Formatter;

use Vendimia\Logger\Target\TargetInterface;
use InvalidArgumentException;
use Stringable;

abstract class FormatterAbstract implements FormatterInterface
{
    protected $options = [];

    protected $metadata = [];

    public function __construct(
        protected ?TargetInterface $target = null,
    )
    {

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

    /**
     * Adds known metadata information to this formatter, like the loglevel
     */
    public function setMetadata(...$metadata): void
    {
        $this->metadata = array_merge($this->metadata, $metadata);
    }

    /**
     * Replace $context values in $message placeholders
     */
    public function interpolateContext(string|Stringable $message, array $context = []): string
    {
        $replace = [];

        foreach ($context as $key => $value) {
            // Solo reemplazamos valores stringables
            if (is_string($key) || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string)$value;
            }
        }

        return strtr($message, $replace);
    }
}