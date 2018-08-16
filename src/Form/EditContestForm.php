<?php
namespace Drupal\unccd_engagement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

use Drupal\unccd_engagement\ContestStorage;

class EditContestForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'edit_contest_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $id = null) {
        $contest = ContestStorage::loadById(['id' => $id]);
        if ($contest == null) {
            drupal_set_message($this->t('Could not find contest.'));
            return $this->redirect('engagement.contest_admin.list');
        }
        $form['title'] = [
            '#type' => 'textfield',
            '#title' => t('Title:'),
            '#required' => TRUE,
            '#default_value' => $contest->title,
        ];
        $form['type'] = [
            '#type' => 'select',
            '#title' => t('Type of contest:'),
            '#options' => [
                "photo" => 'Photo',
                "video" => 'Video',
                "text" => 'Text',
                "other" => 'Other'
            ],
            '#default_value' => $contest->type,
        ];
        $form['status'] = [
            '#type' => 'select',
            '#title' => t('Status:'),
            '#options' => [
                0 => 'Draft',
                1 => 'Live',
            ],
            '#default_value' => $contest->status,
        ];
        $form['number_of_votes_allowed'] = [
            '#type' => 'textfield',
            '#title' => t('Number of votes per person:'),
            '#field_prefix' => t('This option allows for users to be able to vote for multiple differen entries in the contest.<br>Only one vote per entry.<br>'),
            '#required' => TRUE,
            '#default_value' => $contest->number_of_votes_allowed,
        ];
        $form['deadline_for_entries'] = [
            '#type' => 'date',
            '#title' => t('Deadline for new entries:'),
            '#required' => TRUE,
            '#default_value' => date_format(date_create($contest->deadline_for_entries), "Y-m-d"),
        ];
        $form['voting_starts'] = [
            '#type' => 'date',
            '#title' => t('Voting starts on:'),
            '#required' => TRUE,
            '#default_value' => date_format(date_create($contest->voting_starts), "Y-m-d"),
        ];
        $form['deadline_for_voting'] = [
            '#type' => 'date',
            '#title' => t('Deadline for voting:'),
            '#required' => TRUE,
            '#default_value' => date_format(date_create($contest->deadline_for_voting), "Y-m-d"),
        ];
        $form['allow_online_entries'] = [
            '#type' => 'select',
            '#title' => t('Allow entry submission:'),
            '#options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            '#default_value' => $contest->allow_online_entries,
        ];
        $form['show_number_of_votes'] = [
            '#type' => 'select',
            '#title' => t('Show number of votes:'),
            '#options' => [
                1 => 'Yes',
                0 => 'No',
            ],
            '#default_value' => $contest->show_number_of_votes,
        ];
        $form['description'] = [
            '#type' => 'text_format',
            '#title' => t('Description:'),
            '#required' => TRUE,
            '#default_value' => $contest->description,
        ];
        $form['id'] = [
            '#type' => 'hidden',
            '#required' => FALSE,
            '#value' => $id,
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
        // Save the contest to the database
        $fields = [
            'id' => $form_state->getValue('id'),
            'title' => $form_state->getValue('title'),
            'type' => $form_state->getValue('type'),
            'status' => $form_state->getValue('status'),
            'number_of_votes_allowed' => $form_state->getValue('number_of_votes_allowed'),
            'deadline_for_entries' => $form_state->getValue('deadline_for_entries'),
            'voting_starts' => $form_state->getValue('voting_starts'),
            'deadline_for_voting' => $form_state->getValue('deadline_for_voting'),
            'allow_online_entries' => $form_state->getValue('allow_online_entries'),
            'show_number_of_votes' => $form_state->getValue('show_number_of_votes'),
            'description' => $form_state->getValue('description')['value'],
        ];
        ContestStorage::update($fields);
        
        // Redirect to contest list
        drupal_set_message($this->t('Contest sucessfully updated.'));
        $form_state->setRedirect('engagement.contest_admin.list');
        return;
    }
}
