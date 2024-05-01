<?php

namespace Vendimia\Logger\Formatter;

use Throwable;
use Stringable;

use Vendimia\ObjectManager\ObjectManager;
use Vendimia\Http\Request;
use Vendimia\Routing\MatchedRoute;
use Vendimia\Exception\VendimiaException;

/**
 * Helper class for generating a text version of SimpleHtml class
 */
class SimpleText extends SimpleHtml implements FormatterInterface
{
    /**
     * Formats the traversable argument $context into HTML
     */
    public function formatContext($context, $indent = 0): string
    {
        $text = '';

        $max_key_length = 0;
        foreach ($context as $key => $value) {
            $length = mb_strlen($key);
            if ($length > $max_key_length) {
                $max_key_length = $length;
            }
        }

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $value = "[array]\n" . $this->formatContext($value, $indent + 1);
            } elseif (is_object($value)) {
                $prefix = "[object: " . get_class($value) . "]";
                if ($value instanceof Stringable) {
                    $value = $prefix . (string)$value;
                } else {
                    $value = $prefix . "\n" . $this->formatContext($value);
                }
            }

            $text .= str_repeat(" ", $indent)
                . $key
                . str_repeat(" ", $max_key_length - mb_strlen($key))
                . ': ' . $value . "\n";
        }

        return $text;
    }

    public function formatThrowable(Throwable $throwable)
    {
        $t_class = get_class($throwable);
        $t_description = $throwable->getMessage();
        $t_file = $throwable->getFile();
        $t_line = $throwable->getLine();
        $t_trace = $throwable->getTrace();

        $text = <<<EOF
        $t_description

        Unhandled $t_class exception on
        $t_file:$t_line

        Stack trace
        ===========

        EOF;

        $id = 1;
        foreach ($t_trace as $t) {
            if (!isset($t['file'])) {
                $class = $trace['class'] ?? '';
                $type = $trace['type'] ?? '';

                $args = htmlentities($this->processTraceArgs($t['args'] ?? []));

                $trace_line = "{$class}{$type}{$t['function']}({$args})";
            } else {
                $trace_line = "{$t['file']}:{$t['line']}";
            }

            $text .= str_pad($id, 3, ' ', STR_PAD_LEFT);
            $text .= ". {$trace_line}\n";

            $id++;
        }
        $text .= "\n";

        // VendimiaException puede tener más info
        if ($throwable instanceof VendimiaException) {
            $text .= "Extra information\n=================\n\n";
            $text .=  $this->formatContext($throwable->getExtra());

        }

        return $text . "\n";
    }

    public function format(string|Stringable $message, array $context = []): string
    {
        $message = $this->interpolatePlaceholders($message, $context);

        $text = '';

        // Las excecpiones las tratamos distinto.
        if ($context['exception'] ?? false
            && $context['exception'] instanceof Throwable) {

            $text .= $this->formatThrowable($context['exception']);

            unset($context['exception']);
        }

        $text .= "Context\n=======\n\n";

        $text .= $this->formatContext($context);

        // Si está disponible Vendimia\ObjectManager\ObjectManager, intentamos
        // obtener algunas cosas extras
        $object = null;
        if (class_exists(ObjectManager::class)) {
            $object = ObjectManager::retrieve();

            if ($this->getOption('show_matched_rule')) {
                // Matched route?
                if ($matched_route = $object?->get(MatchedRoute::class)) {

                    $html .= <<<EOF
                    Matched route
                    =============

                    {$matched_route}
                    EOF;
                }
            }


            if ($this->getOption('show_request')) {
                // Request?
                if ($request = $object?->get(Request::class)) {
                    $data = [
                        'Request URI' => $request->getUri() ?? '',
                        'HTTP Method' => $request->getMethod(),
                        'Query parameters' => $request->query_params->asArray(),
                        'Parsed body' => $request->parsed_body->asArray(),
                        'Server parameters' => $request->getServerParams(),
                    ];

                    $array = $this->formatContext($data);

                    $html .= <<<EOF
                    HTTP Request
                    ============

                    {$array}
                    EOF;
                }

            }
        }

        return trim($text);
    }
}