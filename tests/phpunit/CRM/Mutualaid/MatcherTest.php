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
class CRM_Mutialaid_MatcherTest extends CRM_Mutualaid_TestBase
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
     * Tests the simples match
     */
  public function testSimplestMatch()
  {
      $help_offer = $this->createHelpOffer(['types' => [1]]);
      $help_request = $this->createHelpRequest(['types' => [1]]);
      $this->runMatcher();

      $this->assertRequestIsMatched($help_request['contact_id'], $help_offer['contact_id'], [1]);
  }

    /**
     * Tests a simple match, but with 100km distance
     */
    public function testLongRangeMatch()
    {
        $help_offer = $this->createHelpOffer(['types' => [1], 'radius' => 100000]);
        $help_request = $this->createHelpRequest(['types' => [1]]);
        $this->runMatcher();

        $this->assertRequestIsMatched($help_request['contact_id'], $help_offer['contact_id'], [1]);
    }
}
