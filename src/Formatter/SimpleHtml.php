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
     * Formats the array passes as $context into HTML
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

            $html .= '<td style="padding: 5px; border-bottom: 1px solid #eee">' . $value . '</td>';
            $html .= '</tr>';
        }
        $html .= "</table>";

        return $html;
    }

    public function formatThrowable(Throwable $throwable)
    {
        $t_class = get_class($throwable);
        $t_description = $throwable->getMessage();
        $t_file = $throwable->getFile();
        $t_line = $throwable->getLine();
        $t_trace = $throwable->getTrace();

        $html = "<p>An unhandled <strong>{$t_class}</strong> exception has occurred:</p>\n\n";
        $html .= "<p><strong>{$t_description}</strong></p>\n\n";
        $html .= "<p>On file <strong>{$t_file}</strong>:{$t_line}</p>\n\n";
        $html .= "<h2>Stack trace</h2>\n\n";
        $html .= "<ol>";

        foreach ($t_trace as $t) {
            if (!isset($t['file'])) {
                $args = join(', ', $t['args'] ?? []);
                $html .= "<li><tt>{$t['class']}{$t['type']}{$t['function']}({$args})</tt></li>\n";
            } else {
                $html .= "<li><tt>{$t['file']}:{$t['line']}</tt></li>\n";
            }
        }

        $html .= "</ol>";

        return $html;
    }

    public function format(string|Stringable $message, array $context = []): string
    {
        $message = $this->interpolatePlaceholders($message, $context);

        // Las excecpiones las tratamos distinto.
        if (key_exists('exception', $context) &&
            $context['exception'] instanceof Throwable) {

            return $this->formatThrowable($context['exception']);
        } else {
            $html = "<h1>" . htmlentities($message) . "</h1>";
        }

        $html .= $this->formatContext($context);

        // Si hay una excepción en el context, la añadimos
        if ($context['exception'] ?? false
            && $context['exception'] instanceof Throwable) {

            $html .= <<<EOF
            <h2>Exception</h2>

            {$this->formatThrowable($context['exception'])}
            EOF;
        }

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
