<?php
/*-------------------------------------------------------+
| SYSTOPIA Mutual Aid Extension                          |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
|         J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Mutualaid_ExtensionUtil as E;

/**
 * Provides the matching algorithm
 */
class CRM_Mutualaid_Matcher
{

    /** @var array matching parameters */
    protected $params = null;

    /** @var string database table storing available helpers */
    protected $helper_table = null;

    /**
     * Generate a new matcher object
     *
     * @param array $params
     *   parameters to control the matching algorithm
     */
    public function __construct($params = [])
    {
        // init
        $this->params = $params;
        // TODO: implement

        // do some caching
        CRM_Mutualaid_CustomData::cacheCustomGroups(
          [
            'mutualaid_language',
            'mutualaid_needs_help',
            'mutualaid_offers_help',
          ]
        );
    }

    /**
     * Try to assign all open/unconfirmed requests
     *
     * @param array $params
     *    parameters for the matching algorithm
     *
     * @throws CRM_Core_Exception
     *    if matching is not working atm, possibly a lock issue
     */
    public function assignOpenRequests()
    {
        $lock = new CRM_Core_Lock('mutualaid_matcher', 150, true);
        if (!$lock->acquire()) {
            throw new CRM_Core_Exception('Matching already in progress');
        }

        // store status quo in terms of unconfirmed requests
        $this->storeUnconfirmedRequests();
        $available_helpers_table = $this->getHelperTable();

        // get a list (query) of all open requests
        $requests_sql = $this->getOpenRequestsQuery();
        $request = CRM_Core_DAO::executeQuery($requests_sql);
        while ($request->fetch()) {
            // get the next request
            $request_data = [
              'contact_id' => $request->contact_id,
              'location' => [$request->longitude, $request->latitude],
              'types' => CRM_Utils_Array::explodePadded($request->help_needed),
              'languages' => CRM_Utils_Array::explodePadded($request->languages),
            ];

            // identify potential helpers (apply hard criteria) using SQL query on the helper table
            $potential_helpers = $this->getPotentialHelpers($request_data);

            // score potential helpers (apply soft criteria/scoring) and pick best match
            $helper_data = $this->getBestMatchingHelper($request_data, $potential_helpers);

            if ($helper_data) {
                // there is a helper!
                $this->assignHelper($request_data, $helper_data);
                $this->updateUnconfirmedRequestsWithMatch(
                  $request_data,
                  $helper_data
                );
            } else {
                // sadly, no helper available for this request...
                // TODO: anything to do here?
            }
        }

        // the remaining unconfirmed requests seem no longer valid, and need to be deleted
        $this->removeUnconfirmedRequests();

        // that's it
        $lock->release();
    }

    /**
     * Select/pick the best suited helper for the help request
     *
     * @param array $help_request
     *      help request with fields 'contact_id', 'location', 'types', 'languages'
     *
     * @param array $potential_helpers
     *      list of helper structures, containing the fields 'contact_id', 'location', 'max_distance', 'offers_help'
     *
     * @return array|null best suiting helper
     *      helper structure, containing the fields 'contact_id', 'location', 'max_distance', 'offers_help'
     */
    protected function getBestMatchingHelper($help_request, $potential_helpers)
    {
        $best_helper = null;
        $best_helper_score = -1.0;
        foreach ($potential_helpers as $potential_helper) {
            // some basic checks
            /*if ($help_request['contact_id'] == $potential_helper['contact_id']) {
                continue;
            }*/

            $distance = $this->calculateDistance($help_request['location'], $potential_helper['location']);
            if ($potential_helper['max_distance'] < $distance) {
                continue;
            }

            // score the helper
            $helper_score = 0.0;

            // score 1: total distance
            // TODO: implement

            // score 2: relative distance
            // TODO: implement

            // score 3: help request/offer overlap
            // TODO: implement

            // score 4: helper workload
            // TODO: implement

            if ($helper_score > $best_helper_score) {
                $best_helper_score = $helper_score;
                $best_helper = $potential_helper;
            }
        }

        return $best_helper;
    }

