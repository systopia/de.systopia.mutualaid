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
 * Form controller class
 */
class CRM_Mutualaid_Form_OfferHelp extends CRM_Mutualaid_Form
{

    public function buildQuickForm()
    {
        $this->setTitle(E::ts('Offer Help'));

        // Add contact form fields.
        $this->addContactFormFields();

        if (
            CRM_Mutualaid_Settings::get('help_offered_enabled')
            && count(CRM_Mutualaid_Settings::getHelpTypes()) > 1
        ) {
            $this->addWithInfo(
              'select',
              'help_offered',
              E::ts('I am offering help for'),
              CRM_Mutualaid_Settings::getHelpTypes(),
              CRM_Mutualaid_Settings::get('help_offered_required'),
              array(
                'class' => 'crm-select2 crm-form-select2 huge',
                'multiple' => 'multiple',
              ),
              array(
                'description' => E::ts(
                  'Select what kind of help you are offering.'
                ),
              )
            );
        }

        if (CRM_Mutualaid_Settings::get('max_persons_enabled')) {
            $this->addWithInfo(
                'text',
                'max_persons',
                E::ts('I am offering help for max.'),
                array(),
                CRM_Mutualaid_Settings::get('max_persons_required'),
                array(),
                array(
                    'field_suffix' => E::ts('persons'),
                )
            );
        }

        if (CRM_Mutualaid_Settings::get('max_distance_enabled')) {
            $this->addWithInfo(
                'text',
                'max_distance',
                E::ts('I am offering help in a max. proximity of'),
                array(),
                CRM_Mutualaid_Settings::get('max_distance_required'),
                null,
                array(
                    'field_suffix' => CRM_Mutualaid_Settings::getDistanceUnit(true),
                )
            );
        }

        if (CRM_Mutualaid_Settings::get('comments_enabled')) {
            $this->addWithInfo(
              'textarea',
              'comment',
              E::ts('Notes/Comments')
            );
        }

        $this->addWithInfo(
          'checkbox',
          'terms_conditions_consent',
          E::ts('Terms and Conditions'),
          E::ts(
            'I understand and accept the terms and conditions for using this service.'
          ),
          true,
          null,
          array(
            'prefix' => CRM_Mutualaid_Settings::get('terms_conditions'),
          )
        );

        $this->addButtons(
          array(
            array(
              'type' => 'submit',
              'name' => E::ts('Offer help'),
              'isDefault' => true,
            ),
          )
        );

        parent::buildQuickForm();
    }

    /**
     * Sets default values for form elements.
     *
     * @return array|NULL
     */
    public function setDefaultValues()
    {
        $defaults = parent::setDefaultValues();

        // Set default values from settings.
        foreach (
            CRM_Mutualaid_Settings::getContactCustomFields(
                true,
                false,
                'mutualaid_offers_help'
            ) as $field_name
        ) {
            $default_value = CRM_Mutualaid_Settings::get($field_name . '_default');

            switch ($field_name) {
                // Convert distance into configured unit.
                case 'max_distance':
                    $default_value /= CRM_Mutualaid_Settings::get('distance_unit');
                    break;
            }

            $defaults[$field_name] = $default_value;
        }

        return $defaults;
    }

    /**
     * Validates form values.
     *
     * @return bool
     *   Whether the form validates.
     */
    public function validate()
    {
        $values = $this->exportValues();

        // Require integer values for max_persons.
        if (!is_int($values['max_persons']) && !ctype_digit(
            $values['max_persons']
          )) {
            $this->_errors['max_persons'] = E::ts(
              'Please provide an integer value for the maximum number of persons you would like to offer help for.'
            );
        }

        // Require integer values for max_distance.
        if (!is_numeric($values['max_distance'])) {
            $this->_errors['max_distance'] = E::ts(
              'Please provide a numeric value for the maximum distance you would like to offer help in.'
            );
        }

        return parent::validate();
    }

    /**
     * Processes valid form submissions.
     */
    public function postProcess()
    {
        parent::postProcess();

        $fields = CRM_Mutualaid_Settings::getFields(true, false);

        // Fetch and filter form values.
        $values = $this->exportValues(null, true);
        $values = array_intersect_key($values, array_fill_keys($fields, null));

        $result = civicrm_api3('MutualAid', 'Offer', $values);

        $session = CRM_Core_Session::singleton();
        $session->setStatus(E::ts('Your offer has been submitted. You will receive an e-mail confirmation shortly.'));
    }
}
