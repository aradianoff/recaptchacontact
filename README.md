# Grav reCAPTCHA Contact Plugin

[![Release](https://img.shields.io/github/release/aradianoff/recaptchacontact.svg)](project) [![Issues](https://img.shields.io/github/issues/aradianoff/recaptchacontact.svg)](issues) [![Dual license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE "License")

`reCAPTCHA Contact` is a [Grav](http://github.com/getgrav/grav) v0.9.33+ plugin based in the [Simple Contact](https://github.com/nunopress/grav-plugin-simple_contact) plugin from NunoPress LLC that adds a contact form in Grav pages with [Google reCAPTCHA](https://www.google.com/recaptcha/) validation to filter Spam Robots and multilang support. Currently Italian (it), Spanish (es), German (de) and English (en) translations are included by default in the `languages.yaml`.

## Installation

Installing the plugin can be done in one of two ways. Our GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's Terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install recaptchacontact

This will install the `reCAPTCHA Contact` plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/recaptchacontact`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `recaptchacontact`. You can find these files either on [GetGrav.org](http://getgrav.org/downloads/plugins#extras) or the [reCAPTCHA Contact GitHub repo](https://github.com/aradianoff/recaptchacontact).

You should now have all the plugin files under

    /your/site/grav/user/plugins/recaptchacontact

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate. It also requires having at least an outgoing mailserver on your server side (to send the emails) and a [reCAPTCHA API key](www.google.com/recaptcha/) for your site.

## Configuration

The plugin comes with some sensible default configuration that you can see in the `recaptchacontact.yaml` and `languages.yaml` files of the plugin, that are pretty self explanatory:

### Options in `recaptchacontact.yaml`

```
enabled: (true|false)               // Enables or Disables the entire plugin for all pages.
default_lang: en                    // default_lang in case there is no multilang support in the installation

grecaptcha_sitekey: "your reCAPTCHA site key" // override in your /user/config/plugins/recaptchacontact.yaml
grecaptcha_secret: "secret-g-recaptcha-key" // override in your /user/config/plugins/recaptchacontact.yaml and remember not to keep it in a public repository
```

> **WARNING:** For the reCAPTCHA to work you have to copy [recaptchacontact.yaml](recaptchacontact.yaml) in your `/user/config/plugins` folder and set your keys. If not, it will not work.

### Options in `languages.yaml`

```
  RECAPTCHACONTACT:
    FORM_LEGEND: "Contact me"                       // Form Legend
    SUBJECT: "New contact from Grav site!"          // Subject for email.
    RECIPIENT: "hello@example.com"            // Email address.

    FIELDS:                     // Default fields, you can translate the text.
      NAME:
        LABEL: "Name"
        PLACEHOLDER: "Add your name"

      EMAIL:
        LABEL: "Email"
        PLACEHOLDER: "Add your email"

      MESSAGE:
        LABEL: "Message"
        PLACEHOLDER: "Add your message"

      ANTISPAM:
        LABEL: "Antispam"
        PLACEHOLDER: "Please leave this field empty for Antispam"

      SUBMIT:
        LABEL: "Submit"

    MESSAGES:                   // Default messages, you can translate the text.
      SUCCESS: "Thank You! Your message has been sent."
      ERROR: "Oops! There was a problem with your submission. Please complete the form and try again."
      FAIL: "Oops! Something went wrong and we couldn't send your message."
```

If you want to add your own translations of the `languages.yaml`variables or modify the existing ones you can do so by creating a `languages`folder in your `user`folder and creating a `.yaml` file for the languages you want (ex. `es.yaml`) adding the above variables to the file and customizing their values.

## Usage

If you want to add the contact form to a page or modular page your can do it by adding to the page header:

```
    ---
    title: 'My "Page"'

    recaptchacontact: true
    ---

    # "Lorem ipsum dolor sit amet"
```

With this method you use the config file and languages file options (either the default ones or your customized ones if they exist). This will add the contact form at the end of the contents of your page.

But if you want to overwrite any of the configuration variables (including those in the `recaptchacontact/languages.yaml` you can also do it in the page header as in:

```
    ---
    title: 'My "Page"'

    recaptchacontact:
      form_legend: "Another legend for the form"
      subject: "Another subject form the email"
      recipient: "anotheremail@example.com"
      fields:
        name:
          label: "Another label for name"
          placeholder: "Another placeholder for mail"
      submit:
        label: "Another Submit Label"

      messages:
        success: "Hurray! You did it!"
    ---

    # "Lorem ipsum dolor sit amet"
```

Just use the same structure as in the `languages.yaml`file but use lowercase letters instead of uppercase.


## Updating

As development for this plugin continues, new versions may become available that add additional features and functionality, improve compatibility with newer Grav releases, and generally provide a better user experience. Updating this plugin is easy, and can be done through Grav's GPM system, as well as manually.

### GPM Update (preferred)

The simplest way to update this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm). You can do this by navigating to the root directory of your Grav install using your system's Terminal (also called command line) and typing the following:

    bin/gpm update recaptchacontact

This command will check your Grav install to see if your plugin is due for an update. If a newer release is found, you will be asked whether or not you wish to update. To continue, type `y` and hit enter. The plugin will automatically update and clear Grav's cache.

### Manual Update

Manually updating this plugin is pretty simple. Here is what you will need to do to get this done:

* Delete the `your/site/user/plugins/recaptchacontact` directory.
* Download the new version of the plugin from either [GetGrav.org](http://getgrav.org/downloads/plugins#extras) or the [reCAPTCHA Contact GitHub repo](https://github.com/aradianoff/recaptchacontact).
* Unzip the zip file in `your/site/user/plugins` and rename the resulting folder to `recaptchacontact`.
* Clear the Grav cache. The simplest way to do this is by going to the root Grav directory in terminal and typing `bin/grav clear-cache`.

> Note: Any changes you have made to any of the files listed under this directory will also be removed and replaced by the new set. Any files located elsewhere (for example a YAML settings file placed in `user/config/plugins`) will remain intact.

## Acknowledgements:

- @nunopress: For the [Simple Contact](https://github.com/nunopress/grav-plugin-simple_contact) plugins in which this one is based.
- @iusvar: For the Italian translation.
- @Sommerregen: For the German translation.
