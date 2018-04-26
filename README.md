# Bug Catcher Plugin

The **Bug Catcher** Plugin is for [Grav CMS](http://github.com/getgrav/grav). Catch errors and get notifications via Email or Slack

## Installation

Installing the Bug Catcher plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install bug-catcher

This will install the Bug Catcher plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/bug-catcher`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `bug-catcher`. You can find these files on [GitHub](https://github.com/digital-factory/grav-plugin-bug-catcher) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/bug-catcher

> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/bug-catcher/bug-catcher.yaml` to `user/config/plugins/bug-catcher.yaml` and only edit that copy.

Here is an explanation of available options:

```yaml
enabled: true

grav-email-handlers:
  -
    enabled: true
    subject: 'New error' # The e-mail subject
    from: bugs@website.com # The sender e-mail
    to: you@website.com # The recipient e-mail
    deduplicate: true # Remove duplicate errors?

slack-handlers:
  -
    enabled: true
    url: 'https://hooks.slack.com/services/_________/_________/________________________' # Slack integration URL
    channel: '#random' # Slack channel to post into
    use_short_attachment: true # See Slack documentation for these
    include_context_and_extra: true # See Slack documentation for these
    deduplicate: true # Remove duplicate errors?
```

Note that if you use the admin plugin, a file with your configuration, and named bug-catcher.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

**Describe how to use the plugin.**

## Credits

**Did you incorporate third-party code? Want to thank somebody?**

## To Do

- [ ] Future plans, if any

