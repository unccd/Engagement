<?php
namespace Drupal\unccd_engagement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

use Drupal\unccd_engagement\EntryStorage;

class EditContestEntryForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'edit_contest_entry_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $contest_id = null, $entry_id = null) {
        $entry = EntryStorage::loadById(['id' => $entry_id]);
        if ($entry == null) {
            drupal_set_message($this->t('Could not find entry.'));
            return $this->redirect('engagement.contest_entry_admin.list');
        }

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Title:'),
            '#required' => TRUE,
            '#default_value' => $entry->title,
        ];
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => t('Author name:'),
            '#default_value' => $entry->name,
        ];
        $form['postal_address'] = [
            '#type' => 'textarea',
            '#title' => t('Postal Address:'),
            '#default_value' => $entry->postal_address,
        ];
        $form['country'] = [
            '#type' => 'textfield',
            '#title' => t('Country:'),
            '#default_value' => $entry->country,
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Author email:'),
            '#default_value' => $entry->email,
        ];
        $form['how_did_you_know'] = [
            '#type' => 'textfield',
            '#title' => t('How did you know about the contest?'),
            '#default_value' => $entry->how_did_you_know,
        ];
        $form['description'] = [
            '#type' => 'textarea',
            '#title' => t('Description:'),
            '#required' => TRUE,
            '#default_value' => $entry->description,
        ];
        switch($contest->type) {
            case "other":
                $form['attachment'] = [
                    '#type' => 'managed_file',
                    '#title' => "Attachment:",
                    '#upload_location' => 'public://constests/other',
                    '#multiple' => FALSE,
                    '#required' => TRUE,
                    '#default_value' => [$entry->attachment_id],
                    '#upload_validators' => [
                        'file_validate_extensions' => ['gif png jpg jpeg pdf txt rar zip 7z'],
                        'file_validate_size' => [25600000]
                    ],
                ];
                break;
            case "video":
                $form['youtube'] = [
                    '#type' => 'textfield',
                    '#title' => "Youtube video:",
                    '#required' => TRUE,
                    '#default_value' => $entry->attachment,
                    '#field_prefix' => t('<br>Please upload the video on Youtube and insert the video link here.<br> If you do not want the video to be visible on your Youtube profile, you can mark it as "Unlisted" in the video settings.'),
                ];
                break;
            case "text":
                $form['attachment'] = [
                    '#type' => 'managed_file',
                    '#title' => "PDF:",
                    '#upload_location' => 'public://constests/texts',
                    '#multiple' => FALSE,
                    '#required' => TRUE,
                    '#default_value' => [$entry->attachment_id],
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
                    '#default_value' => [$entry->attachment_id],
                    '#upload_validators' => [
                        'file_validate_extensions' => ['gif png jpg jpeg'],
                        'file_validate_size' => [25600000]
                    ],
                ];
                break;
        }
        $form['id'] = [
            '#type' => 'hidden',
            '#required' => FALSE,
            '#value' => $entry_id,
        ];
        $form['contest_id'] = [
            '#type' => 'hidden',
            '#required' => FALSE,
            '#value' => $contest_id,
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
        // Handle attachment upload
        if(!empty($form_state->getValue('attachment'))) {
            $image = $form_state->getValue('attachment');
            $file = File::load($image[0]);
            $file->setPermanent();
            $file->save();
            $attachment_url = $file->url();
        }

        // Save the changes to the database
        $fields = [
            'id' => $form_state->getValue('id'),
            'title' => $form_state->getValue('title'),
            'name' => $form_state->getValue('name'),
            'email' => $form_state->getValue('email'),
            'postal_address' => $form_state->getValue('postal_address'),
            'country' => $form_state->getValue('country'),
            'how_did_you_know' => $form_state->getValue('how_did_you_know'),
            'description' => $form_state->getValue('description'),
        ];

        if(!empty($form_state->getValue('attachment'))) {
            $fields['attachment_id'] = $form_state->getValue('attachment')[0];
            $fields['attachment'] = $attachment_url;
        } else if(!empty($form_state->getValue('youtube'))) {
            $fields['attachment_id'] = null;
            $fields['attachment'] = $form_state->getValue('youtube');
        } else {
            $fields['attachment_id'] = null;
            $fields['attachment'] = null;
        }

        EntryStorage::update($fields);
        
        // Redirect to contest list
        drupal_set_message($this->t('Contest entry sucessfully updated.'));
        $form_state->setRedirect('engagement.contest_entry_admin.list', ['contest_id' => $form_state->getValue('contest_id')]);
        return;
    }
}
