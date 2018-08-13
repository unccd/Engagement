<?php

namespace Drupal\unccd_engagement\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\unccd_engagement\CampaignStorage;

/**
 * The admin panel pages to manage campaigns
 */
class CampaignAdminController extends ControllerBase {

    /**
     * Lists the campaigns
     *
     * @return array A render array as expected by the renderer.
     */
    public function campaignList() {
        $headers = [
            $this->t('ID'),
            $this->t('Title'),
            $this->t('Status'),
            $this->t('Supporters'),
            $this->t('Operations'),
        ];

        $rows = [];
        $campaigns = CampaignStorage::loadAll();

        foreach ($campaigns as $campaign) {
            $row['id'] = [
                'data' => $campaign->id,
                'class' => 'table-filter-text-source',
            ];

            $row['title'] = [
                'data' => $campaign->title,
                'class' => 'table-filter-text-source',
            ];

            $row['status'] = [
                'data' => (($campaign->status == 1) ? "Live" : "Draft"),
                'class' => 'table-filter-text-source',
            ];

            $row['supporters'] = [
                'data' => CampaignStorage::countSupporters($campaign->id),
                'class' => 'table-filter-text-source',
            ];

            $operations = [];

            $operations['view'] = [
                'title' => $this->t('View'),
                'url' => Url::fromRoute('engagement.campaign.view', ['id' => $campaign->id]),
            ];

            $operations['edit'] = [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('engagement.campaign_admin.edit', ['id' => $campaign->id]),
            ];

            $operations['delete'] = [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('engagement.campaign_admin.delete', ['id' => $campaign->id]),
            ];

            $row['operations']['data'] = [
                '#type' => 'operations',
                '#links' => $operations,
            ];

            $rows[$campaign->id] = $row;
        }

        $output['services'] = [
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
            '#empty' => $this->t('No campaigns found.'),
            '#sticky' => TRUE,
        ];

        return $output;
    }

    /**
     * Deletes a campaign
     */
    public function deleteCampaign($id) {
        CampaignStorage::delete($id);
        drupal_set_message(t('Campaign successfully deleted'), 'status', TRUE);
        return $this->redirect('engagement.campaign_admin.list');
    }
}
