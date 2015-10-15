<?php 
/**
 * reCAPTCHA Contact v1.0.8
 *
 * This plugin adds contact form features for sending email with 
 * google reCAPTCHA 2.0  validation.
 *
 * Licensed under the MIT license, see LICENSE.
 *
 * @package     recaptchacontact
 * @version     1.0.8
 * @link        <https://github.com/aradianoff/recaptchacontact>
 * @author      aRadianOff - Inés Naya <inesnaya@aradianoff.com>
 * @copyright   2015, Inés Naya - aRadianOff
 * @license     <http://opensource.org/licenses/MIT>        MIT
 */
 
namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Common\Uri;

class ReCaptchaContactPlugin extends Plugin
{
    protected $submissionMessage = array();

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
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

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigSiteVariables() // Esto se procesa después de onPageInitialized
    {
        $config = $this->grav['config'];

        if ($config->get('plugins.recaptchacontact.enabled')) {
            if (!$config->get('plugins.recaptchacontact.disable_css')) {
                $this->grav['assets']->addCss('plugin://recaptchacontact/assets/css/style.css');
            }

            $this->grav['twig']->twig_vars['recaptchacontact'] = $this->grav['config']->get('plugins.recaptchacontact');
            $this->grav['twig']->twig_vars['recaptchacontact']['message'] = $this->submissionMessage;
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

        $data['recaptchacontact']['message'] = $this->submissionMessage;

        // The surrounding div tags are SOLELY a workaround for a
        // Parsedown bug that throws away anything after the page content
        // which, in this case is the entire form, if it is not surrounded.
        $page->content('<div>' . $original_content . $twig->processTemplate($template, $data) . '</div>');
    }

    protected function setupRecaptchaContact(Page $page, $collection = false)
    {
        $this->mergePluginConfig($page); 
        $options = $this->grav['config']->get('plugins.recaptchacontact');
        
        if ($options['enabled']) {
            $uri = $this->grav['uri'];

            if ($uri->param('send') === false) {
                $this->processFormAction($uri);
            } else {
                $this->getMessageFromUrl($uri);
            }

            if ($options['inject_template'] === true && !$collection) {
                $this->injectTemplate($this->grav['page']);
            }
        }
    }

    protected function processFormAction(Uri $uri)
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (false === $this->validateFormData()) {
                $this->grav->redirect($uri->url . '/send:error');
            } else {
                if (false === $this->sendEmail()) {
                    $this->grav->redirect($uri->url . '/send:fail');
                } else {
                    $this->grav->redirect($uri->url . '/send:success');
                }
            }
        }
    }

    protected function setSubmissionMessage($type, $text)
    {
        $this->submissionMessage = [
            'type' => $type,
            'text' => $text
        ];
    }

    protected function getMessageFromUrl(Uri $uri)
    {
        $message_success = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.success', 'RECAPTCHACONTACT.MESSAGES.SUCCESS');
        $message_error = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.error', 'RECAPTCHACONTACT.MESSAGES.ERROR');
        $message_fail = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.fail', 'RECAPTCHACONTACT.MESSAGES.FAIL');

        switch ($uri->param('send')) {
            case 'success':
                $this->setSubmissionMessage('success', $message_success);
                break;

            case 'error':
                $this->setSubmissionMessage('error', $message_error);
                break;

            case 'fail':
                $this->setSubmissionMessage('fail', $message_fail);
                break;
        }
    }

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

    protected function sendEmail()
    {
        $form   = $this->filterFormData($_POST);

        $recipient  = $this->overwriteConfigVariable('plugins.recaptchacontact.recipient','RECAPTCHACONTACT.RECIPIENT'); 
        $subject    = $this->overwriteConfigVariable('plugins.recaptchacontact.subject','RECAPTCHACONTACT.SUBJECT'); 
        $email_content = "Name: {$form['name']}\n";
        $email_content .= "Email: {$form['email']}\n\n";
        $email_content .= "Message:\n{$form['message']}\n";

        $email_headers = "From: {$form['name']} <{$form['email']}>";

        return (mail($recipient, $subject, $email_content, $email_headers)) ? true : false;
    }

    private function mergePluginConfig(Page $page)
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
    }
    
    private function overwriteConfigVariable($pageconfigvar, $langconfigvar)
    {
        $language = $this->grav['language']; 
        return $this->grav['config']->get($pageconfigvar) ?: $language->translate([$langconfigvar], null, true);
    }
    
}
