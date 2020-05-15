<?php
/*-------------------------------------------------------+
| SYSTOPIA Mutual Aid Extension                          |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: Nicol (@vingle)
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

class CRM_Mutualaid_Page_Dashlet extends CRM_Core_Page
{

    public function run()
    {
        // set title
        CRM_Utils_System::setTitle(E::ts('MutualAid Dashboard'));

        // look up report URLs
        $this->assign('unconfirmed_report_url', CRM_Mutualaid_Upgrader::getReportURL('mutualaid_unconfirmed'));
        $this->assign('issues_report_url', CRM_Mutualaid_Upgrader::getReportURL('mutualaid_issues'));
        parent::run();
    }

}
