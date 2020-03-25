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
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Mutualaid_Form_RequestHelp extends CRM_Mutualaid_Form
{

    public function buildQuickForm()
    {
        $this->setTitle(E::ts('Request Help'));

        // TODO.
        // Add contact form fields.
        $this->addContactFormFields();

        if (count(CRM_Mutualaid_Settings::getHelpTypes()) > 1) {
            // TODO: This needs a default for the "General" option, since it's
            //       required.
            $this->addWithInfo(
              'select',
              'help_needed',
              E::ts('I am requesting help for'),
              CRM_Mutualaid_Settings::getHelpTypes(),
              true,
              array(
                'class' => 'crm-select2 crm-form-select2 huge',
                'multiple' => 'multiple',
              ),
              array(
                'description' => E::ts(
                  'Select what kind of help you are requesting.'
                ),
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
              'name' => E::ts('Request Help'),
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
                'mutualaid_needs_help'
            ) as $field_name
        ) {
            $defaults[$field_name] = CRM_Mutualaid_Settings::get($field_name . '_default');
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
        // Nothing to validate for now.

        return parent::validate();
    }

    public function postProcess()
    {
        parent::postProcess();

        $fields = CRM_Mutualaid_Settings::getFields(true, false);

        // Fetch and filter form values.
        $values = $this->exportValues(null, true);
        $values = array_intersect_key($values, array_fill_keys($fields, null));

        $result = civicrm_api3('MutualAid', 'Request', $values);
    }
}
