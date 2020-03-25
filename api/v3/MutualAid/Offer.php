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
 * MutualAid.Offer API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_mutual_aid_Offer_spec(&$spec)
{
    // TODO: Get from configuration (available/required fields and defaults.
}

/**
 * MutualAid.Offer API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_mutual_aid_Offer($params)
{
    try {
        // Abort when there are no active help types.
        if (count($help_types = CRM_Mutualaid_Settings::getHelpTypes()) == 0) {
            throw new Exception(E::ts('No help types active.'));
        }

        // Set default for help types, if only one is active.
        if (count($help_types = CRM_Mutualaid_Settings::getHelpTypes()) == 1) {
            $params['help_offered'] = array_keys($help_types);
        }

        // Calculate distance in meters.
        $params['max_distance'] *= CRM_Mutualaid_Settings::getDistanceUnit();

        // Resolve custom fields.
        CRM_Mutualaid_Settings::resolveCustomFields($params);

        // Prepare data for XCM: Filter for contact data params.
        $contact_fields = CRM_Mutualaid_Settings::getContactFields();
        $contact_data = array_intersect_key(
            $params,
            array_fill_keys(
                $contact_fields,
                null
            )
        );

        // Identify/create contact using XCM with mutualaid profile.
        $contact_data['xcm_profile'] = 'mutualaid';
        $xcm_result = civicrm_api3('Contact', 'getorcreate', $contact_data);
        if ($xcm_result['is_error']) {
            throw new Exception($xcm_result['error_message']);
        }
        $contact_id = $xcm_result['id'];

        // TODO: Add comment as contact note.
        if (!empty($params['comment'])) {
        }

        // Send confirmation e-mail when configured.
        if ($template_id = CRM_Mutualaid_Settings::get('email_confirmation_template')) {
            $result = civicrm_api3(
                'MessageTemplate',
                'send',
                array(
                    'id' => $template_id,
                    'contact_id' => $contact_id,
                )
            );
        }

        return civicrm_api3_create_success();
    } catch (Exception $exception) {
        return civicrm_api3_create_error($exception->getMessage());
    }
}
