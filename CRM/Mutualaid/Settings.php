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
     * Resolves custom fields from extension-internal names.
     *
     * @param $params
     *   The parameters array to resolve parameter keys for.
     */
    public static function resolveContactCustomFields(&$params)
    {
        foreach (
            self::getContactCustomFieldMapping() as $element => $custom_field
        ) {
            if (isset($params[$element])) {
                $params[$custom_field] = $params[$element];
                unset($params[$element]);
            }
        }

        CRM_Mutualaid_CustomData::resolveCustomFields($params);
    }

    /**
     * Returns all extension-specific custom fields, optionally resolved to
     * "custom_X" notation.
     *
     * @param bool $only_keys
     *   Whether to only return field names (keys) as array values.
     *
     * @param bool $resolve
     *   Whether to resolve to "custom_X" notation or keep extension-internal
     *   names.
     *
     * @return array
     *   An array of custom field names, optionally in "custom_X" notation.
     */
    public static function getContactCustomFields($only_keys = true, $resolve = true, $custom_group = null) {
        $fields = array();
        $resources = self::getContactCustomFieldResources();
        $customData = new CRM_Mutualaid_CustomData(E::LONG_NAME);
        foreach ($resources as $source_file) {
            list(
                $data,
                $customGroup
                ) = $customData->identifyCustomGroup($source_file);
            if (!isset($custom_group) || $customGroup['name'] == $custom_group) {
                foreach ($data['_fields'] as $customFieldSpec) {
                    $customField = $customData->identifyCustomField(
                        $customFieldSpec,
                        $customGroup
                    );
                    // Add custom fields with extension-internal name or in custom_X
                    // notation as keys and labels as values.
                    if ($resolve) {
                        $field_name = $customGroup['name'] . '.' . $customField['name'];
                    }
                    else {
                        $field_name = substr($customField['name'], strlen(E::SHORT_NAME) + 1);
                    }
                    $fields[$field_name] = $customField['label'];
                }
            }
        }

        if ($resolve) {
            self::resolveContactCustomFields($fields);
        }

        if ($only_keys) {
            $fields = array_keys($fields);
        }

        return $fields;
    }

    /**
     * Returns a mapping of extension-specific custom fields, with their
     * extension-internal names as keys and custom_group_name.custom_field_name
     * as their values.
     *
     * @return array
     */
    public static function getContactCustomFieldMapping() {
        // TODO: Read from resource files.
        return array(
            'max_distance' => 'mutualaid_offers_help.mutualaid_max_distance',
            'max_persons' => 'mutualaid_offers_help.mutualaid_max_persons',
            'help_offered' => 'mutualaid_offers_help.mutualaid_help_offered',
            'help_needed' => 'mutualaid_needs_help.mutualaid_help_needed',
            'language' => 'mutualaid_language.mutualaid_languages',
        );
    }

    public static function getContactCustomFieldResources() {
        return array(
            E::path('resources/custom_group_individual_language.json'),
            E::path('resources/custom_group_individual_needs_help.json'),
            E::path('resources/custom_group_individual_offers_help.json'),
        );
    }

    /**
     * Retrieves active fields for the forms to display and process.
     *
     * @param bool $only_keys
     *   Whether to only return field names (keys) as array values.
     *
     * @param bool $resolve_custom_fields
     *   Whether to resolve to "custom_X" notation or keep extension-internal
     *   names.
     *
     * @return array
     *   A list of fields activated to be shown on forms, as set in the
     *   extension configuration.
     */
    public static function getFields($only_keys = true, $resolve_custom_fields = true)
    {
        $available_fields = array_merge(
            self::getContactFields($only_keys),
            self::getContactCustomFields($only_keys, $resolve_custom_fields)
        );

        // TODO: Remove fields not activated in extension configuration.

        return $available_fields;
    }

    /**
     * Retrieves active contact fields from Core preferences.
     *
     * @return array
     *   An array of active individual contact field, address field and extra
     *   field names understood by XCM.
     */
    public static function getContactFields($only_keys = true, $only_active = false)
    {
        // Retrieve all available individual contact fields.
        $contact_fields = CRM_Core_BAO_Setting::valueOptions(
            CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
            'contact_edit_options',
            true,
            null,
            false,
            'name',
            true,
            'AND v.filter = 2' // Individual
        );

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

        // Make field names the keys and labels the values.
        $contact_fields = array_flip($contact_fields);

        // Retrieve all available address fields.
        $address_fields = CRM_Core_BAO_Setting::valueOptions(
            CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
            'address_options',
            true,
            null,
            false,
            'name',
            true
        );

        // Add Pseudo-contact fields for details that XCM can handle.
        $extra_fields = array(
            'email' => E::ts('E-Mail Address'), // "Email" detail entity
            'phone' => E::ts('Primary Phone'), // "Phone" detail entity for primary phone.
            'phone2' => E::ts('Secondary Phone'), // "Phone" detail entity for secondary phone.
            'url' => E::ts('Website'), // "Website" detail entity
        );

        $fields = array_merge(
            $contact_fields,
            $address_fields,
            $extra_fields
        );

        if ($only_active) {
            // Filter for fields activated in extension configuration.
            foreach (array_keys($fields) as $field_name) {
                if (!CRM_Mutualaid_Settings::get($field_name . '_enabled')) {
                    unset($fields[$field_name]);
                }
            }
        }

        if ($only_keys) {
            $fields = array_keys($fields);
        }

        return $fields;
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

    public static function getStateProvinces($country_id = null)
    {
        $state_provinces = array();
        if (!isset($country_id)) {
            $country_id = CRM_Mutualaid_Settings::get('country_default');
        }
        // Default to system default country, if no default is set (this should
        // only happen during settings parsing).
        if (!isset($country_id)) {
            $country_id = Civi::settings()->get('defaultContactCountry');
        }
        if ($country_id) {
            $state_provinces = CRM_Core_PseudoConstant::stateProvinceForCountry($country_id);
        }

        return $state_provinces;
    }

    public static function getCounties($state_province_id = null)
    {
        $counties = array();

        if (!isset($state_province_id)) {
            $state_province_id = CRM_Mutualaid_Settings::get(
                'state_province_default'
            );
        }

        if (isset($state_province_id)) {
            $counties = CRM_Core_PseudoConstant::countyForState($state_province_id);
        }

        return $counties;
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
