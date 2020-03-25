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
     * Retrieves fields available for being made active on forms.
     *
     * @return array
     *   A list of fields that are available as set in the CiviCRM preferences.
     */
    public static function getAvailableFields()
    {
        return array_merge(
            self::getContactFields(),
            array_keys(self::getCustomFieldMapping(false))
        );
    }

    /**
     * Resolves custom fields from extension-internal names.
     *
     * @param $params
     *   The parameters array to resolve parameter keys for.
     */
    public static function resolveCustomFields(&$params)
    {
        foreach (
            self::getCustomFieldMapping(
                false
            ) as $element => $custom_field
        ) {
            if (isset($params[$element])) {
                $params[$custom_field] = $params[$element];
                unset($params[$element]);
            }
        }

        CRM_Mutualaid_CustomData::resolveCustomFields($params);
    }

    /**
     * Retrieves all extension-specific custom fields, optionally resolved to
     * "custom_X" notation.
     *
     * @param $resolve
     *   Whether to resolve to "custom_X" notation or keep extension-internal
     *   names.
     *
     * @return array
     *   An array of custom field names, optionally in "custom_X" notation.
     */
    public static function getCustomFieldMapping($resolve = true)
    {
        $params = array(
            'max_distance' => 'mutualaid_offers_help.mutualaid_max_distance',
            'max_persons' => 'mutualaid_offers_help.mutualaid_max_persons',
            'help_types' => 'mutualaid_offers_help.mutualaid_help_offered',
        );

        if ($resolve) {
            self::resolveCustomFields($params);
        }

        return $params;
    }

    /**
     * Retrieves active fields for the forms to display.
     *
     * @return array
     *   A list of fields activated to be shown on forms, as set in the
     *   extension configuration.
     */
    public static function getFields()
    {
        $available_fields = self::getAvailableFields();

        // TODO: Remove fields not activated in extension configuration.

        return $available_fields;
    }

    /**
     * Retrieves active contact fields from the Core option group.
     *
     * @return array
     *   An array of active individual contact field names.
     */
    public static function getContactFields()
    {
        // Retrieve all available individual contact fields.
        $contact_fields = CRM_Core_BAO_Setting::valueOptions(
            CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
            'contact_edit_options',
            true,
            null,
            false,
            'name',
            false,
            'AND v.filter = 2' // Individual
        );

        // Filter for active individual contact fields.
        $contact_fields = array_keys(array_filter($contact_fields));

        // Copied from CRM_Contact_Form_Edit_Individual::buildQuickForm(),
        // including the comment.
        // Fixme: dear god why? these come out in a format that is NOT the name
        //        of the fields.
        foreach ($contact_fields as &$fix) {
            $fix = str_replace(' ', '_', strtolower($fix));
            if ($fix == 'prefix' || $fix == 'suffix') {
                // God, why god?
                $fix .= '_id';
            }
        }

        // Retrieve all available address fields.
        $address_fields = CRM_Core_BAO_Setting::valueOptions(
            CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
            'address_options'
        );

        // Filter for active address fields.
        $address_fields = array_keys(array_filter($address_fields));

        // Add Pseudo-contact fields for details that XCM can handle.
        $extra_fields = array(
            'email', // "Email" detail entity
            'phone', // "Phone" detail entity for primary phone.
            'phone2', // "Phone" detail entity for secondary phone.
            'url', // "Website" detail entity
        );

        return array_merge(
            $contact_fields,
            array_keys(self::getCustomFieldMapping()),
            $address_fields,
            $extra_fields
        );
    }

    /**
     * Retrieves all languages configured in CiviCRM.
     *
     * @param bool $associate
     *   Whether to return an array with values as keys and labels as values.
     *   If
     *   set to false, all properties of the option values will be returned,
     *   keyed by their ID.
     *
     * @return array
     *   An array of all available languages.
     */
    public static function getLanguages($associate = true)
    {
        $languages = array();
        CRM_Core_OptionValue::getValues(
            array('name' => 'languages'),
            $languages,
            'weight',
            true
        );

        // Return value-label pairs when requested.
        if ($associate) {
            foreach ($languages as $language) {
                $return[$language['value']] = $language['label'];
            }
        } else {
            $return = $languages;
        }

        return $return;
    }

    /**
     * Retrieves all available countries configured in CiviCRM.
     *
     * @return array
     *   An array of all available countries.
     */
    public static function getCountries()
    {
        return CRM_Admin_Form_Setting_Localization::getAvailableCountries();
    }

    /**
     * Retrieves all configured help types from the option group.
     *
     * @param bool $associate
     *   Whether to return an array with values as keys and labels as values.
     *   If
     *   set to false, all properties of the option values will be returned,
     *   keyed by their ID.
     *
     * @return array
     *   An array of all available help types.
     */
    public static function getHelpTypes($associate = true)
    {
        static $help_types = null; // cache result

        if ($help_types === null) {
            $help_types = [];
            CRM_Core_OptionValue::getValues(
                array('name' => 'mutualaid_help_types'),
                $help_types,
                'weight',
                true
            );
        }

        // Return value-label pairs when requested.
        $return = [];
        if ($associate) {
            foreach ($help_types as $help_type) {
                $return[$help_type['value']] = $help_type['label'];
            }
        } else {
            $return = $help_types;
        }

        return $return;
    }

    /**
     * Retrieves the configured distance unit setting.
     *
     * @param bool $label
     *   Whether to return the option label for the setting value.
     *
     * @return mixed
     */
    public static function getDistanceUnit($label = false)
    {
        $setting = Civi::settings()->get(E::SHORT_NAME . '_distance_unit');

        if ($label) {
            $metadata = civicrm_api3(
                'Setting',
                'getfields',
                array(
                    'api_action' => 'get',
                    'name' => 'mutualaid_distance_unit',
                )
            );
            $setting = $metadata['values'][E::SHORT_NAME . '_distance_unit']['options'][$setting];
        }

        return $setting;
    }

    /**
     * Retrieves all extension settings.
     *
     * @return array
     *   An array of extension settings.
     */
    public static function getAll($filter = array())
    {
        $settings = array_filter(
            Civi::settings()->all(),
            function ($setting) {
                return strpos($setting, 'mutualaid_') === 0;
            },
            ARRAY_FILTER_USE_KEY
        );

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
     * Get a list of all help provided status IDs that mean the the help is
     * active
     */
    public static function getActiveHelpStatusList()
    {
        return [2, 3];
    }

    /**
     * Get a list of all help provided status IDs that mean the the help is
     * active
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
        static $relationship_type_id = null;
        if ($relationship_type_id === null) {
            $relationship_type_id = civicrm_api3(
                'RelationshipType',
                'getvalue',
                [
                    'return' => 'id',
                    'name_a_b' => 'mutualaid_provides_for',
                ]
            );
        }
        return (int)$relationship_type_id;
    }
}
