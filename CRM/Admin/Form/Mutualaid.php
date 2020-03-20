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
 * Class CRM_Admin_Form_Mutualaid
 */
class CRM_Admin_Form_Mutualaid extends CRM_Admin_Form_Generic
{
  public function buildQuickForm()
  {
    parent::buildQuickForm();

    // Make Terms & Conditions element a WYSIWYG editor.
    $terms_conditions = $this->getElement(E::SHORT_NAME . '_terms_conditions');
    if (!$class_attribute = $terms_conditions->getAttribute('class')) {
      $class_attribute = 'crm-form-wysiwyg';
    }
    $terms_conditions->setAttribute('class', $class_attribute);

    // Add e-mail templates select options.
    $email_confirmation_template = $this->getElement(E::SHORT_NAME . '_email_confirmation_template');
    $templates = civicrm_api3('MessageTemplate', 'get', array(
      'return' => array(
        'id',
        'msg_title',
      ),
    ));
    foreach ($templates['values'] as $template) {
      $email_confirmation_template->_options[$template['id']] = array(
        'text' => $template['msg_title'],
        'attr' => array(
          'value' => $template['id'],
        ),
      );
    }

    // TODO: Add configurtation element for scheduled job frequency.


  }

}
