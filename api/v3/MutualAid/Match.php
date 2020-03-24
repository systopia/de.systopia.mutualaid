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

/**
 * Assign the unmatched help requests to the offered help
 */
function civicrm_api3_mutual_aid_match($params)
{
    try {
        $matcher = new CRM_Mutualaid_Matcher($params);
        $matcher->assignOpenRequests();
        $stats = $matcher->getStats();
        //$matcher->cleanup();
        $null = null;
        return civicrm_api3_create_success([], $params, $entity = 'MutualAid', $action = 'match', $null, $stats);
    } catch (Exception $ex) {
        return civicrm_api3_create_error(
          "Matching failed: " . $ex->getMessage()
        );
    }
}

/**
 * API3 action specs
 */
function _civicrm_api3_mutual_aid_match_spec(&$params)
{
    $params['contact_type'] = array(
      'name' => 'contact_type',
      'api.default' => 'Individual',
      'type' => CRM_Utils_Type::T_STRING,
      'title' => 'Contact Type',
    );
    $params['xcm_profile'] = array(
      'name' => 'xcm_profile',
      'api.required' => 0,
      'type' => CRM_Utils_Type::T_STRING,
      'title' => 'Which profile should be used for matching?',
    );
}

