<?php

namespace Vendimia\Logger\Formatter;

use Throwable;
use Stringable;

use Vendimia\ObjectManager\ObjectManager;
use Vendimia\Http\Request;
use Vendimia\Routing\MatchedRoute;

/**
 * Generates a simple HTML with $extra as a table
 */
class SimpleHtml extends FormatterAbstract implements FormatterInterface
{
    protected $options = [
        'show_loglevel' => true,

        // Requires packages vendimia\object-manager and vendimia\http
        'show_request' => false,

        // Requires packages vendimia\object-manager and vendimia\routing
        'show_matched_rule' => false,
    ];

    private $max_depth = 10;

    /**
     * Process and return method arguments from a trace
     */
    protected static function processTraceArgs($args, $separator = ', '): string
    {
        // Si no es iterable, lo retornamos de vuelta
        if (!is_iterable($args)) {
            return (string)$args;
        }

        $result = [];
        foreach ($args as $param => $arg) {
            $processed_arg = '';
            if (is_string($param)) {
                $processed_arg = "{$param}: ";
            }
            if (is_null($arg)) {
                $processed_arg .= 'NULL';
            } elseif (is_array($arg)) {
                $processed_arg .= '[' . self::processTraceArgs($arg) . ']';
            } elseif (is_object($arg)) {
                $processed_arg .= get_class($arg);// . ' ' . $short_name;
            } elseif (is_string($arg)) {
                $processed_arg .= '"' . $arg . '"';
            } else {
                $processed_arg .= $arg;
            }

            $result[] = $processed_arg;
        }

        return join($separator, $result);
    }

    private function normalize($data, $depth = 0)
    {
        if ($depth > $this->max_depth) {
            return "MAX DEPTH REACHED";
        }

        if (is_array($data)) {
            $newdata = [];
            foreach ($data as $key=>$value)
            {
                $newdata[$key] = $this->normalize($value, $depth + 1);
            }
            return $newdata;
        }
        return $data;
    }

    public function escape(string $string): string
    {
        return htmlspecialchars($string);
    }

    /**
     * Formats the traversable argument $context into HTML
     */
    public function formatContext($context)
    {
        $html = '<table style="border-collapse: collapse">';
        $context = $this->normalize($context);

        foreach ($context as $key=>$value) {
            $html .= '<tr>';
            $html .= '<th style="padding: 5px; border-bottom: 1px solid #eee; vertical-align: top; text-align: left">' . $key . '</th>';

            if (is_array($value)) {
                $value = $this->formatContext($value);
            }
            if (is_object($value)) {
                $prefix = '<span style="background-color: #EE8">[' . get_class($value) . ']</span> ';

                if ($value instanceof Stringable) {
                    $value = $prefix . (string)$value;
                } else {
                    $value = $prefix . $this->formatContext($value);
                }
            }

            $html .= '<td style="padding: 5px; border-bottom: 1px solid #eee">' . $value . '</td>';
            $html .= '</tr>';
        }
        $html .= "</table>";

        return $html;
    }

    public function formatThrowable(Throwable $throwable)
    {
        $html = '';

        $first_exception = true;

        do {
            $t_class = get_class($throwable);
            $t_description = $throwable->getMessage();
            $t_file = $throwable->getFile();
            $t_line = $throwable->getLine();
            $t_trace = $throwable->getTrace();

            if (!$first_exception) {
                $html .= '<p>Previous exception:</p>';
            }

            $html .= "<h2>{$t_class}</h2><h3>{$t_description}</h3>\n\n";

            $html .= $this->formatContext([
                'file' => $t_file,
                'line' => $t_line
            ]);

            $html .= "<h2>Stack trace</h2>\n\n";
            $html .= "<ol>";

            foreach ($t_trace as $t) {
                if (!isset($t['file'])) {
                    $class = $trace['class'] ?? '';
                    $type = $trace['type'] ?? '';

                    $args = htmlentities($this->processTraceArgs($t['args'] ?? []));
                    $html .= "<li><tt>{$class}{$type}{$t['function']}({$args})</tt></li>\n";
                } else {
                    $html .= "<li><tt>{$t['file']}:{$t['line']}</tt></li>\n";
                }
            }

            $html .= "</ol>";

            $first_exception = false;

        } while ($throwable = $throwable->getPrevious());

        return $html;
    }

    public function format(string|Stringable $message, array $context = []): string
    {
        $message = $this->interpolatePlaceholders($message, $context);

        $html = '';

        // Las excecpiones las tratamos distinto.
        if ($context['exception'] ?? false
            && $context['exception'] instanceof Throwable) {

            $html .= $this->formatThrowable($context['exception']);

            unset($context['exception']);
        }

        $html .= "<h2>Context</h2>\n\n";

        $html .= $this->formatContext($context);

        // Si está disponible Vendimia\ObjectManager\ObjectManager, intentamos
        // obtener algunas cosas extras
        $object = null;
        if (class_exists(ObjectManager::class)) {
            $object = ObjectManager::retrieve();
        }

        if ($this->getOption('show_matched_rule')) {
            // Matched route?
            if ($matched_route = $object?->get(MatchedRoute::class)) {

                $html .= <<<EOF
                <h2>Matched route</h2>

                <p><pre>{$matched_route}</pre></p>
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
                <h2>Request</h2>

                {$array}
                EOF;
            }

        }

        return $html;
    }
}
