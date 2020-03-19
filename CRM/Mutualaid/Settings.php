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
 * Class CRM_Mutualaid_Settings
 */
class CRM_Mutualaid_Settings
{
  /**
   * Retrieves all configured help types from the option group.
   *
   * @param bool $associate
   *   Whether to return an array with values as keys and labels as values. If
   *   set to false, all properties of the option values will be returned, keyed
   *   by their ID.
   *
   * @return array
   *   An array of all available help types.
   */
  public static function getHelpTypes($associate = true)
  {
    $help_types = array();
    CRM_Core_OptionValue::getValues(array('name' => 'mutualaid_help_types'), $help_types);
    if ($associate) {
      foreach ($help_types as $help_type) {
        $return[$help_type['value']] = $help_type['label'];
      }
    }
    else {
      $return = $help_types;
    }

    return $return;
  }

  /**
   * Retrieves an extension setting from the CiviCRM settings.
   *
   * @param $setting
   *   The name of the setting. This will be prefixed with the extension's short
   *   name.
   *
   * @return mixed
   *   The value of the requested setting.
   */
  public static function get($setting)
  {
    return Civi::settings()->get(E::SHORT_NAME . '_' . $setting);
  }
}
