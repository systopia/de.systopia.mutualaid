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

class CRM_Mutualaid_Form_Report_MutualAidMissingData extends CRM_Report_Form
{

    protected $_addressField = false;

    protected $_emailField = false;

    protected $_summary = null;

    protected $_customGroupExtends = array('Individual');
    protected $_customGroupGroupBy = false;

    function __construct()
    {
        $this->_columns     = array(
            'civicrm_contact' => array(
                'dao'      => 'CRM_Contact_DAO_Contact',
                'fields'   => array(
                    'sort_name'  => array(
                        'title'     => E::ts('Contact Name'),
                        'required'  => true,
                        'default'   => true,
                        'no_repeat' => true,
                    ),
                    'mutualaid_issues'  => array(
                        'title'     => E::ts('Issues'),
                        'required'  => true,
                        'default'   => true,
                        'no_repeat' => false,
                        'selected'  => true,
                    ),
                    'id'         => array(
                        'no_display' => true,
                        'required'   => true,
                    ),
                    'first_name' => array(
                        'title'     => E::ts('First Name'),
                        'no_repeat' => true,
                    ),
                    'last_name'  => array(
                        'title'     => E::ts('Last Name'),
                        'no_repeat' => true,
                    ),
                ),
                'filters'  => array(
                    'sort_name' => array(
                        'title'    => E::ts('Contact Name'),
                        'operator' => 'like',
                    ),
                    'id'        => array(
                        'no_display' => true,
                    ),
                ),
                'grouping' => 'contact-fields',
            ),
            'civicrm_address' => array(
                'dao'      => 'CRM_Core_DAO_Address',
                'fields'   => array(
                    'street_address'    => null,
                    'city'              => null,
                    'postal_code'       => null,
                    'state_province_id' => array('title' => E::ts('State/Province')),
                    'country_id'        => array('title' => E::ts('Country')),
                ),
                'grouping' => 'contact-fields',
            ),
            'civicrm_email'   => array(
                'dao'      => 'CRM_Core_DAO_Email',
                'fields'   => array('email' => null),
                'grouping' => 'contact-fields',
            ),
        );
        $this->_groupFilter = true;
        $this->_tagFilter   = true;
        parent::__construct();
    }

    function preProcess()
    {
        $this->assign('reportTitle', E::ts('MutualAid - Missing Data'));
        parent::preProcess();
    }

    function from()
    {
        // fetch some data
        $HELP_REQUESTED_TABLE = CRM_Mutualaid_CustomData::getGroupTable('mutualaid_needs_help');
        $HELP_OFFERED_TABLE   = CRM_Mutualaid_CustomData::getGroupTable('mutualaid_offers_help');

        $this->_from = "
            FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
            LEFT JOIN {$HELP_REQUESTED_TABLE}  help_requested              
                 ON help_requested.entity_id = {$this->_aliases['civicrm_contact']}.id
            LEFT JOIN {$HELP_OFFERED_TABLE}  help_offered              
                     ON help_offered.entity_id = {$this->_aliases['civicrm_contact']}.id
        ";

        $this->joinAddressFromContact();
        $this->joinEmailFromContact();
    }

