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
    CRM_Mutualaid_CustomData::cacheCustomGroups(['mutualaid_language', 'mutualaid_needs_help', 'mutualaid_offers_help']);
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
        'location'   => [$request->longitude, $request->latitude],
        'types'      => CRM_Utils_Array::explodePadded($request->type_of_help)
      ];

      // identify potential helpers (apply hard criteria) using SQL query on the helper table
      $potential_helpers = $this->getPotentialHelpers($request_data);

      // score potential helpers (apply soft criteria/scoring) and pick best match
      $helper_data = $this->getBestMatchingHelper($request_data, $potential_helpers);

      if ($helper_data) {
        // there is a helper!
        $this->assignHelper($request_data, $helper_data);
        $this->updateUnconfirmedRequestsWithMatch($request_data, $helper_data);
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
   * Generate a SQL query to select all open requests,
   *   in order of importance
   *
   * @return string
   *    SQL query
   */
  protected function getOpenRequestsQuery()
  {
    // TODO: implement
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
      $MAX_JOBS_FIELD = CRM_Mutualaid_CustomData::getCustomField('mutualaid_offers_help', 'mutualaid_max_persons');
      $MAX_JOBS_COLUMN = $MAX_JOBS_FIELD['column_name'];
      $HELP_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid_offers_help');
      $HELP_PROVIDED_TYPE = CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID();
      $HELP_PROVIDED_STATUS = CRM_Mutualaid_CustomData::getCustomField('mutualaid', 'help_status');
      $HELP_PROVIDED_STATUS_COLUMN = $HELP_PROVIDED_STATUS['column_name'];
      $HELP_PROVIDED_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid');
      $HELP_OFFERED_FIELD = CRM_Mutualaid_CustomData::getCustomField('mutualaid_offers_help', 'mutualaid_help_offered');
      $HELP_OFFERED_TYPE = $HELP_OFFERED_FIELD['column_name'];

      // build selects for individual help offers
      $HELP_OFFERED_SELECTS = ''; // TODO

      // compile query to build the table
      $table_query = "
      SELECT
        contact.id                   AS contact_id,
        (help_offered.{$MAX_JOBS_COLUMN} - COUNT(help_provided_data.id))      
                                     AS open_spots,
        {$HELP_OFFERED_SELECTS}
        address.geo_code_1           AS latitude,
        address.geo_code_2           AS longitude
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
        AND help_provided_data.id IS NOT NULL  -- only count active help_provided relationships
        AND help_offered.{$HELP_OFFERED_TYPE} IS NOT NULL
      GROUP BY contact.id
      HAVING open_spots > 0
      ";

      // build the table
      $this->helper_table = CRM_Utils_SQL_TempTable::build();
      $this->helper_table->createWithQuery($table_query);

      // add indexes
      // TODO

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
    $HELP_PROVIDED_TYPE                    = CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID();
    $HELP_PROVIDED_STATUS                  = CRM_Mutualaid_CustomData::getCustomField('mutualaid', 'help_status');
    $HELP_PROVIDED_STATUS_COLUMN           = $HELP_PROVIDED_STATUS['column_name'];
    $HELP_PROVIDED_TABLE                   = CRM_Mutualaid_CustomData::getGroupTable('mutualaid');
    $HELP_PROVIDED_UNCONFIRMED_STATUS_LIST = implode(',', CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList());

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
  public static function updateUnconfirmedRequestsWithMatch($helpee_data, $helper_data)
  {
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
