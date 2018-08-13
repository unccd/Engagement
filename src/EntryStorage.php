<?php

namespace Drupal\unccd_engagement;

/**
 * Class EntryStorage.
 */
class EntryStorage {

    /**
     * Insert a new contest entry into the database.
     *
     * @param array $entry An array containing all the fields of the database record.
     * @return int The number of updated rows.
     */
    public static function insert(array $entry) {
        $return_value = NULL;
        $entry['date'] = date('Y-m-d H:i:s');
        $return_value = db_insert('unccd_contest_entries')
            ->fields($entry)
            ->execute();
        return $return_value;
    }

    /**
     * Update an entry already in the databasse.
     *
     * @param array $entry An array containing all the fields of the item to be updated.
     * @return int The number of updated rows.
     */
    public static function update(array $entry) {
        $count = db_update('unccd_contest_entries')
            ->fields($entry)
            ->condition('id', $entry['id'])
            ->execute();
        return $count;
    }

    /**
     * Delete a contest entry from the database.
     *
     * @param int $id The id of the entry to delete
     * @see db_delete()
     */
    public static function delete($id) {
        db_delete('unccd_contest_entries')
            ->condition('id', $id)
            ->execute();
    }

    public static function loadById($id) {
        $select = db_select('unccd_contest_entries', 'entry');
        $select->fields('entry');
        $select->condition('id', $id);
        return $select->execute()->fetch();
    }

    /**
     * Retrieve all the entries in the database.
     *
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAll() {
        $select = db_select('unccd_contest_entries', 'entry');
        $select->fields('entry');
        return $select->execute()->fetchAll();
    }

    /**
     * Retrieve all the entries for a contest
     *
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAllInContest($contest_id) {
        $select = db_select('unccd_contest_entries', 'entry');
        $select->fields('entry');
        $select->condition('contest_id', $contest_id);
        return $select->execute()->fetchAll();
    }


    /**
     * Retrieve all the entries in the database paginated.
     * 
     * @param int $page The page to show
     * @return object An object containing the loaded entries if found.
     */
    public static function loadAllPaginated($page = 1) {
        $start = ($page - 1) * 50;
        $end = 50 * $page;
        $select = db_select('unccd_contest_entries', 'entry');
        $select->fields('entry');
        $select->orderBy('entry.id', 'DESC');
        $select->range($start, $end);
        return $select->execute()->fetchAll();
    }
    
    /**
     * Retrieve all of the entries matching provided conditions.
     *
     * @param array $entry An array containing all the fields used to search the entries in the table.
     * @return object An object containing the loaded entries if found.
     */
    public static function loadByCriteria(array $entry = []) {
        $select = db_select('unccd_contest_entries', 'entry');
        $select->fields('entry');

        // Add each field and value as a condition to this query.
        foreach ($entry as $field => $value) {
            $select->condition($field, $value);
        }

        // Return the result in object format.
        return $select->execute()->fetchAll();
    }

    public static function countVotes($contest_id, $entry_id) {
        $select = db_select('unccd_contest_votes');
        $select->condition('contest_id', $contest_id);
        $select->condition('entry_id', $entry_id);
        return intval($select->countQuery()->execute()->fetchField());
    }

}
