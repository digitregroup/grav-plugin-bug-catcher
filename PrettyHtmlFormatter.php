<?php

namespace Grav\Plugin;

class PrettyHtmlFormatter extends \Monolog\Formatter\HtmlFormatter
{
    /**
     * Formats a log record.
     * @param  array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $template = <<<HTML
<div style="font-family: 'DejaVu Sans Mono', Menlo, Monaco, Consolas, Courier, monospace;font-size: .8em">
{{LEVEL}}
<h2>{{TITLE}}</h2>
{{CONTEXT}}
{{EXTRA}}
<pre style="font-size: 1.2em">🕒 {{DATETIME}}</pre>
{{STACKTRACE}}
</div>
HTML;

        return str_replace(['{{LEVEL}}', '{{TITLE}}', '{{CONTEXT}}', '{{EXTRA}}', '{{DATETIME}}', '{{STACKTRACE}}'], [
            $this->addTitle($record['level_name'], $record['level']), // LEVEL
            substr($record['message'], 0, strpos($record['message'], ' - Trace:')), // TITLE
            $this->formatExtra($record, 'context'), // CONTEXT
            $this->formatExtra($record, 'extra'), // EXTRA
            $record['datetime']->format($this->dateFormat), // DATETIME
            $this->formatStacktrace($record['message']), // STACKTRACE
        ], $template);
    }

    /**
     * Formats an "extra" block (context, extra) into a table
     * @param array $record A record to format
     * @param string $recordKey The array key of the block in the record
     * @return string The formatted block
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

        return sprintf('<table cellspacing="1" width="100%%">%s</table>', implode('', $rows));
    }

    /**
     * Formats the stacktrace from a message into a syntax-colored table
     * @param string $message The message containing the stacktrace
     * @return string The formatted stacktrace
     */
    private function formatStacktrace($message)
    {
        // Parse stacktrace
        if (!preg_match_all('/#[\d]+ (.+): ([^\n]+)/', $message, $matches, PREG_SET_ORDER)) {
            // Simple message without stacktrace
            return sprintf('<div style="font-weight: bold;font-size: 1.2em;">%s</div>', $message);
        }

        $template = <<<HTML
 <div style="padding: .5em 0;">
    <span style="font-size: 1.3em;">%s</span>
    <div style="color: #999;;">%s</div>
</div>
HTML;

        return implode('', array_map(function ($frame) use ($template) {

            // Process each stacktrace line
            list(, $path, $method) = $frame;
            return sprintf($template, $this->colorMethodSyntax($method), $path);

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

}
