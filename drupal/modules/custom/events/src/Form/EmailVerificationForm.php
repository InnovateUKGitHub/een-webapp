<?php
namespace Drupal\events\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailVerificationForm extends \Drupal\elastic_search\Form\EmailVerificationForm
{
    /**
     * @param ContainerInterface $container
     *
     * @return self
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('events.service'),
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
            'Thank you, please check your email to verify you want to register for this event.'
        );

        $this->service->verifyEmail($this->email, $this->token, $this->id);
    }
}