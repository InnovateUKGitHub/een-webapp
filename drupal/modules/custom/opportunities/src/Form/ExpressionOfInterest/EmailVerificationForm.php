<?php
namespace Drupal\opportunities\Form\ExpressionOfInterest;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailVerificationForm extends \Drupal\een_common\Form\EmailVerificationForm
{
    /**
     * @param ContainerInterface $container
     *
     * @return EmailVerificationForm
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('opportunities.service'),
            $container->get('user.private_tempstore')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->submit(
            $form_state,
            'Thank you, please check your email to verify you want to apply for a project.'
        );

        $this->service->verifyEmail($this->email, $this->token, $this->id);
    }
}