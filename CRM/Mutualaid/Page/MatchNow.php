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

class CRM_Mutualaid_Page_MatchNow extends CRM_Core_Page
{

    public function run()
    {
        // TODO: implement properly
        $timestamp = microtime(true);
        $result = civicrm_api3('MutualAid', 'match', []);
        $runtime =  microtime(true) - $timestamp;

        CRM_Core_Session::setStatus(
            E::ts("Matched %2 help offers to help requests in %1 seconds.", [
                1 => sprintf("%0.3f", $runtime),
                2 => $result['matched']]),
            E::ts("Matching Completed"),
            'info'
        );

        CRM_Utils_System::redirect(CRM_Utils_Array::value('HTTP_REFERER', $_SERVER));
    }

}
