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
   * Retrieves all languages configured in CiviCRM.
   *
   * @param bool $associate
   *   Whether to return an array with values as keys and labels as values. If
   *   set to false, all properties of the option values will be returned, keyed
   *   by their ID.
   *
   * @return array
   *   An array of all available languages.
   */
  public static function getLanguages($associate = true)
  {
    $help_types = array();
    CRM_Core_OptionValue::getValues(
      array('name' => 'languages'),
      $help_types,
      'weight',
      true
    );

    // Return value-label pairs when requested.
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
    CRM_Core_OptionValue::getValues(
      array('name' => 'mutualaid_help_types'),
      $help_types,
      'weight',
      true
    );

    // Return value-label pairs when requested.
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

  public static function getDistanceUnit()
  {

  }

  /**
   * Retrieves all extension settings.
   *
   * @return array
   *   An array of extension settings.
   */
  public static function getAll($filter = array())
  {
    $settings = array_filter(Civi::settings()->all(), function($setting) {
      return strpos($setting, 'mutualaid_') === 0;
    }, ARRAY_FILTER_USE_KEY);

    return $settings;
  }

  /**
   * Retrieves an extension setting from the CiviCRM settings.
   *
   * @param $setting
   *   The internal name of the setting. This will be prefixed with the
   *   extension's short name for identification within the CiviCRM settings.
   *
   * @return mixed
   *   The value of the requested setting.
   */
  public static function get($setting)
  {
    return Civi::settings()->get(E::SHORT_NAME . '_' . $setting);
  }

  /**
   * Persists an extension setting in the CiviCRM settings.
   *
   * @param $setting
   *   The internal name of the setting. This will be prefixed with the
   *   extension's short name for identification within the CiviCRM settings.
   * @param $value
   *   The value to set the setting to.
   *
   * @return \Civi\Core\SettingsBag
   */
  public static function set($setting, $value)
  {
    return Civi::settings()->set(E::SHORT_NAME . '_' . $setting, $value);
  }

  /**
   * Get a list of all help provided status IDs that mean the the help is active
   */
  public static function getActiveHelpStatusList()
  {
    return [2,3];
  }

  /**
   * Get a list of all help provided status IDs that mean the the help is active
   */
  public static function getUnconfirmedHelpStatusList()
  {
    return [1];
  }
  /**
   * Get the ID of the help provided relationship type ID
   *
   * @return integer
   *   relationship type ID
   *
   * @throws Exception
   *   if the type doesn't exist
   */
  public static function getHelpProvidedRelationshipTypeID()
  {
    static $relationship_type_id = NULL;
    if ($relationship_type_id === NULL) {
      $relationship_type_id = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'id',
        'name_a_b' => 'mutualaid_provides_for']);
    }
    return (int) $relationship_type_id;
  }
}