    /**
     * Assign the given helper to the person that requested help
     *
     * @param array $help_request
     *      help request with fields 'contact_id', 'location', 'types', 'languages'
     *
     * @param array $helper
     *      helper structure, containing the fields 'contact_id', 'location', 'max_distance', 'offers_help'
     */
    protected function assignHelper($help_request, $helper)
    {
        // TODO: work with unconfirmed requests, when implemented properly
        // TODO: extend existing, communicated help?

        // create a new relationship
        $new_relationship = [
            'relationship_type_id'         => CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(),
            'contact_id_a'                 => $helper['contact_id'],
            'contact_id_b'                 => $help_request['contact_id'],
            'start_date'                   => date('YmdHis'),
            'mutualaid.help_status'        => 1, // assigned
            'mutualaid.help_type_provided' => array_intersect($helper['offers_help'], $help_request['types'])
        ];
        CRM_Mutualaid_CustomData::resolveCustomFields($new_relationship);
        civicrm_api3('Relationship', 'create', $new_relationship);
    }

    /**
     * Find a list of potential helpers for the given help request
     *
     * @param array $help_request
     *      help request with fields 'contact_id', 'location', 'types', 'languages'
     *
     * @return array
     *      list of helper structures, containing the fields 'contact_id', 'location', 'max_distance', 'offers_help'
     */
    protected function getPotentialHelpers($help_request)
    {
        $potential_helpers = [];

        // generate potential helper query
        $HELPER_TABLE = $this->getHelperTable();
        $LONGITUDE    = $help_request['location'][0];
        $LATITUDE     = $help_request['location'][1];

        // TODO: languages

        // helps matched
        $HELPS_MATCHED = [];
        foreach ($help_request['types'] as $help_type_id ) {
            $help_type_id = (int) $help_type_id;
            $HELPS_MATCHED[] = "offers_help_{$help_type_id}";
        }
        $AT_LEAST_ONE_OFFER_MATCHES = "(" . implode(') OR (', $HELPS_MATCHED). ")";

        // helps offered
        $help_types = CRM_Mutualaid_Settings::getHelpTypes();
        $HELP_OFFERED_SELECT_LIST = [];
        foreach ($help_types as $help_type => $help_name) {
            $help_type_value = (int) $help_type;
            $HELP_OFFERED_SELECT_LIST[] = "IF(offers_help_{$help_type_value}, '{$help_type_value},', '')";
        }
        $HELP_OFFERED = "CONCAT(". implode(', ', $HELP_OFFERED_SELECT_LIST) . ")";


        // build & run query
        $potential_helper_query = "
            SELECT
              contact_id                               AS contact_id,
              max_distance                             AS max_distance,
              ((min_longitude +  max_longitude) / 2.0) AS longitude,
              ((min_latitude + max_latitude) / 2.0)    AS latitude,
              {$HELP_OFFERED}                          AS offers_help
            FROM {$HELPER_TABLE}
            WHERE ({$AT_LEAST_ONE_OFFER_MATCHES})
              AND min_longitude <= {$LONGITUDE} 
              AND max_longitude >= {$LONGITUDE} 
              AND min_latitude  <= {$LATITUDE} 
              AND max_latitude  >= {$LATITUDE} 
              AND open_spots > 0
          ";
        $potential_helper       = CRM_Core_DAO::executeQuery($potential_helper_query);
        while ($potential_helper->fetch()) {
            $potential_helpers[] = [
                'contact_id'   => $potential_helper->contact_id,
                'max_distance' => $potential_helper->max_distance,
                'location'     => [$potential_helper->longitude, $potential_helper->latitude],
                'offers_help'  => explode(',', trim($potential_helper->offers_help, ',)'))
            ];
        }

        return $potential_helpers;
    }

