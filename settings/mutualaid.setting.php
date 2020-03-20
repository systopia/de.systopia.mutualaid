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

return array(
  'mutualaid_languages_enabled' => array(
    'name' => 'mutualaid_languages_enabled',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'Radio',
    'title' => E::ts('Languages enabled'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('If enabled, languages spoken will be collected from people offering and requesting help, and taken into account when matching requests with offers.'),
    `settings_pages` => ['mutualaid' => ['weight' => 10]]
  ),
  'mutualaid_distance_unit' => array(
    'name' => 'mutualaid_distance_unit',
    'type' => 'String',
    'default' => 'km',
    'html_type' => 'Select',
    'title' => E::ts('Distance unit'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Unit for distance values, used for collecting max. distance in forms and for calculating proximity.'),
    `settings_pages` => ['mutualaid' => ['weight' => 20]]
  ),
);
