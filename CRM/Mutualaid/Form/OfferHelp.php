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

    if (count(CRM_Mutualaid_Settings::getHelpTypes()) > 1) {
      // TODO: This needs a default for the "General" option, since it's
      //       required.
      $this->addWithInfo(
        'select',
        'help_types',
        E::ts('I am offering help for'),
        CRM_Mutualaid_Settings::getHelpTypes(),
        true,
        array(
          'class' => 'crm-select2 crm-form-select2 huge',
          'multiple' => 'multiple',
        ),
        array(
          'description' => E::ts('Select what kind of help you are offering.'),
        )
      );
    }
    $this->addWithInfo(
      'text',
      'max_persons',
      E::ts('I am offering help for max.'),
      array(),
      true,
      array(),
      array(
        'field_suffix' => E::ts('persons'),
      )
    );
    $this->addWithInfo(
      'text',
      'max_distance',
      E::ts('I am offering help in a max. proximity of'),
      array(),
      true,
      null,
      array(
        'field_suffix' => CRM_Mutualaid_Settings::get('distance_unit'),
      )
    );

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
      E::ts('I understand and accept the terms and conditions for using this service.'),
      true,
      null,
      array(
        'prefix' => CRM_Mutualaid_Settings::get('terms_conditions'),
      )
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Offer help'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
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
    if (!is_int($values['max_persons']) && !ctype_digit($values['max_persons'])) {
      $this->_errors['max_persons'] = E::ts('Please provide an integer value for the maximum number of persons you would like to offer help for.');
    }

    // Require integer values for max_distance.
    if (!is_numeric($values['max_distance'])) {
        $this->_errors['max_distance'] = E::ts('Please provide a numeric value for the maximum distance you would like to offer help in.');
    }

    return parent::validate();
  }

  /**
   * Processes valid form submissions.
   */
  public function postProcess()
  {
    $values = $this->exportValues();
    $options = $this->getColorOptions();
    CRM_Core_Session::setStatus(E::ts('You picked color "%1"', array(
      1 => $options[$values['favorite_color']],
    )));
    parent::postProcess();
  }
}
