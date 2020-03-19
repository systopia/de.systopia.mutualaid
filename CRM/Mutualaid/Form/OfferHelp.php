<?php

use CRM_Mutualaid_ExtensionUtil as E;

/**
 * Form controller class
 */
class CRM_Mutualaid_Form_OfferHelp extends CRM_Mutualaid_Form
{
  public function buildQuickForm()
  {
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
      E::ts('I am offering help for max. persons'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'max_distance',
      E::ts('I am offering help in a max. proximity of'),
      array(),
      true,
      null,
      array(
        'suffix' => CRM_Mutualaid_Settings::get('distance_unit'),
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
