# v2.1.0
## 1/7/2017

1. [](#new)
    * Added option to have a data-callback for the Google Recaptcha
    
   [](#improved)
    * Added scroll anchor so the page scrolls to the message when there is one.

# v2.0.1
## 10/11/2016

1. [](#improved)
    * Added German (DE) translation for new admin integration. 

# v2.0.0
## 09/29/2016

1. [](#new)
    * The plugin can now be fully managed from the admin panel.
    * Modular and simple pages can have the contact form simply by toggling the form on.
    * Integrated with the Email plugin, if it is available.
2. [](#improved)
    * Removed unnecessary jQuery dependency.
    * Added ability to inject contact form into modular page (previously only would for simple).
    * Refactored messages to be session based instead of redirecting which could cause problems with languages.
    * Added better HTML classes to allow easier integration with the default template.
    * Improved CSS.
    * Made email recipient a little smarter with three levels of fallback.
3. [](#bugfix)
    * Fixed a bug that caused CSS to not load on modular pages.
    * Fixed a problem which caused the form to shrink at 50% of the screen width to an uncomfortable width.


# v1.0.9
## 09/03/2016

1. [](#new)
    * Added Russian translation.
2. [](#improved)
    * Made it easier to customize the CSS.
    * Updated the French translation.
3. [](#bugfix)
    * Removed extraneous class that was causing layout issues in Bootstrap themes.
    * Fixed some issues with redirects in multilingual installs (changed `redirect` to `redirectLangSafe`).
    * Fixed a very rare issue with multiple `language.yaml` files loading.

# v1.0.8
## 10/09/2015

1. [](#improved)
    * Added the ability to customize almost everything in the plugin very easily.

# v1.0.7
## 09/01/2015

1. [](#improved)
    * Allow compatibility with PHP 5.4.
2. [](#bugfix)
    * Fixed date in CHANGELOG.md.


# v1.0.6
## 08/30/2015

1. [](#improved)
    * Added blueprints for Grav Admin plugin
2. [](#new)
    * Now it works with modular pages. Now it depends on PHP 5.5 or +.

# v1.0.5
## 08/07/2015

1. [](#bugfix)
    * Bugfix in CHANGELOG.md

# v1.0.4
## 08/03/2015

1. [](#bugfix)
    * Bugfix in CHANGELOG.md

# v1.0.3
## 08/02/2015

1. [](#new)
    * Added German and Italian translations.

# v1.0.2
## 07/28/2015

1. [](#bugfix)
    * Bugfix in recatpchacontact.php


# v1.0.1
## 07/27/2015

1. [](#bugfix)
    * Now reCAPTCHA localization works.
2. [](#new)
    * Config variables can now be overwritten in the page headers.

# v1.0.0
## 07/26/2015

1. [](#new)
    * Change log started.
