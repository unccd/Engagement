<?php

namespace Drupal\unccd_engagement\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\unccd_engagement\ContestStorage;
use Drupal\unccd_engagement\EntryStorage;

/**
 * The admin panel pages to manage contests
 */
class ContestAdminController extends ControllerBase {

    /**
     * Lists the contests
     *
     * @return array A render array as expected by the renderer.
     */
    public function contestList() {
        $headers = [
            $this->t('ID'),
            $this->t('Title'),
            $this->t('Status'),
            $this->t('Votes'),
            $this->t('Operations'),
        ];

        $rows = [];
        $contests = ContestStorage::loadAll();

        foreach ($contests as $contest) {
            $row['id'] = [
                'data' => $contest->id,
                'class' => 'table-filter-text-source',
            ];

            $row['title'] = [
                'data' => $contest->title,
                'class' => 'table-filter-text-source',
            ];

            $row['status'] = [
                'data' => (($contest->status == 1) ? "Live" : "Draft"),
                'class' => 'table-filter-text-source',
            ];

            $row['votes'] = [
                'data' => ContestStorage::countVotes($contest->id),
                'class' => 'table-filter-text-source',
            ];

            $operations = [];

            $operations['view'] = [
                'title' => $this->t('View'),
                'url' => Url::fromRoute('engagement.contest.view', ['id' => $contest->id]),
            ];

            $operations['manage_entries'] = [
                'title' => $this->t('Manage Entries'),
                'url' => Url::fromRoute('engagement.contest_entry_admin.list', ['contest_id' => $contest->id]),
            ];

            $operations['edit'] = [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('engagement.contest_admin.edit', ['id' => $contest->id]),
            ];

            $operations['delete'] = [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('engagement.contest_admin.delete', ['id' => $contest->id]),
            ];

            $row['operations']['data'] = [
                '#type' => 'operations',
                '#links' => $operations,
            ];

            $rows[$contest->id] = $row;
        }

        $output['services'] = [
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
            '#empty' => $this->t('No contests found.'),
            '#sticky' => TRUE,
        ];

        return $output;
    }

    /**
     * Deletes a contest
     */
    public function deleteContest($id) {
        ContestStorage::delete($id);
        drupal_set_message(t('Contest successfully deleted'), 'status', TRUE);
        return $this->redirect('engagement.contest_admin.list');
    }


    /**
     * Lists the contest entries
     *
     * @return array A render array as expected by the renderer.
     */
    public function contestEntryList($contest_id) {
        $headers = [
            $this->t('ID'),
            $this->t('Title'),
            $this->t('Votes'),
            $this->t('Operations'),
        ];

        $rows = [];
        $entries = EntryStorage::loadAllInContest($contest_id);

        foreach ($entries as $entry) {
            $row['id'] = [
                'data' => $entry->id,
                'class' => 'table-filter-text-source',
            ];

            $row['title'] = [
                'data' => $entry->title,
                'class' => 'table-filter-text-source',
            ];

            $row['votes'] = [
                'data' => EntryStorage::countVotes($contest_id, $entry->id),
                'class' => 'table-filter-text-source',
            ];

            $operations = [];

            $operations['edit'] = [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('engagement.contest_entry_admin.edit', ['contest_id' => $contest_id, 'entry_id' => $entry->id]),
            ];

            $operations['delete'] = [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('engagement.contest_entry_admin.delete', ['contest_id' => $contest_id, 'entry_id' => $entry->id]),
            ];

            $row['operations']['data'] = [
                '#type' => 'operations',
                '#links' => $operations,
            ];

            $rows[$entry->id] = $row;
        }

        $output['services'] = [
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
            '#empty' => $this->t('No entries found.'),
            '#sticky' => TRUE,
        ];

        return $output;
    }

    /**
     * Deletes a contest
     */
    public function deleteContestEntry($contest_id, $entry_id) {
        EntryStorage::delete($entry_id);
        drupal_set_message(t('Contest entry successfully deleted'), 'status', TRUE);
        return $this->redirect('engagement.contest_entry_admin.list', ['contest_id' => $contest_id]);
    }

}
