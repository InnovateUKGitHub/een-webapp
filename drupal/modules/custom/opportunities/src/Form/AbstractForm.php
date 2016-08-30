<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;

abstract class AbstractForm extends FormBase
{
    const EMAIL_REGEX = '/^.+\@\S+\.\S+$/';
    const PHONE_REGEX = '/(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?/';

    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     * @param array              $fields
     *
     * @return AjaxResponse
     */
    protected function generateAjaxError(array &$form, FormStateInterface $form_state, $fields)
    {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('.js-form-item .error-message', ''));
        $response->addCommand(new InvokeCommand('.js-form-item.error', 'removeClass', ['error']));

        foreach ($fields as $field) {
            if (($error = $form_state->getError($form[$field]))) {
                $response->addCommand(
                    new HtmlCommand(
                        ".js-form-item-$field .error-message",
                        (is_array($error) ? $error['text'] : $error)
                    )
                );
                $response->addCommand(
                    new InvokeCommand(".js-form-item-$field", 'addClass', ['error'])
                );
            }
        }

        if ($form_state->getErrors()) {
            /** @var Renderer $renderer */
            $renderer = \Drupal::service('renderer');
            $status_messages = ['#type' => 'status_messages'];
            $messages = $renderer->renderRoot($status_messages);
            if (!empty($messages)) {
                $response->addCommand(new HtmlCommand('.status-messages', $messages));
                drupal_get_messages('error');
            }
        } else {
            $response->addCommand(new HtmlCommand('.status-messages', ''));
        }

        return $response;
    }

    /**
     * @param FormStateInterface $form_state
     * @param string             $field
     *
     * @return bool
     */
    protected function checkEmailAndPhoneField(FormStateInterface $form_state, $field)
    {
        if ($this->checkRequireField($form_state, 'email')) {
            $value = $form_state->getValue($field);
            if (!preg_match(self::EMAIL_REGEX, $value)) {
                if (!preg_match(self::PHONE_REGEX, $value)) {
                    $form_state->setErrorByName(
                        $field,
                        [
                            'key'  => 'edit-' . $field,
                            'text' => t("This is not a valid email or phone number."),
                        ]
                    );

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param FormStateInterface $form_state
     * @param string             $field
     *
     * @return bool
     */
    protected function checkRequireField(FormStateInterface $form_state, $field)
    {
        if (strlen($form_state->getValue($field)) < 1) {
            $form_state->setErrorByName(
                $field,
                [
                    'key'  => 'edit-' . $field,
                    'text' => t("The $field is required."),
                ]
            );

            return false;
        }

        return true;
    }
}