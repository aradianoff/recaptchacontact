<?php
/**
 * reCAPTCHA Contact v2.1.0
 *
 * This plugin adds contact form features for sending email with
 * google reCAPTCHA 2.0  validation.
 *
 * Licensed under the MIT license, see LICENSE.
 *
 * @package     recaptchacontact
 * @version     2.1.0
 * @link        <https://github.com/aradianoff/recaptchacontact>
 * @author      aRadianOff - Inés Naya <inesnaya@aradianoff.com>
 * @copyright   2017, Inés Naya - aRadianOff
 * @license     <http://opensource.org/licenses/MIT>        MIT
 */

namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Common\Uri;

class ReCaptchaContactPlugin extends Plugin
{
    /**
     * Allows an optional override for the default CSS loading of whether or
     * not the plugin is enabled on the page or not.
     *
     * Used for modular pages where one page may have it enabled and not the rest.
     *
     * @var bool
     */
    protected $shouldLoadCss = false;

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onGetPageTemplates' => ['onGetPageTemplates', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $this->enable([
            'onTwigTemplatePaths'   => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables'   => ['onTwigSiteVariables', 0],
            'onPageInitialized'     => ['onPageInitialized', 0]
        ]);
    }

    public function onGetPageTemplates($event)
    {
        $event->types->scanBlueprints('plugin://' . $this->name . '/blueprints');
    }

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigSiteVariables() // Esto se procesa después de onPageInitialized
    {
        $config = $this->grav['config'];

        if ($config->get('plugins.recaptchacontact.enabled') || $this->shouldLoadCss) {
            if (!$config->get('plugins.recaptchacontact.disable_css')) {
                $this->grav['assets']->addCss('plugin://recaptchacontact/assets/recaptchacontact.css');
            } else {
                $this->grav['assets']->addCss('theme://assets/recaptchacontact.css');
            }

            $this->grav['twig']->twig_vars['recaptchacontact'] = $this->grav['config']->get('plugins.recaptchacontact');
            $this->grav['twig']->twig_vars['recaptchacontact']['message'] = $this->grav['session']->contact_message;
            $this->grav['twig']->twig_vars['recaptchacontact']['session'] = $this->grav['session']->form;
        }
    }

    public function onPageInitialized()
    {
        if (!empty($this->grav['page']->collection())){
            $collection = $this->grav['page']->collection();

            /** @var $page Page */
            foreach ($collection as $page) {
                if (isset($page->header()->recaptchacontact)){
                    $this->setupRecaptchaContact($page, true);
                }
            }
        } else {
            $this->setupRecaptchaContact($this->grav['page']);
        }
    }

    /**
     * Automatically add the contact form to the Page being loaded
     *
     * @param \Grav\Common\Page\Page $page
     */
    protected function injectTemplate(Page $page)
    {
        /** @var $twig \Grav\Common\Twig\Twig */
        $twig = $this->grav['twig'];
        $original_content = $page->content();
        $template = 'partials/recaptcha_container.html.twig';

        $data = [
            'recaptchacontact' => $this->grav['config']->get('plugins.recaptchacontact'),
            'page' => $page
        ];

        $data['recaptchacontact']['message'] = $this->grav['session']->contact_message;
        $data['recaptchacontact']['session'] = $this->grav['session']->form;


        // The surrounding div tags are SOLELY a workaround for a
        // Parsedown bug that throws away anything after the page content
        // which, in this case is the entire form, if it is not surrounded.
        $page->content('<div>' . $original_content . $twig->processTemplate($template, $data) . '</div>');
    }

    /**
     * Setup the Recaptcha Contact form
     *
     * @param \Grav\Common\Page\Page $page
     */
    protected function setupRecaptchaContact(Page $page)
    {
        $this->mergePluginConfig($page);
        $options = $this->grav['config']->get('plugins.recaptchacontact');

        if ($options['enabled']) {
            $uri = $this->grav['uri'];

            $this->processFormAction($uri);

            if ($options['inject_template'] === true) {
                $this->injectTemplate($page);
            }
        }
    }

    /**
     * Handle the Form Process
     *
     * @param \Grav\Common\Uri $uri
     */
    protected function processFormAction(Uri $uri)
    {
        $message_success = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.success', 'RECAPTCHACONTACT.MESSAGES.SUCCESS');
        $message_error = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.error', 'RECAPTCHACONTACT.MESSAGES.ERROR');
        $message_fail = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.fail', 'RECAPTCHACONTACT.MESSAGES.FAIL');

        if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['g-recaptcha-response'])) {
            $this->clearSession();

            if (false === $this->validateFormData()) {
                $this->setSubmissionMessage('error', $message_error);
                $this->setSessionFields();
            } else {
                if (false === $this->sendEmail()) {
                    $this->setSubmissionMessage('fail', $message_fail);
                    $this->setSessionFields();
                } else {
                    $this->setSubmissionMessage('success', $message_success);
                }
            }

            $this->grav->redirectLangSafe($uri->url());
        }
    }

    protected function setSessionFields()
    {
        $fields = [];

        $fields['name'] = htmlspecialchars($_POST['name']);
        $fields['email'] = htmlspecialchars($_POST['email']);
        $fields['message'] = htmlspecialchars($_POST['message']);

        $this->grav['session']->form = $fields;
    }

    /**
     * @param $type: Type of message to be displayed
     * @param $text: Text of message for user
     */
    protected function setSubmissionMessage($type, $text)
    {
        $this->grav['session']->contact_message = [
            'type' => $type,
            'text' => $text
        ];
    }

    protected function clearSession()
    {
        $this->grav['session']->contact_message = null;
        $this->grav['session']->form = null;
    }

    /**
     * Make sure that the form is valid
     *
     * @return bool
     */
    protected function validateFormData()
    {
        $form_data = $this->filterFormData($_POST);

        $name     = $form_data['name'];
        $email    = $form_data['email'];
        $message  = $form_data['message'];

        $antispam = $form_data['antispam'];

        $grecaptcha = $form_data['g-recaptcha-response'];
        $secretkey = $this->grav['config']->get('plugins.recaptchacontact.grecaptcha_secret');

        if (!empty($grecaptcha)) {
           $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretkey."&response=".$grecaptcha), true);
        }

        return (empty($name) or empty($message) or empty($email) or $antispam or empty($grecaptcha) or $response['success']==false) ? false : true;
    }

    /**
     * Clean up and abstract form data
     *
     * @param $form
     *
     * @return array
     */
    protected function filterFormData($form)
    {
        $defaults = [
            'name'      => '',
            'email'     => '',
            'message'   => '',
            'antispam'  => '',
            'g-recaptcha-response' => ''
        ];

        $data = array_merge($defaults, $form);

        return [
            'name'      => $data['name'],
            'email'     => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'message'   => $data['message'],
            'antispam'  => $data['antispam'],
            'g-recaptcha-response' => $data['g-recaptcha-response']
        ];
    }

    /**
     * Send the email
     *
     * @return bool: returns true if email was sent | return false if it failed to send
     */
    protected function sendEmail()
    {
        $form   = $this->filterFormData($_POST);

        $recipient  = $this->getEmailRecipient();
        $subject    = $this->overwriteConfigVariable('plugins.recaptchacontact.subject','RECAPTCHACONTACT.SUBJECT');
        $email_content = "Name: {$form['name']}\n";
        $email_content .= "Email: {$form['email']}\n\n";
        $email_content .= "Message:\n{$form['message']}\n";

        $email_headers = "From: {$form['name']} <{$form['email']}>";

        if ($this->grav['config']->get('plugins.email.enabled')) {
            $message = $this->grav['Email']->message($subject, $email_content, 'text/html')
                ->setFrom($form['email'])
                ->setTo($recipient);

            return $this->grav['Email']->send($message);
        } else {
            return (mail($recipient, $subject, $email_content, $email_headers)) ? true : false;
        }
    }

    protected function getEmailRecipient()
    {
        $recipient  = $this->overwriteConfigVariable('plugins.recaptchacontact.recipient','RECAPTCHACONTACT.RECIPIENT');

        if ((!$recipient || $recipient === 'hello@example.com' || $recipient === 'name@provider.de') &&
            isset($this->grav['config']['site']['author']['email'])) {
            $recipient = $this->grav['config']['site']['author']['email'];
        }

        return $recipient;
    }

    protected function mergePluginConfig(Page $page)
    {
        $defaults = (array) $this->grav['config']->get('plugins.recaptchacontact');

        if (isset($page->header()->recaptchacontact)) {
            if (is_array($page->header()->recaptchacontact)) {
                $this->grav['config']->set('plugins.recaptchacontact', array_replace_recursive($defaults, $page->header()->recaptchacontact));
            } else {
                $this->grav['config']->set('plugins.recaptchacontact.enabled', $page->header()->recaptchacontact);
            }
        } else {
            $this->grav['config']->set('plugins.recaptchacontact.enabled', false);
        }

        $this->enableCssLoading();
    }

    protected function enableCssLoading()
    {
        if ($this->grav['config']->get('plugins.recaptchacontact')['enabled']) {
            $this->shouldLoadCss = true;
        }
    }

    private function overwriteConfigVariable($pageconfigvar, $langconfigvar)
    {
        $language = $this->grav['language'];
        return $this->grav['config']->get($pageconfigvar) ?: $language->translate([$langconfigvar]);
    }

}
