<?php
namespace Drupal\unccd_engagement\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;

use Drupal\unccd_engagement\ContestStorage;
use Drupal\unccd_engagement\EntryStorage;

class ContestController extends ControllerBase {
    /**
    * {@inheritdoc}
    */
    protected function getModuleName() {
        return 'unccd_engagement';
    }

    /**
     * Shows the contest description
     */
    public function view($id) {
        $contest = ContestStorage::loadById($id);

        // Do not cache the content
        // Drupal Bug
        // @see https://www.drupal.org/node/2352009
        \Drupal::service('page_cache_kill_switch')->trigger();

        // If not found, return 404
        if ($contest == null) throw new NotFoundHttpException();

        // If draft, only show with permissions
        if ($contest->status == 0) {
            $user = \Drupal::currentUser();
            if(!$user->hasPermission("unccd manage contests")) throw new NotFoundHttpException();
        }

        // Load entries
        $entries = EntryStorage::loadAllInContest($id);

        foreach($entries as $entry) {
            // Add number of votes
            $entry->votes = EntryStorage::countVotes($id, $entry->id);
           
            // Convert youtube video urls into embeeds
            if($contest->type == "video") {
                if(strpos($entry->attachment, 'youtu') !== false) {
                    $entry->attachment_id = "youtube";
                    $entry->attachment = $this->convertYoutube($entry->attachment);
                } else if (strpos($entry->attachment, 'youku') !== false) {
                    $entry->attachment_id = "youku";
                    $entry->attachment = $this->convertYouku($entry->attachment);
                }
            }
        }

        // Is the contest still open?
        $now = new \DateTime();
        $now->setTime(0,0);
        $deadline_voting = new \DateTime($contest->deadline_for_voting);
        $voting_starts = new \DateTime($contest->voting_starts);
        $deadline_entries = new \DateTime($contest->deadline_for_entries);

        $new_entries_allowed = (($contest->allow_online_entries) && ($deadline_entries >= $now));
        $voting_open = (($voting_starts <= $now) && ($deadline_voting >= $now));

        return [
            '#theme' => 'contest_view',
            '#contest' => $contest,
            '#voting_open' => $voting_open,
            '#new_entries_allowed' => $new_entries_allowed,
            '#entries' => $entries,
            '#cache' => ['max-age' => 0],
        ];
    }

    /**
     * Returns the contest page title
     */
    public function viewTitle($id) {
        $contest = ContestStorage::loadById($id);
        $title = $contest->title;
        if ($contest->status == 0) $title .= " (Draft)";
        return $title;
    }

    /**
     * Vote on a contest
     */
    public function vote($contest_id, $entry_id) {
        $contest = ContestStorage::loadById($contest_id);

        if($contest->status == 0) {
            drupal_set_message(t('Cannot vote on draft contests.'), 'error', TRUE);
            return $this->redirect('engagement.contest.view', ['id' => $contest_id]);
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        // Get number of votes for this ip in contest
        $number_of_votes = ContestStorage::userNumberOfVotes($contest_id, $ip);

        // Check if user can still vote
        if($number_of_votes >= $contest->number_of_votes_allowed) {
            drupal_set_message("You already used your {$contest->number_of_votes_allowed} votes in this contest.", 'error', TRUE);
            return $this->redirect('engagement.contest.view', ['id' => $contest_id]);
        }

        // check no vote for this entry with this ip
        if(ContestStorage::alreadyVotedOnEntry($contest_id, $entry_id, $ip)) {
            drupal_set_message(t('You already voted for this entry.'), 'error', TRUE);
            return $this->redirect('engagement.contest.view', ['id' => $contest_id]);
        }
        
        // add vote
        ContestStorage::addVote($contest_id, $entry_id, $ip);

        // redirect back to contest with messageD
        drupal_set_message(t('Thanks for voting!'), 'status', TRUE);
        return $this->redirect('engagement.contest.view', ['id' => $contest_id]);
    }

    public function enter($id) {
        // Do not cache the content
        // Drupal Bug
        // @see https://www.drupal.org/node/2352009
        \Drupal::service('page_cache_kill_switch')->trigger();
        $form = \Drupal::formBuilder()->getForm('Drupal\unccd_engagement\Form\PublicContestEntryForm', $id);
        return [
            '#theme' => 'contest_form_view',
            '#form' => $form
        ];
    }

    private function convertYoutube($url) {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "$2",
            $url
        );
    }

    private function convertYouku($url) {
        return preg_replace(
            "#(https?:\/\/)?(v|player)\.youku\.com\/(v_show|player\.php|embed)(\/sid)?\/(id_)?(\w+)|(\w+)#",
            "$6",
            $url
        );
    }
}
