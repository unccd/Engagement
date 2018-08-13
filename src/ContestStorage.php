<?php

namespace Drupal\unccd_engagement;

/**
 * Class ContestStorage.
 */
class ContestStorage {

    /**
     * Insert a new contest into the database.
     *
     * @param array $entry An array containing all the fields of the database record.
     * @return int The number of updated rows.
     */
    public static function insert(array $entry) {
        $return_value = NULL;
        $return_value = db_insert('unccd_contests')
            ->fields($entry)
            ->execute();
        return $return_value;
    }

    /**
     * Update a contest already in the databasse.
     *
     * @param array $entry An array containing all the fields of the item to be updated.
     * @return int The number of updated rows.
     */
    public static function update(array $entry) {
        $count = db_update('unccd_contests')
            ->fields($entry)
            ->condition('id', $entry['id'])
            ->execute();
        return $count;
    }

    /**
     * Delete a contest from the database.
     *
     * @param int $id The id of the contest to delete
     * @see db_delete()
     */
    public static function delete($id) {
        db_delete('unccd_contests')
            ->condition('id', $id)
            ->execute();
    }

    public static function loadById($id) {
        $select = db_select('unccd_contests', 'contest');
        $select->fields('contest');
        $select->condition('id', $id);
        return $select->execute()->fetch();
    }

    /**
     * Retrieve all the contests in the database.
     *
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAll() {
        $select = db_select('unccd_contests', 'contest');
        $select->fields('contest');
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
        $select = db_select('unccd_contests', 'contest');
        $select->fields('constest');
        $select->orderBy('contest.id', 'DESC');
        $select->range($start, $end);
        return $select->execute()->fetchAll();
    }
    
    /**
     * Retrieve all of the contests matching provided conditions.
     *
     * @param array $entry An array containing all the fields used to search the entries in the table.
     * @return object An object containing the loaded entries if found.
     */
    public static function loadByCriteria(array $entry = []) {
        $select = db_select('unccd_contests', 'contest');
        $select->fields('contest');

        // Add each field and value as a condition to this query.
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }

        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

    public static function countVotes($id) {
        $select = db_select('unccd_contest_votes');
        $select->condition('contest_id', $id);
        return intval($select->countQuery()->execute()->fetchField());

    }

    public static function alreadyVoted($id, $ip) {
        $select = db_select('unccd_contest_votes', 'vote');
        $select->fields('vote');
        $select->condition('contest_id', $id);
        $select->condition('ip', $ip);
        return (intval($select->countQuery()->execute()->fetchField()) > 0);
    }

    /**
     * Adds vote to the contest
     * 
     * @param int $contest_id ID of the contest
     * @param int $entry_id ID of the entry being voted for
     * @param string $ip IP of the user voting
     * @return int The number of updated rows.
     */
    public static function addVote($contest_id, $entry_id, $ip) {
        $return_value = NULL;
        $return_value = db_insert('unccd_contest_votes')
            ->fields([
                'contest_id' => $contest_id,
                'entry_id' => $entry_id,
                'ip' => $ip,
                'date' => date('Y-m-d H:i:s'),
            ])
            ->execute();
        return $return_value;
    }
}
