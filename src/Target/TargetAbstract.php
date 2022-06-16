<?php
namespace Vendimia\Logger\Target;

use Vendimia;
use Vendimia\Logger\Formatter\FormatterInterface;

abstract class TargetAbstract implements TargetInterface
{
    protected $formatter = null;

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
