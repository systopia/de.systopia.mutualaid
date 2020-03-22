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

  public function __construct()
  {
    // TODO: implement
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
  protected function getOpenRequestsQuery() {
    // TODO: implement
  }

  /**
   * Generate/get the table that contains all
   *  helpers with capacity to help
   *
   * @return string
   *    table name
   */
  protected function getHelperTable() {
    // create indexed table with:
    //  contact_id, latitude, longitude, open spots, help_offered (true/false columns), languages (true/false columns)
    // TODO: implement
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
  public static function calculateDistance($point1, $point2) {
    // TODO
  }
}
