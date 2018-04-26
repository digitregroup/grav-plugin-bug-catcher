<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;

/**
 * Class BugCatcherPlugin
 * @package Grav\Plugin
 */
class BugCatcherPlugin extends Plugin
{
    const GRAV_EMAIL_HANDLERS_KEY = 'grav-email-handlers';
    const SLACK_HANDLERS_KEY      = 'slack-handlers';
    const LOAD_PLUGIN_LAST        = -1;

    public static function getSubscribedEvents()
    {
        return ['onPluginsInitialized' => ['onPluginsInitialized', static::LOAD_PLUGIN_LAST]];
    }

    public function onPluginsInitialized()
    {
        // Adds Git branch and commit
        $this->grav['log']->pushProcessor(new GitProcessor());
        // Adds UID, File, Line, Class, and Function
        $this->grav['log']->pushProcessor(new IntrospectionProcessor());
        // Adds URL, IP, HTTP method, Server name and Referrer
        $this->grav['log']->pushProcessor(new WebProcessor($_SERVER));

        // Grav e-mail handlers
        foreach ($this->getConfigHandlers(static::GRAV_EMAIL_HANDLERS_KEY) as $config) {
            $this->addHandler($this->getGravEmailHandler($this->grav['Email'], $config), $config);
        }

        // Slack handlers
        foreach ($this->getConfigHandlers(static::SLACK_HANDLERS_KEY) as $config) {
            $this->addHandler($this->getSlackHandler($config), $config);
        }
    }

    /**
     * Get enabled handlers from plugin config file
     * @param string $handlersKey Handlers configuration key (ex: 'slack-handlers')
     * @return array Enabled handlers
     */
    private function getConfigHandlers($handlersKey)
    {
        $handlersConfigs = $this->grav['config']->get('plugins.bug-catcher.' . $handlersKey);
        return array_filter($handlersConfigs, function ($config) {
            return !(isset($config['enabled']) && false === (boolean)$config['enabled']);
        });
    }

    /**
     * Add handler and (optional) deduplicate entries
     * @param AbstractProcessingHandler $handler Monolog handler
     * @param array $config Handler configuration
     */
    private function addHandler($handler, $config)
    {
        // Deduplicate (enabled by default)
        $deduplicate = !(isset($config['deduplicate']) && false === (boolean)$config['deduplicate']);
        $this->grav['log']->pushHandler($deduplicate ? new DeduplicationHandler($handler) : $handler);
    }

    /**
     * Get Grav email handler
     * @param Email\Email $emailer Grav e-mail sender
     * @param array $emailConfig Handler configuration
     * @return BugCatcherHandler
     */
    private function getGravEmailHandler($emailer, $emailConfig)
    {
        include_once __DIR__ . '/BugCatcherHandler.php';
        include_once __DIR__ . '/PrettyHtmlFormatter.php';

        return new BugCatcherHandler(function ($record) use ($emailer, $emailConfig) {
            return $emailer->send($emailer
                ->message(
                    $emailConfig['subject'],
                    (new PrettyHtmlFormatter)->format($record),
                    'text/html'
                )
                ->setFrom($emailConfig['from'])
                ->setTo($emailConfig['to']));
        });
    }

    /**
     * Get Slack handler
     * @param array $slackConfig Slack handler configuration
     * @return SlackWebhookHandler
     */
    private function getSlackHandler($slackConfig)
    {
        $config = array_merge([
            'url'                       => null,
            'channel'                   => null,
            'username'                  => null,
            'use_attachment'            => true,
            'icon_emoji'                => null,
            'use_short_attachment'      => false,
            'include_context_and_extra' => false,
            'level'                     => Logger::CRITICAL
        ], $slackConfig);

        return new SlackWebhookHandler(
            $config['url'],
            $config['channel'],
            $config['username'],
            $config['use_attachment'],
            $config['icon_emoji'],
            $config['use_short_attachment'],
            $config['include_context_and_extra'],
            $config['level']
        );
    }
}
