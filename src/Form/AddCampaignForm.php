<?php
/**
 * @file
 * Contains \Drupal\unccd_engagement\Form\AddCampaignForm.
 */
namespace Drupal\unccd_engagement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

use Drupal\unccd_engagement\CampaignStorage;

class AddCampaignForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'add_campaign_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Title:'),
            '#required' => TRUE,
        ];
        $form['status'] = [
            '#type' => 'select',
            '#title' => t('Status:'),
            '#options' => [
                0 => 'Draft',
                1 => 'Live',
            ],
        ];
        $form['button_text'] = [
            '#type' => 'textfield',
            '#title' => t('Button text:'),
            '#required' => TRUE,
            '#default_value' => 'Support',
        ];
        $form['description'] = [
            '#type' => 'text_format',
            '#title' => t('Description:'),
            '#required' => TRUE,
            '#default_value' => '<p></p>',
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        ];
        
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        if (strlen($form_state->getValue('title')) < 3) {
            $form_state->setErrorByName('title', $this->t('Title is too short.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Save the campaign to the database
        $fields = [
            'title' => $form_state->getValue('title'),
            'status' => $form_state->getValue('status'),
            'button_text' => $form_state->getValue('button_text'),
            'description' => $form_state->getValue('description')['value'],
        ];
        CampaignStorage::insert($fields);
        
        // Redirect to campaign list
        drupal_set_message($this->t('Campaign sucessfully created.'));
        $form_state->setRedirect('engagement.campaign_admin.list');
        return;
    }
}