    /**
     * Add field specific select alterations.
     *
     * @param string $tableName
     * @param string $tableKey
     * @param string $fieldName
     * @param array $field
     *
     * @return string
     */
    function selectClause(&$tableName, $tableKey, &$fieldName, &$field)
    {
        if ($fieldName == 'mutualaid_issues') {
            $alias = "{$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['dbAlias'] = CRM_Utils_Array::value('dbAlias', $field);
            $this->_selectAliases[] = $alias;

            // add the having clause
            $this->_havingClauses[] = "{$alias} <> ''";

            // build the issue list
            $issues = [];

            // generic criteria
            $IS_REQUESTING_AID = $this->getIsRequestingClause();
            $IS_OFFERING_AID   = $this->getIsOfferingClause();
            $IS_MUTUAL_AID     = "({$IS_REQUESTING_AID} OR {$IS_OFFERING_AID})";

            // issue 1: no geocoding
            $address = $this->_aliases['civicrm_address'];
            $issues[] = "IF({$IS_MUTUAL_AID} AND ({$address}.geo_code_1 IS NULL OR {$address}.geo_code_2 IS NULL), 'nocoord,', '')";

            // issue 2: radius is NULL
            $MAX_DISTANCE_FIELD = CRM_Mutualaid_CustomData::getCustomField(
                'mutualaid_offers_help',
                'mutualaid_max_distance');
            $MAX_DISTANCE_COLUMN = $MAX_DISTANCE_FIELD['column_name'];
            $issues[] = "IF({$IS_OFFERING_AID} AND (help_offered.{$MAX_DISTANCE_COLUMN} IS NULL OR help_offered.{$MAX_DISTANCE_COLUMN} < 1), 'max_distance,', '')";

            // issue 3: have a maximum number of helpees of 0
            $MAX_JOBS_FIELD = CRM_Mutualaid_CustomData::getCustomField(
                'mutualaid_offers_help',
                'mutualaid_max_persons');
            $MAX_JOBS_COLUMN = $MAX_JOBS_FIELD['column_name'];
            $issues[] = "IF({$IS_OFFERING_AID} AND (help_offered.{$MAX_JOBS_COLUMN} IS NULL OR help_offered.{$MAX_JOBS_COLUMN} < 1), 'max_jobs,', '')";

            // issue 5: request disabled types
            $HELP_NEEDED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
                'mutualaid_needs_help',
                'mutualaid_help_needed');
            $HELP_NEEDED_COLUMN = $HELP_NEEDED_FIELD['column_name'];
            $offers_disabled_help = $this->getIsHelpDisabled("help_requested.{$HELP_NEEDED_COLUMN}");
            $issues[] = "IF({$IS_REQUESTING_AID} AND ({$offers_disabled_help}), 'requests_disabled,', '')";

            // issue 6: offer disabled types
            $HELP_OFFERED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
                'mutualaid_offers_help',
                'mutualaid_help_offered');
            $HELP_OFFERED_TYPE = $HELP_OFFERED_FIELD['column_name'];
            $offers_disabled_help = $this->getIsHelpDisabled("help_offered.{$HELP_OFFERED_TYPE}");
            $issues[] = "IF({$IS_OFFERING_AID} AND ({$offers_disabled_help}), 'offers_disabled,', '')";

            // compile
            if (count($issues) > 1) {
                $issue_select = "CONCAT(" . implode(', ', $issues) . ") AS {$alias}";
            } else {
                $issue_select = "{$issues[0]} AS {$alias}";
            }

