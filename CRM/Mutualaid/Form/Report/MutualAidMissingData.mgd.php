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

return array (
  0 => 
  array (
    'name' => 'CRM_Mutualaid_Form_Report_MutualAidMissingData',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => E::ts('MutualAid - Missing Data'),
      'description' => E::ts('MutualAid: List requests/offers with missing information'),
      'class_name' => 'CRM_Mutualaid_Form_Report_MutualAidMissingData',
      'report_url' => 'de.systopia.mutualaid/mutualaidmissingdata',
      'component' => '',
    ),
  ),
);
