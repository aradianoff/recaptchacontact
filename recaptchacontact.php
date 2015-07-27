<?php 
/**
 * reCAPTCHA Contact v1.0.2
 *
 * This plugin adds contact form features for sending email with 
 * google reCAPTCHA 2.0  validation.
 *
 * Licensed under the MIT license, see LICENSE.
 *
 * @package     recaptchacontact
 * @version     1.0.2
 * @link        <https://github.com/aradianoff/recaptchacontact>
 * @author      Inés Naya <inesnaya@aradianoff.com>
 * @copyright   2015, Inés Naya - aRadianOff
 * @license     <http://opensource.org/licenses/MIT>        MIT
 */
 
namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;

class   ReCaptchaContactPlugin extends Plugin
{
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

    public function onTwigSiteVariables()
    {
        if ($this->grav['config']->get('plugins.recaptchacontact.enabled')) {
            $this->grav['assets']->addCss('plugin://recaptchacontact/assets/css/style.css');
        }
    }
    
    public function onPageInitialized()
    {    
        $this->mergePluginConfig($this->grav['page']); 
        $config = $this->grav['config'];
        $options = $config->get('plugins.recaptchacontact'); 
     
        $message_success = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.success', 'RECAPTCHACONTACT.MESSAGES.SUCCESS');
        $message_error = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.error', 'RECAPTCHACONTACT.MESSAGES.ERROR');
        $message_fail = $this->overwriteConfigVariable('plugins.recaptchacontact.messages.fail', 'RECAPTCHACONTACT.MESSAGES.FAIL');


        if ($options['enabled']) {
            $page   = $this->grav['page'];
            $twig   = $this->grav['twig'];
            $uri    = $this->grav['uri'];

            if (false === $uri->param('send')) {
                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                    if (false === $this->validateFormData()) {
                        $this->grav->redirect($page->slug() . '/send:error');
                    } else {
                        if (false === $this->sendEmail()) {
                            $this->grav->redirect($page->slug() . '/send:fail');
                        } else {
                            $this->grav->redirect($page->slug() . '/send:success');
                        }
                    }
                } else {
                    $old_content = $page->content();

                    $template = 'partials/recaptchaform.html.twig';
                    $data = [
                      'recaptchacontact' => $options,
                      'page' => $page
                    ];

                    $page->content($old_content .$twig->processTemplate($template, $data));
                }
            } else {
                switch ($uri->param('send')) {
                    case 'success':
                        $page->content($message_success);
                    break;

                    case 'error':
                        $page->content($message_error);
                    break;

                    case 'fail':
                        $page->content($message_fail);
                    break;

                    default:
                    break;
                }
            }
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
        $options = $this->grav['config']->get('plugins.recaptchacontact');
        
        $recipient  = $this->overwriteConfigVariable('plugins.recaptchacontact.recipient'],'RECAPTCHACONTACT.RECIPIENT'); 
        $subject    = $this->overwriteConfigVariable('plugins.recaptchacontact.subject'],'RECAPTCHACONTACT.SUBJECT'); 
        $email_content = "Name: {$form['name']}\n";
        $email_content .= "Email: {$form['email']}\n\n";
        $email_content .= "Message:\n{$form['message']}\n";

        $email_headers = "From: {$form['name']} <{$form['email']}>";

        return (mail($recipient, $subject, $email_content, $email_headers)) ? true : false;
    }

    private function mergePluginConfig( Page $page )
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
