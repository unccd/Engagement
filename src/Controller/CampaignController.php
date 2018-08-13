<?php
namespace Drupal\unccd_engagement\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\unccd_engagement\CampaignStorage;

class CampaignController extends ControllerBase {
    /**
    * {@inheritdoc}
    */
    protected function getModuleName() {
        return 'unccd_engagement';
    }

    /**
     * Shows the campaign description
     */
    public function view($id) {
        $campaign = CampaignStorage::loadById($id);

        // Do not cache the content
        // Drupal Bug
        // @see https://www.drupal.org/node/2352009
        \Drupal::service('page_cache_kill_switch')->trigger();

        // If not found, return 404
        if ($campaign == null) throw new NotFoundHttpException();

        // If draft, only show with permissions
        if ($campaign->status == 0) {
            $user = \Drupal::currentUser();
            if(!$user->hasPermission("unccd manage campaigns")) throw new NotFoundHttpException();
        }

        $supporters = CampaignStorage::countSupporters($id);

        return [
            '#theme' => 'campaign_view',
            '#campaign' => $campaign,
            '#supporters' => $supporters,
            '#cache' => ['max-age' => 0],
        ];
    }

    /**
     * Returns the campaign page title
     */
    public function viewTitle($id) {
        $contest = CampaignStorage::loadById($id);
        $title = $contest->title;
        if ($contest->status == 0) $title .= " (Draft)";
        return $title;
    }

    /**
     * Support the campaign
     */
    public function support($id) {
        $campaign = CampaignStorage::loadById($id);

        if($campaign->status == 0) {
            drupal_set_message(t('Cannot support draft campaigns.'), 'error', TRUE);
            return $this->redirect('engagement.campaign.view', ['id' => $id]);
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        // check no signature
        if(CampaignStorage::alreadySupported($id, $ip)) {
            drupal_set_message(t('You already supported this campaign.'), 'status', TRUE);
            return $this->redirect('engagement.campaign.view', ['id' => $id]);
        }
        
        // add support
        CampaignStorage::addSupport($id, $ip);

        // redirect back to campaign with message
        drupal_set_message(t('Thanks for supporting the campaign!'), 'status', TRUE);
        return $this->redirect('engagement.campaign.view', ['id' => $id]);
    }
}
