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

use Civi\Test\Api3TestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

use CRM_Mutualaid_ExtensionUtil as E;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Mutualaid_TestBase extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
    use Api3TestTrait {
        callAPISuccess as protected traitCallAPISuccess;
    }

    /** @var CRM_Core_Transaction current transaction */
    protected $transaction = null;

    // geo_code_1 = latitude, geo_code_2 = longitude
    static $home_location = ['geo_code_1' => 50.9542182, 'geo_code_2' => 6.9094389];

    public function setUpHeadless() {
        // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
        // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
        return \Civi\Test::headless()
            ->install(['de.systopia.xcm'])
            ->installMe(__DIR__)
            ->apply();
    }

    public function setUp() {
        parent::setUp();
        // cleanup (shouldn't be necessary
        CRM_Core_DAO::executeQuery("DELETE FROM civicrm_relationship WHERE relationship_type_id = %1",
                                   [1 => [CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(), 'Integer']]);
        CRM_Core_DAO::executeQuery("DELETE FROM civicrm_address");
        $this->transaction = new CRM_Core_Transaction();
    }

    public function tearDown() {
        $this->transaction->rollback();
        $this->transaction = null;
        parent::tearDown();
    }

    /**
     * Create a new contact
     *
     * @param array $params
     *      various parameters for contact creation
     */
    public function createContact($params = [])
    {
        if (empty($params['first_name'])) {
            $params['first_name'] = sha1(random_bytes(10));
        }
        if (empty($params['last_name'])) {
            $params['last_name'] = sha1(random_bytes(10));
        }
        if (empty($params['contact_type'])) {
            $params['contact_type'] = 'Individual';
        }

        $contact = $this->traitCallAPISuccess('Contact', 'create', $params);
        return $this->traitCallAPISuccess('Contact', 'getsingle', ['id' => $contact['id']]);
    }

    /**
     * Create a new address
     *
     * @param array $params
     *      various parameters for address creation
     */
    public function createAddress($params = []) {
        if (empty($params['contact_id'])) {
            $params['contact_id'] = $this->createContact()['id'];
        }
        if (empty($params['street_address'])) {
            $params['street_address'] = sha1(random_bytes(10));
        }
        if (empty($params['city'])) {
            $params['city'] = sha1(random_bytes(10));
        }
        if (empty($params['postal_code'])) {
            $params['postal_code'] = rand(10000,99999);
        }
        if (empty($params['radius'])) {
            $params['radius'] = 500; //500m
        }

        // geo_code_1 = latitude, geo_code_2 = longitude
        if (empty($params['geo_code_1']) || empty($params['geo_code_2'])) {
            // generate location
            $this->generateLocation($params, self::$home_location, $params['radius']);
        }

        $this->traitCallAPISuccess('Address', 'create', $params);

    }

    /**
     * Create a new help offer
     *
     * @param array $params
     *      various parameters for creation
     */
    public function createHelpOffer($params = [])
    {
        // create contact + address
        if (empty($params['contact_id'])) {
            $params['contact_id'] = $this->createContact($params)['id'];
            $this->createAddress($params);
        }

        // create help offer
        $help_data = [
            'id'                                           => $params['contact_id'],
            'mutualaid_offers_help.mutualaid_help_offered' => CRM_Utils_Array::value('types', $params, [1]),
            'mutualaid_offers_help.mutualaid_max_persons'  => CRM_Utils_Array::value('max_persons', $params, 10),
            'mutualaid_offers_help.mutualaid_max_distance' => CRM_Utils_Array::value('max_distance', $params, 1000),
        ];
        CRM_Mutualaid_CustomData::resolveCustomFields($help_data);
        $this->traitCallAPISuccess('Contact', 'create', $help_data);

        return ['contact_id' => $params['contact_id']];
    }

    /**
     * Create a new help request
     *
     * @param array $params
     *      various parameters for creation
     */
    public function createHelpRequest($params = [])
    {
        // create contact + address
        if (empty($params['contact_id'])) {
            $params['contact_id'] = $this->createContact($params)['id'];
            $this->createAddress($params);
        }

        // create help offer
        $help_data = [
            'id'                                         => $params['contact_id'],
            'mutualaid_needs_help.mutualaid_help_needed' => CRM_Utils_Array::value('types', $params, [1]),
        ];
        CRM_Mutualaid_CustomData::resolveCustomFields($help_data);
        $this->traitCallAPISuccess('Contact', 'create', $help_data);

        return ['contact_id' => $params['contact_id']];
    }

    public function runMatcher()
    {
        $this->traitCallAPISuccess('MutualAid', 'match', []);
    }

    /**
     * Verify that the given help relationship has been established
     *
     * @param integer $help_requested_contact_id
     * @param integer $help_offered_contact_id
     * @param array $help_types
     */
    public function assertRequestIsMatched($help_requested_contact_id, $help_offered_contact_id, $help_types)
    {
        $relationships = $this->traitCallAPISuccess('Relationship', 'get', [
            'contact_id_a'     => $help_offered_contact_id,
            'contact_id_b'     => $help_requested_contact_id,
            'activity_type_id' => CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(),
            'status_id'        => ['IN' => CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList()],
            'option.limit'     => 0,
        ]);
        $this->assertNotEmpty($relationships['count'], "no active help relationship found");
        foreach ($relationships['values'] as $relationship) {
            CRM_Mutualaid_CustomData::labelCustomFields($relationship);
            $relationship_help_type = $relationship['mutualaid.help_type_provided'];
            if (array_values($help_types) == array_values($relationship_help_type)) {
                return;
            }
        }
        $this->fail("no active help relationship with the given help types found");
    }

    /**
     * Generate a new random location based on the given one + radius
     *
     * @param array $params
     *   will store the geocodes here
     *
     * @param array $location
     *  geo_code_1/geo_code_2 location data
     *
     * @param int $radius
     *   radius in meters
     */
    public function generateLocation(&$params, $location, $radius) {
        $radius = (float) $radius;
        $LATITUDE_FACTOR  = 111000.1;
        $LONGITUDE_FACTOR = 73000.1;
        $center = [(float)$location['geo_code_2'], (float)$location['geo_code_1']];
        for ($i = 0; $i < 100; $i++) {
            // try generating a new location
            $test = $center[0] - $radius / $LONGITUDE_FACTOR + (mt_rand() / mt_getrandmax() * 2.0 * $radius / $LONGITUDE_FACTOR);
            $new_location = [
                $center[0] - $radius / $LONGITUDE_FACTOR + (mt_rand() / mt_getrandmax() * 2.0 * $radius / $LONGITUDE_FACTOR),
                $center[1] - $radius / $LATITUDE_FACTOR  + (mt_rand() / mt_getrandmax() * 2.0 * $radius / $LATITUDE_FACTOR),
            ];
            if ($radius > CRM_Mutualaid_Matcher::calculateDistance($center, $new_location)) {
                // this is a good one
                $params['geo_code_1'] = $new_location[1];
                $params['geo_code_2'] = $new_location[0];
                return;
            }
        }
        $this->fail("Failed to create a random point within the radious - check the algorithm!");
    }
}