    /**
     * Generate a SQL query to select all open requests,
     *   in order of importance
     *
     * @return string
     *    SQL query
     */
    protected function getOpenRequestsQuery()
    {
        // gather some necessary information
        $HELP_ASSIGNED_ACTIVE_STATUS_LIST = implode(',', CRM_Mutualaid_Settings::getActiveHelpStatusList());
        $HELP_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid_needs_help');
        $HELP_ASSIGNED_TYPE = CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID();
        $HELP_ASSIGNED_STATUS_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid',
            'help_status');
        $HELP_ASSIGNED_STATUS_COLUMN = $HELP_ASSIGNED_STATUS_FIELD['column_name'];
        $HELP_ASSIGNED_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid');
        $HELP_ASSIGNED_TYPES_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid',
            'help_type_provided');
        $HELP_ASSIGNED_TYPES_COLUMN = $HELP_ASSIGNED_TYPES_FIELD['column_name'];

        $HELP_REQUESTED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid_offers_help',
            'mutualaid_help_offered');
        $HELP_NEEDED_COLUMN = $HELP_REQUESTED_FIELD['column_name'];

        // TODO: select languages
        $LANGUAGES_SPOKEN_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid_language');
        $LANGUAGES_SPOKEN_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid_language',
            'mutualaid_languages');
        $LANGUAGES_SPOKEN_COLUMN = $LANGUAGES_SPOKEN_FIELD['column_name'];

        // generate filter criteria
        $help_types = CRM_Mutualaid_Settings::getHelpTypes();
        $REQUESTS_ALREADY_MATCHED_BY_ASSIGNMENTS = [];
        foreach ($help_types as $help_type => $help_name) {
            // each individual clause is true, if $help_type requested AND assigned
            $help_type_value = (int) $help_type;
            $token = "CONCAT(0x01, '{$help_type_value}', 0x01)";
            $REQUESTS_ALREADY_MATCHED_BY_ASSIGNMENTS[] =
                //     help type not requested       OR            help is assigned and already covers requested type
                "(LOCATE({$token}, help_needed) = 0) OR ((help_assigned IS NOT NULL) AND (LOCATE({$token}, help_assigned) > 0))";
        }
        $ALL_REQUESTS_ALREADY_MATCHED_BY_ASSIGNMENTS = '(' . implode(') AND (', $REQUESTS_ALREADY_MATCHED_BY_ASSIGNMENTS) . ')';

        return "
          SELECT
            contact.id                                               AS contact_id,
            CONCAT(help_assigned_data.{$HELP_ASSIGNED_TYPES_COLUMN}) AS help_assigned,
            help_requested.{$HELP_NEEDED_COLUMN}                     AS help_needed,
            languages_spoken.{$LANGUAGES_SPOKEN_COLUMN}              AS languages,
            address.geo_code_1                                       AS latitude,
            address.geo_code_2                                       AS longitude
          FROM civicrm_contact contact
          LEFT JOIN civicrm_address address                   
                 ON address.contact_id = contact.id 
                 AND address.is_primary = 1  
          LEFT JOIN {$HELP_TABLE}  help_requested              
                 ON help_requested.entity_id = contact.id
          LEFT JOIN {$LANGUAGES_SPOKEN_TABLE}  languages_spoken              
                 ON languages_spoken.entity_id = contact.id
          LEFT JOIN civicrm_relationship  help_assigned        
                 ON help_assigned.contact_id_b = contact.id 
                 AND help_assigned.relationship_type_id = {$HELP_ASSIGNED_TYPE}
          LEFT JOIN {$HELP_ASSIGNED_TABLE}  help_assigned_data 
                 ON help_assigned_data.entity_id = help_assigned.id
                 AND help_assigned_data.{$HELP_ASSIGNED_STATUS_COLUMN} IN ({$HELP_ASSIGNED_ACTIVE_STATUS_LIST})
                                   
          WHERE (contact.is_deleted IS NULL OR contact.is_deleted = 0)
            AND address.geo_code_1 IS NOT NULL
            AND address.geo_code_2 IS NOT NULL
            AND help_requested.{$HELP_NEEDED_COLUMN} IS NOT NULL 
            AND help_requested.{$HELP_NEEDED_COLUMN} <> ''
          GROUP BY contact.id
          HAVING NOT ({$ALL_REQUESTS_ALREADY_MATCHED_BY_ASSIGNMENTS})
        ";
    }

    /**
     * Generate/get the table that contains all
     *  helpers with capacity to help
     *
     * @return string
     *    table name
     */
    protected function getHelperTable()
    {
        if (empty($this->helper_table)) {
            // build helper table with
            //  contact_id, latitude, longitude, open spots, help_offered (true/false columns), languages (true/false columns) late

            // gather some necessary information
            $HELP_PROVIDED_ACTIVE_STATUS_LIST = implode(',', CRM_Mutualaid_Settings::getActiveHelpStatusList());
            $MAX_JOBS_FIELD = CRM_Mutualaid_CustomData::getCustomField(
              'mutualaid_offers_help',
              'mutualaid_max_persons');
            $MAX_JOBS_COLUMN = $MAX_JOBS_FIELD['column_name'];
            $MAX_DISTANCE_FIELD = CRM_Mutualaid_CustomData::getCustomField(
                'mutualaid_offers_help',
                'mutualaid_max_distance');
            $MAX_DISTANCE_COLUMN = $MAX_DISTANCE_FIELD['column_name'];

            $HELP_TABLE = CRM_Mutualaid_CustomData::getGroupTable(
              'mutualaid_offers_help'
            );
            $HELP_PROVIDED_TYPE = CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID();
            $HELP_PROVIDED_STATUS = CRM_Mutualaid_CustomData::getCustomField(
              'mutualaid',
              'help_status');
            $HELP_PROVIDED_STATUS_COLUMN = $HELP_PROVIDED_STATUS['column_name'];
            $HELP_PROVIDED_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid');
            $HELP_OFFERED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
              'mutualaid_offers_help',
              'mutualaid_help_offered');
            $HELP_OFFERED_TYPE = $HELP_OFFERED_FIELD['column_name'];

            // build selects for individual help offers
            $help_types = CRM_Mutualaid_Settings::getHelpTypes();
            $HELP_OFFERED_SELECT_LIST = [];
            foreach ($help_types as $help_type => $help_name) {
                $help_type_value = (int)$help_type;
                $token = "CONCAT(0x01, '{$help_type_value}', 0x01)";
                $HELP_OFFERED_SELECT_LIST[] = "(LOCATE({$token}, help_offered.{$HELP_OFFERED_TYPE}) > 0) AS offers_help_{$help_type_value}";
            }
            $HELP_OFFERED_SELECTS = implode(';\n              ', $HELP_OFFERED_SELECT_LIST);
            if (!empty($HELP_OFFERED_SELECTS)) {
              $HELP_OFFERED_SELECTS .= ',';
            }

            // TODO: don't use approximation (min/max factors in meters per degree)
            $LATITUDE_FACTOR  = 111000.1;
            $LONGITUDE_FACTOR = 73000.1;

            // build languages spoken
            $HELP_LANGUAGES_SPOKEN = ''; // TODO

            // compile query to build the table
            $table_query = "
              SELECT
                contact.id                   AS contact_id,
                (help_offered.{$MAX_JOBS_COLUMN} - COUNT(help_provided_data.id))      
                                             AS open_spots,
                help_offered.{$MAX_DISTANCE_COLUMN}      
                                             AS max_distance,
                {$HELP_OFFERED_SELECTS}
                {$HELP_LANGUAGES_SPOKEN}
                (address.geo_code_1 - (help_offered.{$MAX_DISTANCE_COLUMN} / {$LATITUDE_FACTOR}))            
                                             AS min_latitude,
                (address.geo_code_1 + (help_offered.{$MAX_DISTANCE_COLUMN} / {$LATITUDE_FACTOR}))
                                             AS max_latitude,
                (address.geo_code_2 - (help_offered.{$MAX_DISTANCE_COLUMN} / {$LONGITUDE_FACTOR}))           
                                             AS min_longitude,
                (address.geo_code_2 + (help_offered.{$MAX_DISTANCE_COLUMN} / {$LONGITUDE_FACTOR}))           
                                             AS max_longitude
              FROM civicrm_contact contact
              LEFT JOIN civicrm_address address                   
                     ON address.contact_id = contact.id 
                     AND address.is_primary = 1  
              LEFT JOIN {$HELP_TABLE}  help_offered              
                     ON help_offered.entity_id = contact.id
              LEFT JOIN civicrm_relationship  help_provided        
                     ON help_provided.contact_id_b = contact.id 
                     AND help_provided.relationship_type_id = {$HELP_PROVIDED_TYPE}
              LEFT JOIN {$HELP_PROVIDED_TABLE}  help_provided_data 
                     ON help_provided_data.entity_id = help_provided.id
                     AND help_provided_data.{$HELP_PROVIDED_STATUS_COLUMN} IN ({$HELP_PROVIDED_ACTIVE_STATUS_LIST})
              WHERE (contact.is_deleted IS NULL OR contact.is_deleted = 0)
                AND address.geo_code_1 IS NOT NULL
                AND address.geo_code_2 IS NOT NULL
                AND help_offered.{$HELP_OFFERED_TYPE} IS NOT NULL 
                AND help_offered.{$HELP_OFFERED_TYPE} <> ''
              GROUP BY contact.id
              HAVING open_spots > 0
              ";

            // build the table
            $this->helper_table = CRM_Utils_SQL_TempTable::build();
            $this->helper_table->createWithQuery($table_query);

            // add indexes
            $helper_table_name = $this->helper_table->getName();
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX contact_id(contact_id)");
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX max_latitude(max_latitude)");
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX min_latitude(min_latitude)");
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX max_longitude(max_longitude)");
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX min_longitude(min_longitude)");
            foreach ($help_types as $help_type => $help_name) {
                $help_type_value = (int) $help_type;
                CRM_Core_DAO::executeQuery("ALTER TABLE `{$helper_table_name}` ADD INDEX offers_help_{$help_type_value}(offers_help_{$help_type_value})");
            }
        }
        return $this->helper_table->getName();
    }

    /**
     * Calculate the distance in meters between two points
     *
     * @param array $point1
     *    geo coordinates [longitude, latitude]
     * @param array $point2
     *    geo coordinates [longitude, latitude]
     *
     * @return integer
     *    distance in meters
     */
    public static function calculateDistance($point1, $point2)
    {
        // TODO
        return 0.0;
    }

    /**
     * Store the existing unconfirmed requests
     *
     * Part of the synchronisation of unconfirmed requests
     *  (because we don't want them to be deleted and regenerated every time)
     */
    public static function storeUnconfirmedRequests()
    {
        // TODO: implement properly

        // temporary implementation: delete all
        $HELP_PROVIDED_TYPE = CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(
        );
        $HELP_PROVIDED_STATUS = CRM_Mutualaid_CustomData::getCustomField(
          'mutualaid',
          'help_status'
        );
        $HELP_PROVIDED_STATUS_COLUMN = $HELP_PROVIDED_STATUS['column_name'];
        $HELP_PROVIDED_TABLE = CRM_Mutualaid_CustomData::getGroupTable(
          'mutualaid'
        );
        $HELP_PROVIDED_UNCONFIRMED_STATUS_LIST = implode(
          ',',
          CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList()
        );

        $delete_all_query = "
          DELETE help_provided
          FROM civicrm_relationship help_provided
          LEFT JOIN {$HELP_PROVIDED_TABLE} help_provided_data ON help_provided_data.entity_id = help_provided.id
          WHERE help_provided.relationship_type_id = {$HELP_PROVIDED_TYPE}
            AND help_provided_data.{$HELP_PROVIDED_STATUS_COLUMN} IN ({$HELP_PROVIDED_UNCONFIRMED_STATUS_LIST})
       ";
        CRM_Core_DAO::executeQuery($delete_all_query);
    }

    /**
     * A match has been made and the book keeping of the unconfirmed
     *   requests have to be updated
     *
     * Part of the synchronisation of unconfirmed requests
     *  (because we don't want them to be deleted and regenerated every time)
     *
     * @param array $helpee_data
     *   the data of the contact being helped
     * @param array $helper_data
     *   the data of the contact helping
     */
    public static function updateUnconfirmedRequestsWithMatch(
      $helpee_data,
      $helper_data
    ) {
        // TODO: implement
    }

    /**
     * After matching completed, remove the remaining requests
     *
     * Part of the synchronisation of unconfirmed requests
     *  (because we don't want them to be deleted and regenerated every time)
     */
    public static function removeUnconfirmedRequests()
    {
        // TODO: implement
    }
}
