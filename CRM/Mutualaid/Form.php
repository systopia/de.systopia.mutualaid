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
  public function buildQuickForm() {
    // Export form element names and information for template.
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('elementInfo', $this->element_info);

    $this->assign('terms_conditions', CRM_Mutualaid_Settings::get('terms_conditions'));

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
   * Adds elements to the form just as CRM_Core_Form::add() does, with an extra
   * parameter for additional generic information for the element, such as
   * prefix, suffix, description, etc.
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
    $required = FALSE,
    $extra = array(),
    $info = array()
  )
  {
    $this->element_info[$name] = $info;

    // Make all elements "huge".
    if (in_array($type, array(
      'select',
      'select2',
    ))) {
      $attr_to_alter = &$extra;
    }
    else {
      $attr_to_alter = &$attributes;
    }
    $classes = explode(' ', (isset($attr_to_alter['class']) ? $attr_to_alter['class'] : ''));
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
    $this->addWithInfo(
      'text',
      'first_name',
      E::ts('First name'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'last_name',
      E::ts('Last name'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'email',
      E::ts('E-Mail Address'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'phone_landline',
      E::ts('Phone (Landline)'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'phone_mobile',
      E::ts('Phone (Mobile)'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'im_whatsapp',
      E::ts('WhatsApp')
    );
    $this->addWithInfo(
      'text',
      'street_address',
      E::ts('Street Address'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'postal_code',
      E::ts('Postal Code'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'city',
      E::ts('City'),
      array(),
      true
    );
    $this->addWithInfo(
      'text',
      'country',
      E::ts('Country'),
      array(),
      false
    );
    if (CRM_Mutualaid_Settings::get('languages_enabled')) {
      // TODO: This can't be required without a default; Take into account to
      //       also match people without language
      $this->addWithInfo(
        'text',
        'languages',
        E::ts('Languages spoken'),
        array(),
        false
      );
    }
  }
}
