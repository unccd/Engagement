<?php
/**
 * @file
 * Contains \Drupal\unccd_engagement\Form\EditCampaignForm.
 */
namespace Drupal\unccd_engagement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

use Drupal\unccd_engagement\CampaignStorage;

class EditCampaignForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'edit_campaign_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $campaign = CampaignStorage::loadById(['id' => $id]);
        if ($campaign == null) {
            drupal_set_message($this->t('Could not find campaign.'));
            return $this->redirect('engagement.campaign_admin.list');
        }
        
        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Title:'),
            '#required' => TRUE,
            '#default_value' => $campaign->title,
        ];
        $form['status'] = [
            '#type' => 'select',
            '#title' => t('Status:'),
            '#default_value' => $campaign->status,
            '#options' => [
                0 => 'Draft',
                1 => 'Live',
            ],
        ];
        $form['button_text'] = [
            '#type' => 'textfield',
            '#title' => t('Button Text:'),
            '#default_value' => $campaign->button_text,
        ];
        $form['description'] = [
            '#type' => 'text_format',
            '#title' => t('Description:'),
            '#required' => TRUE,
            '#default_value' => $campaign->description,
        ];
        $form['id'] = [
            '#type' => 'hidden',
            '#required' => FALSE,
            '#value' => $campaign->id,
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
        // Update db entry
        $fields = [
            'id'  => $form_state->getValue('id'),
            'title' => $form_state->getValue('title'),
            'status' => $form_state->getValue('status'),
            'button_text' => $form_state->getValue('button_text'),
            'description' => $form_state->getValue('description')['value'],
        ];
        CampaignStorage::update($fields);

        // Redirect to campaign list
        drupal_set_message($this->t('Campaign sucessfully saved.'));
        $form_state->setRedirect('engagement.campaign_admin.list');
        return;
    }
}