            return $issue_select;
        } else {
            return parent::selectClause($tableName, $tableKey, $fieldName, $field);
        }
    }

    /**
     * generate a clause the evaluates whether the contact is a mutual aid contact
     */
    protected function getIsOfferingClause()
    {
        $HELP_OFFERED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid_offers_help',
            'mutualaid_help_offered');
        $HELP_OFFERED_TYPE = $HELP_OFFERED_FIELD['column_name'];
        return "((help_offered.{$HELP_OFFERED_TYPE} IS NOT NULL) AND (help_offered.{$HELP_OFFERED_TYPE} <> ''))";
    }

    /**
     * generate a clause the evaluates whether the contact is a mutual aid contact
     */
    protected function getIsRequestingClause()
    {
        $HELP_NEEDED_FIELD = CRM_Mutualaid_CustomData::getCustomField(
            'mutualaid_needs_help',
            'mutualaid_help_needed');
        $HELP_NEEDED_COLUMN = $HELP_NEEDED_FIELD['column_name'];
        return "((help_requested.{$HELP_NEEDED_COLUMN} IS NOT NULL) AND (help_requested.{$HELP_NEEDED_COLUMN} <> ''))";
    }

    /**
     * generate a clause the evaluates whether the
     */
    protected function getIsHelpDisabled($help_field_name)
    {
        static $disabled_help_types = null;
        if ($disabled_help_types === null) {
            $disabled_help_types = [];
            $values = civicrm_api3('OptionValue', 'get', [
                'option_group_id' => 'mutualaid_help_types',
                'is_active'       => 0,
                'option.limit'    => 0,
                'return'          => 'value'
            ]);
            foreach ($values['values'] as $value) {
                $disabled_help_types[] = $value['value'];
            }
        }

        if (empty($disabled_help_types)) {
            return 'FALSE';
        } else {
            $clauses = [];
            foreach ($disabled_help_types as $help_type_value) {
                $token = "CONCAT(0x01, '{$help_type_value}', 0x01)";
                $clauses[] = "(LOCATE({$token}, {$help_field_name}) > 0)";
            }
            return '(' . implode(' OR ', $clauses) . ')';
        }
    }



    /**
     * Add field specific where alterations.
     *
     * This can be overridden in reports for special treatment of a field
     *
     * @param array $field Field specifications
     * @param string $op Query operator (not an exact match to sql)
     * @param mixed $value
     * @param float $min
     * @param float $max
     *
     * @return null|string
     */
    public function whereClause(&$field, $op, $value, $min, $max)
    {
        return parent::whereClause($field, $op, $value, $min, $max);
    }

    function alterDisplay(&$rows)
    {
        // custom code to alter rows
        $entryFound = false;
        $checkList  = array();
        foreach ($rows as $rowNum => $row) {
            if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
                // not repeat contact display names if it matches with the one
                // in previous row
                $repeatFound = false;
                foreach ($row as $colName => $colVal) {
                    if (CRM_Utils_Array::value($colName, $checkList) &&
                        is_array($checkList[$colName]) &&
                        in_array($colVal, $checkList[$colName])
                    ) {
                        $rows[$rowNum][$colName] = "";
                        $repeatFound             = true;
                    }
                    if (in_array($colName, $this->_noRepeats)) {
                        $checkList[$colName][] = $colVal;
                    }
                }
            }

            if (array_key_exists('civicrm_contact_mutualaid_issues', $row)) {
                if ($value = $row['civicrm_contact_mutualaid_issues']) {
                    $labeled_issues = [];
                    $issues = explode(',', $value);
                    foreach ($issues as $issue_code) {
                        switch ($issue_code) {
                            case '':
                                continue;

                            case 'nocoord':
                                $labeled_issues[] = E::ts("No geo coordinates");
                                break;
                            case 'max_distance':
                                $labeled_issues[] = E::ts("Maximum help distance empty");
                                break;
                            case 'max_jobs':
                                $labeled_issues[] = E::ts("Maximum help count empty");
                                break;
                            case 'offers_disabled':
                                $labeled_issues[] = E::ts("Offers disabled help types");
                                break;
                            case 'requests_disabled':
                                $labeled_issues[] = E::ts("Requests disabled help types");
                                break;

                            default:
                                $labeled_issues[] = E::ts("Unknown issue '%1'", [1 => $issue_code]);
                        }
                    }


                    $rows[$rowNum]['civicrm_contact_mutualaid_issues'] = implode(', ', $labeled_issues);
                }
                $entryFound = true;
            }

            if (array_key_exists('civicrm_address_state_province_id', $row)) {
                if ($value = $row['civicrm_address_state_province_id']) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince(
                        $value,
                        false
                    );
                }
                $entryFound = true;
            }

            if (array_key_exists('civicrm_address_country_id', $row)) {
                if ($value = $row['civicrm_address_country_id']) {
                    $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, false);
                }
                $entryFound = true;
            }

            if (array_key_exists('civicrm_contact_sort_name', $row) &&
                $rows[$rowNum]['civicrm_contact_sort_name'] &&
                array_key_exists('civicrm_contact_id', $row)
            ) {
                $url                                              = CRM_Utils_System::url(
                    "civicrm/contact/view",
                    'reset=1&cid=' . $row['civicrm_contact_id'],
                    $this->_absoluteUrl
                );
                $rows[$rowNum]['civicrm_contact_sort_name_link']  = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] = E::ts("View Contact Summary for this Contact.");
                $entryFound                                       = true;
            }

            if (!$entryFound) {
                break;
            }
        }
    }

}
