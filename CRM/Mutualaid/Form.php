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
 * Class CRM_Mutualaid_Form
 */
class CRM_Mutualaid_Form extends CRM_Core_Form
{

    /**
     * @var array $element_info
     *   Holds additional information for form elements, such as prefix, suffix,
     *   description, etc.
     */
    protected $element_info = array();

    /**
     *
     */
    public function buildQuickForm()
    {
        // Export form element names and information for template.
        $this->assign('elementNames', $this->getRenderableElementNames());
        $this->assign('elementInfo', $this->element_info);

        $this->assign(
            'terms_conditions',
            CRM_Mutualaid_Settings::get('terms_conditions')
        );

        parent::buildQuickForm();
    }

    /**
     * Retrieves the fields/elements defined in this form.
     *
     * @return string[]
     *   An array of form element names.
     */
    public function getRenderableElementNames()
    {
        // The _elements list includes some items which should not be
        // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
        // items don't have labels.  We'll identify renderable by filtering on
        // the 'label'.
        $elementNames = array();
        foreach ($this->_elements as $element) {
            /** @var HTML_QuickForm_Element $element */
            $label = $element->getLabel();
            if (!empty($label)) {
                $elementNames[] = $element->getName();
            }
        }
        return $elementNames;
    }

    /**
     * Adds elements to the form just as CRM_Core_Form::add() does, with an
     * extra parameter for additional generic information for the element, such
     * as prefix, suffix, description, etc.
     *
     * @param $type
     * @param $name
     * @param string $label
     * @param string $attributes
     * @param bool $required
     * @param null $extra
     * @param array $info
     *   Additional information for this element.
     *
     * @return \HTML_QuickForm_Element
     */
    public function addWithInfo(
        $type,
        $name,
        $label = '',
        $attributes = array(),
        $required = false,
        $extra = array(),
        $info = array()
    ) {
        $this->element_info[$name] = $info;

        // Make all elements "huge".
        if (in_array(
            $type,
            array(
                'select',
                'select2',
            )
        )) {
            $attr_to_alter = &$extra;
        } elseif (!in_array(
            $type,
            array(
                'checkbox',
                'radio',
            )
        )) {
            $attr_to_alter = &$attributes;
        }
        $classes = explode(
            ' ',
            (isset($attr_to_alter['class']) ? $attr_to_alter['class'] : '')
        );
        if (!in_array('huge', $classes)) {
            $classes[] = 'huge';
            $attr_to_alter['class'] = implode(' ', array_filter($classes));
        }

        return $this->add($type, $name, $label, $attributes, $required, $extra);
    }

    /**
     * Adds contact form fields to the form.
     */
    public function addContactFormFields()
    {
        $active_contact_fields = CRM_Mutualaid_Settings::getContactFields(
            false,
            true
        );
        foreach (
            $active_contact_fields as $field_name => $field_label
        ) {
            $required = CRM_Mutualaid_Settings::get($field_name . '_required');
            switch ($field_name) {
                case 'country':
                    // Add country field with option values.
                    $this->addWithInfo(
                        'select',
                        'country',
                        E::ts($field_label),
                        CRM_Mutualaid_Settings::getCountries(),
                        $required,
                        array(
                            'class' => 'crm-select2 crm-form-select2 huge',
                        )
                    );
                    break;
                case 'state_province':
                    // Add state/province field with option values for default
                    // country, but only if country field is not active.
                    if (!array_key_exists('country', $active_contact_fields)
                        && $default_country = CRM_Mutualaid_Settings::get(
                            'country_default'
                        )) {
                        $this->addWithInfo(
                            'select',
                            'state_province',
                            E::ts($field_label),
                            CRM_Mutualaid_Settings::getStateProvinces($default_country),
                            $required,
                            array(
                                'class' => 'crm-select2 crm-form-select2 huge',
                            )
                        );
                    }
                    break;
                case 'county':
                    // Add county field and option values for default
                    // state/province, but only if state/province and country
                    // fields are not active.
                    if (!array_key_exists('country', $active_contact_fields)
                        && !array_key_exists(
                            'state_province',
                            $active_contact_fields
                        )
                        && $default_state_province = CRM_Mutualaid_Settings::get(
                            'state_province_default'
                        )) {
                        $this->addWithInfo(
                            'select',
                            'county',
                            E::ts($field_label),
                            CRM_Mutualaid_Settings::getCounties($default_state_province),
                            $required,
                            array(
                                'class' => 'crm-select2 crm-form-select2 huge',
                            )
                        );
                    }
                    break;

                case 'prefix_id':
                case 'suffix_id':
                    $options = array();
                    if (!$required) {
                        $options[''] = E::ts('- None -');
                    }
                    $options += CRM_Contact_BAO_Contact::buildOptions(
                        $field_name
                    );
                    $this->addWithInfo(
                        'select',
                        $field_name,
                        E::ts($field_label),
                        $options,
                        $required,
                        array(
                            'class' => 'crm-select2 crm-form-select2 huge',
                        )
                    );
                    break;

                default:
                    $this->addWithInfo(
                        'text',
                        $field_name,
                        E::ts($field_label),
                        array(),
                        $required
                    );
                    break;
            }
        }

        if (CRM_Mutualaid_Settings::get('languages_enabled')) {
            // This will default to self::getDefaultLanguage, even if the field
            // is not added.
            $this->addWithInfo(
                'select',
                'languages',
                E::ts('Languages spoken'),
                CRM_Mutualaid_Settings::getLanguages(),
                false,
                array(
                    'class' => 'crm-select2 crm-form-select2 huge',
                    'multiple' => true,
                )
            );
        }
    }

    /**
     * Sets default values for form elements.
     *
     * @return array|NULL
     */
    public function setDefaultValues()
    {
        $defaults = array(
            'languages' => array(
                self::getDefaultLanguage(),
            ),
        );

        foreach (
            CRM_Mutualaid_Settings::getContactFields(
                false,
                false
            ) as $field_name => $field_label
        ) {
            $defaults[$field_name] = CRM_Mutualaid_Settings::get(
                $field_name . '_default'
            );
        }

        return $defaults;
    }

    /**
     * Processes valid form submissions.
     */
    public function postProcess()
    {
        // Set default value for language, if not set.
        if (empty($this->_submitValues['languages'])) {
            $this->_submitValues['languages'] = array(
                self::getDefaultLanguage(),
            );
        }
    }

    /**
     * Retrieves the system's default language.
     *
     * @return string | null
     *   Either the configured default language, or null, if none is defined.
     */
    public static function getDefaultLanguage()
    {
        if ($default_language = CRM_Core_I18n::getContactDefaultLanguage()) {
            $default_language = substr($default_language, 0, 2);
        }

        return $default_language;
    }
}
