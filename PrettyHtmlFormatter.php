<?php

namespace Grav\Plugin;

class PrettyHtmlFormatter extends \Monolog\Formatter\HtmlFormatter
{
    /** @var \Twig_Environment $twig */
    private $twig;

    /**
     * Formats a log record.
     * @param  array $record A record to format
     * @return mixed The formatted record
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function format(array $record)
    {
        return $this->renderTwigTemplate('email.twig', [
            'level'      => $this->addTitle($record['level_name'], $record['level']),
            'title'      => substr($record['message'], 0, strpos($record['message'], ' - Trace:')),
            'context'    => $this->formatExtra($record, 'context'),
            'extra'      => $this->formatExtra($record, 'extra'),
            'datetime'   => $record['datetime']->format($this->dateFormat),
            'stacktrace' => $this->formatStacktrace($record['message']),
        ]);
    }

    /**
     * Renders a Twig template
     * @param $templatePath
     * @param array $data
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function renderTwigTemplate($templatePath, $data = [])
    {
        if (null === $this->twig) {
            // Use generic Twig environment if Grav's Twig environment has not been set
            $this->twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem(__DIR__ . '/twig'),
                ['autoescape' => false]
            );
        }
        return $this->twig->render($templatePath, $data);
    }

    /**
     * Formats an "extra" block (context, extra) into a table
     * @param array $record A record to format
     * @param string $recordKey The array key of the block in the record
     * @return string The formatted block
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function formatExtra($record, $recordKey)
    {
        if (!(array_key_exists($recordKey, $record)
            && count($record[$recordKey]) > 0)) {
            return '';
        }

        $rows = array_map(function ($key, $value) {
            return $this->addRow($key, $this->convertToString($value));
        },
            array_keys($record[$recordKey]),
            array_values($record[$recordKey])
        );

        return $this->renderTwigTemplate('extra.twig', ['rows' => $rows]);
    }

    /**
     * Formats the stacktrace from a message into a syntax-colored table
     * @param string $message The message containing the stacktrace
     * @return string The formatted stacktrace
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function formatStacktrace($message)
    {
        // Parse stacktrace
        if (!preg_match_all('/#[\d]+ (.+): ([^\n]+)/', $message, $matches, PREG_SET_ORDER)) {
            // Simple message without stacktrace
            return $this->renderTwigTemplate('simple-message.twig', ['message' => $message]);
        }

        return implode('', array_map(function ($frame) {
            // Process each stacktrace line
            list(, $path, $method) = $frame;
            return $this->renderTwigTemplate('stacktrace-line.twig', [
                'method' => $this->colorMethodSyntax($method),
                'path'   => $path
            ]);

        }, $matches));

    }

    /**
     * Colors a PHP method's syntax
     * @param string $method The method to color
     * @return string The HTML code of the coloured syntax
     */
    private function colorMethodSyntax($method)
    {
        return str_replace('&lt;?php&nbsp;', '',
            highlight_string('<?php ' . $method, true)
        );
    }

    /**
     * Set Twig environment
     * @param \Twig_Environment $twigEnvironment
     * @return PrettyHtmlFormatter
     */
    public function setTwigEnvironment(\Twig_Environment $twigEnvironment)
    {
        $this->twig = $twigEnvironment;
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__ . '/twig'));
        return $this;
    }

}
