<?php
namespace Drupal\unccd_engagement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

use Drupal\unccd_engagement\ContestStorage;
use Drupal\unccd_engagement\EntryStorage;

class PublicContestEntryForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'public_contest_entry_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $contest_id = null) {
        $contest = ContestStorage::loadById($contest_id);

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Title:'),
            '#required' => TRUE,
        ];
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => t('Full name:'),
            '#required' => TRUE,
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email address:'),
            '#required' => TRUE,
            '#field_prefix' => t('<br>Your email will not be published. It will only be used to contact you if required.'),
        ];
        $form['description'] = [
            '#type' => 'textarea',
            '#title' => t('Description:'),
            '#required' => TRUE,
        ];
        switch($contest->type) {
            case "other":
                $form['attachment'] = [
                    '#type' => 'managed_file',
                    '#title' => "Attachment:",
                    '#upload_location' => 'public://constests/other',
                    '#multiple' => FALSE,
                    '#required' => TRUE,
                    '#upload_validators' => [
                        'file_validate_extensions' => ['gif png jpg jpeg pdf txt rar zip 7z'],
                        'file_validate_size' => [25600000]
                    ],
                ];
                break;

            case "text":
                $form['attachment'] = [
                    '#type' => 'managed_file',
                    '#title' => "PDF:",
                    '#upload_location' => 'public://constests/texts',
                    '#multiple' => FALSE,
                    '#required' => TRUE,
                    '#upload_validators' => [
                        'file_validate_extensions' => ['pdf'],
                        'file_validate_size' => [25600000]
                    ],
                ];
                break;
            case "photo":
            default:
                $form['attachment'] = [
                    '#type' => 'managed_file',
                    '#title' => "Photo:",
                    '#upload_location' => 'public://constests/photos',
                    '#multiple' => FALSE,
                    '#required' => TRUE,
                    '#upload_validators' => [
                        'file_validate_extensions' => ['gif png jpg jpeg'],
                        'file_validate_size' => [25600000]
                    ],
                ];
                break;
        }

        $form['contest_id'] = [
            '#type' => 'hidden',
            '#required' => FALSE,
            '#value' => $contest_id,
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Enter Contest'),
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
        // Handle the image upload
        if(!empty($form_state->getValue('attachment'))) {
            $image = $form_state->getValue('attachment');
            $file = File::load($image[0]);
            $file->setPermanent();
            $file->save();
            $attachment_url = $file->url();
        }

        // Save the entry to the database
        $fields = [
            'contest_id' => $form_state->getValue('contest_id'),
            'title' => $form_state->getValue('title'),
            'name' => $form_state->getValue('name'),
            'email' => $form_state->getValue('email'),
            'description' => $form_state->getValue('description'),
        ];
        if(!empty($form_state->getValue('attachment'))) {
            $fields['attachment_id'] = $form_state->getValue('attachment')[0];
            $fields['attachment'] = $attachment_url;
        }

        EntryStorage::insert($fields);
        
        drupal_set_message($this->t('Thanks for entering the contest!'));
        $form_state->setRedirect('engagement.contest.view', ['id' => $form_state->getValue('contest_id')]);
        return;
    }
}
