<?php

namespace Vendimia\Logger\Target;

use Vendimia;
use Vendimia\Logger\Formatter\FormatterInterface;

abstract class TargetAbstract implements TargetInterface
{
    protected $formatter = null;
    protected $options = [];
    protected $metadata = [];

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

    public function getMetadata($metadata)
    {
        return $this->metadata[$metadata];
    }


    /**
     * Sets a formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Returns the formatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

}
