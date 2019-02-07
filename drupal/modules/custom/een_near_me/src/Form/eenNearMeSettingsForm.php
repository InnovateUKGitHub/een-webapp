<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\een_near_me\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin Settings form for Near Me
 *
 * @author simonpotthast
 */
class eenNearMeSettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'een_near_me_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'een_near_me.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('een_near_me.settings');
    
    $form['enable_notifications'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Notifications'),
      '#default_value' => $config->get('enable_notifications'),
    );

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    $this->config('een_near_me.settings')
      // Set the submitted configuration setting
      ->set('enable_notifications', $form_state->getValue('enable_notifications'))
      // You can set multiple configurations at once by making
      // multiple calls to set()
      ->save();

    parent::submitForm($form, $form_state);
  }
}
