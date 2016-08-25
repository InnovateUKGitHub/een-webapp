<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class AbstractForm extends FormBase
{
    /**
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return AjaxResponse
     */
    protected function submitHandler(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('.js-form-item .error-message', ''));
        $response->addCommand(new InvokeCommand('.js-form-item.error', 'removeClass', ['error']));
        if ($form_state->getErrors()) {
            foreach ($form_state->getErrors() as $error) {
                $response->addCommand(
                    new HtmlCommand('.js-form-item-' . $error['element'] . ' .error-message', $error['text'])
                );
                $response->addCommand(
                    new InvokeCommand('.js-form-item-' . $error['element'], 'addClass', ['error'])
                );
            }
        }

        return $response;
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
                    'element' => $field,
                    'key'     => 'edit-' . $field,
                    'text'    => t("The $field is required."),
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param FormStateInterface $form_state
     * @param string             $field
     *
     * @return bool
     */
    protected function checkEmailField(FormStateInterface $form_state, $field)
    {
        $value = $form_state->getValue($field);
        if (!preg_match('/^.+\@\S+\.\S+$/', $value)) {
            $form_state->setErrorByName(
                $field,
                [
                    'element' => $field,
                    'key'     => 'edit-' . $field,
                    'text'    => t("The $field is not valid."),
                ]
            );
        }

        return true;
    }
}