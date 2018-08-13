<?php

namespace Drupal\unccd_engagement;

/**
 * Class CampaignStorage.
 */
class CampaignStorage {

    /**
     * Insert a new campaign into the database.
     *
     * @param array $entry An array containing all the fields of the database record.
     * @return int The number of updated rows.
     */
    public static function insert(array $entry) {
        $return_value = NULL;
        $return_value = db_insert('unccd_campaigns')
            ->fields($entry)
            ->execute();
        return $return_value;
    }

    /**
     * Update a campaign already in the databasse.
     *
     * @param array $entry An array containing all the fields of the item to be updated.
     * @return int The number of updated rows.
     */
    public static function update(array $entry) {
        $count = db_update('unccd_campaigns')
            ->fields($entry)
            ->condition('id', $entry['id'])
            ->execute();
        return $count;
    }

    /**
     * Delete a campaign from the database.
     *
     * @param int $id The id of the campaign to delete
     * @see db_delete()
     */
    public static function delete($id) {
        db_delete('unccd_campaigns')
            ->condition('id', $id)
            ->execute();
    }

    public static function loadById($id) {
        $select = db_select('unccd_campaigns', 'campaign');
        $select->fields('campaign');
        $select->condition('id', $id);
        return $select->execute()->fetch();
    }

    /**
     * Retrieve all the campaigns in the database.
     *
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAll() {
        $select = db_select('unccd_campaigns', 'campaign');
        $select->fields('campaign');
        return $select->execute()->fetchAll();
    }

    /**
     * Retrieve all the campaigns in the database paginated.
     * 
     * @param int $page The page to show
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAllPaginated($page = 1) {
        $start = ($page - 1) * 50;
        $end = 50 * $page;
        $select = db_select('unccd_campaigns', 'campaign');
        $select->fields('campaign');
        $select->orderBy('campaign.id', 'DESC');
        $select->range($start, $end);
        return $select->execute()->fetchAll();
    }
    
    /**
     * Retrieve all of the campaigns matching provided conditions.
     *
     * @param array $entry An array containing all the fields used to search the entries in the table.
     * @return object An object containing the loaded entries if found.
     */
    public static function loadByCriteria(array $entry = []) {
        $select = db_select('unccd_campaigns', 'campaign');
        $select->fields('campaign');

        // Add each field and value as a condition to this query.
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }

        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

    public static function countSupporters($id) {
        $select = db_select('unccd_campaign_signatures');
        $select->condition('campaign_id', $id);
        return intval($select->countQuery()->execute()->fetchField());

    }

    public static function alreadySupported($id, $ip) {
        $select = db_select('unccd_campaign_signatures', 'signature');
        $select->fields('signature');
        $select->condition('campaign_id', $id);
        $select->condition('ip', $ip);
        return (intval($select->countQuery()->execute()->fetchField()) > 0);
    }

    /**
     * Adds support for the campaign
     * 
     * @param int $id ID of the campaign
     * @param string $ip IP of the user signing
     * @return int The number of updated rows.
     */
    public static function addSupport($id, $ip) {
        $return_value = NULL;
        $return_value = db_insert('unccd_campaign_signatures')
            ->fields([
                'campaign_id' => $id,
                'ip' => $ip,
                'date' => date('Y-m-d H:i:s'),
            ])
            ->execute();
        return $return_value;
    }
}
