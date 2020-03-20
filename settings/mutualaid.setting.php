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
  E::SHORT_NAME . '_languages_enabled' => array(
    'name' => E::SHORT_NAME . '_languages_enabled',
    'type' => 'Boolean',
    'default' => FALSE,
    'html_type' => 'radio',
    'quick_form_type' => 'YesNo',
    'title' => E::ts('Languages enabled'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('If enabled, languages spoken will be collected from people offering and requesting help, and taken into account when matching requests with offers.'),
    'settings_pages' => ['mutualaid' => ['weight' => 10]]
  ),
  E::SHORT_NAME . '_comments_enabled' => array(
    'name' => E::SHORT_NAME . '_comments_enabled',
    'type' => 'Boolean',
    'default' => FALSE,
    'html_type' => 'radio',
    'quick_form_type' => 'YesNo',
    'title' => E::ts('Comments enabled'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('If enabled, comments will be collected from people offering and requesting help.'),
    'settings_pages' => ['mutualaid' => ['weight' => 20]]
  ),
  E::SHORT_NAME . '_distance_unit' => array(
    'name' => E::SHORT_NAME . '_distance_unit',
    'type' => 'String',
    'default' => 'km',
    'html_type' => 'select',
    'options' => array(
      'km' => E::ts('Kilometers'),
      'mi' => E::ts('Miles')
    ),
    'title' => E::ts('Distance unit'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Unit for distance values, used for collecting max. distance in forms and for calculating proximity.'),
    'settings_pages' => ['mutualaid' => ['weight' => 30]]
  ),
  E::SHORT_NAME . '_terms_conditions' => array(
    'name' => E::SHORT_NAME . '_terms_conditions',
    'type' => 'String',
    'default' => FALSE,
    'html_type' => 'textarea',
    'title' => E::ts('Terms & Conditions'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('HTML content containing Terms and Conditions to display on forms.'),
    'settings_pages' => ['mutualaid' => ['weight' => 40]]
  ),
  E::SHORT_NAME . '_email_confirmation_template' => array(
    'name' => E::SHORT_NAME . '_email_confirmation_template',
    'type' => 'Integer',
    'default' => 0,
    'html_type' => 'select',
    'title' => E::ts('E-Mail Confirmation Template'),
    'options' => array(
      0 => E::ts('- Do not send confirmation e-mails -'),
    ),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('HTML content containing Terms and Conditions to display on forms.'),
    'settings_pages' => ['mutualaid' => ['weight' => 50]]
  ),
  E::SHORT_NAME . '_max_helpers' => array(
    'name' => E::SHORT_NAME . '_max_helpers',
    'type' => 'Integer',
    'default' => 1,
    'html_type' => 'text',
    'title' => E::ts('Maximum helpers per requesting person'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Maximum number of persons providing help per person requesting help.'),
    'settings_pages' => ['mutualaid' => ['weight' => 70]]
  ),
  E::SHORT_NAME . '_matching_weight_help_types' => array(
    'name' => E::SHORT_NAME . '_matching_weight_help_types',
    'type' => 'Integer',
    'default' => 1,
    'html_type' => 'text',
    'title' => E::ts('Matching Weight: Help Types'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Weight for matching help types when calculating matches between persons.'),
    'settings_pages' => ['mutualaid' => ['weight' => 80]]
  ),
  E::SHORT_NAME . '_matching_weight_distance' => array(
    'name' => E::SHORT_NAME . '_matching_weight_distance',
    'type' => 'Integer',
    'default' => 1,
    'html_type' => 'text',
    'title' => E::ts('Matching Weight: Distance'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Weight for distance when calculating matches between persons.'),
    'settings_pages' => ['mutualaid' => ['weight' => 90]]
  ),
  E::SHORT_NAME . '_matching_weight_workload' => array(
    'name' => E::SHORT_NAME . '_matching_weight_workload',
    'type' => 'Integer',
    'default' => 1,
    'html_type' => 'text',
    'title' => E::ts('Matching Weight: Workload'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Weight for workload of persons providing help when calculating matches between persons.'),
    'settings_pages' => ['mutualaid' => ['weight' => 100]]
  ),
  E::SHORT_NAME . '_matching_weight_small_distance_preference' => array(
    'name' => E::SHORT_NAME . '_matching_weight_small_distance_preference',
    'type' => 'Integer',
    'default' => 1,
    'html_type' => 'text',
    'title' => E::ts('Matching Weight: Small Distance Preference'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Weight for preference of persons providing help in small distances when calculating matches between persons.'),
    'settings_pages' => ['mutualaid' => ['weight' => 110]]
  ),
);
