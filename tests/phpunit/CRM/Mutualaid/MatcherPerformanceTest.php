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
 *
 * @group headless
 */
class CRM_Mutialaid_MatcherPerformanceTest extends CRM_Mutualaid_TestBase
{
    use Api3TestTrait {
        callAPISuccess as protected traitCallAPISuccess;
    }


    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests matching 100 help offers to a 100 requests
     */
    public function testSimpleMatchPerformance()
    {
        $timestamp0 = microtime(true);
        foreach (range(1,100) as $idx) {
            $help_offer   = $this->createHelpOffer(['types' => [1]]);
            $help_request = $this->createHelpRequest(['types' => [1]]);
        }
        $timestamp1 = microtime(true);

        printf("Creating 200 contacts took %0.3fs\n", ($timestamp1 - $timestamp0));

        // run matcher
        $this->runMatcher();
        $timestamp2 = microtime(true);
        printf("Matching 100/100 contacts took %0.3fs\n", ($timestamp2 - $timestamp1));

        // show stats
        $match_count = civicrm_api3('Relationship', 'getcount', [
            'activity_type_id' => CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(),
            'status_id'        => ['IN' => CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList()]
        ]);
        printf("%d matches generated\n\n", $match_count);
    }

    /**
     * Tests matching 1000 help offers to one request
     */
    public function testHighDemandPerformance()
    {
        $timestamp0 = microtime(true);
        $help_offer   = $this->createHelpOffer(['types' => [1]]);
        foreach (range(1,1000) as $idx) {
            $help_request = $this->createHelpRequest(['types' => [1]]);
        }
        $timestamp1 = microtime(true);

        printf("Creating 1000 requests took %0.3fs\n", ($timestamp1 - $timestamp0));

        // run matcher
        $this->runMatcher();
        $timestamp2 = microtime(true);
        printf("Matching offer to 1000 requests took %0.3fs\n", ($timestamp2 - $timestamp1));

        // show stats
        $match_count = civicrm_api3('Relationship', 'getcount', [
            'activity_type_id' => CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(),
            'status_id'        => ['IN' => CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList()]
        ]);
        printf("%d matches generated\n\n", $match_count);
    }

    /**
     * Tests matching 1000 help requests to one offer
     */
    public function testHighSupplyPerformance()
    {
        $timestamp0 = microtime(true);
        $help_request = $this->createHelpRequest(['types' => [1]]);
        foreach (range(1,1000) as $idx) {
            $help_offer   = $this->createHelpOffer(['types' => [1]]);
        }
        $timestamp1 = microtime(true);
        printf("Creating 1000 offers took %0.3fs\n", ($timestamp1 - $timestamp0));

        // run matcher
        $this->runMatcher();
        $timestamp2 = microtime(true);
        printf("Matching demand to 1000 offers took %0.3fs\n", ($timestamp2 - $timestamp1));

        // show stats
        $match_count = civicrm_api3('Relationship', 'getcount', [
            'activity_type_id' => CRM_Mutualaid_Settings::getHelpProvidedRelationshipTypeID(),
            'status_id'        => ['IN' => CRM_Mutualaid_Settings::getUnconfirmedHelpStatusList()]
        ]);
        printf("%d matches generated\n\n", $match_count);
    }

}
