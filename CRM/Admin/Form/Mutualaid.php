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
        $terms_conditions = $this->getElement(
          E::SHORT_NAME . '_terms_conditions'
        );
        if (!$class_attribute = $terms_conditions->getAttribute('class')) {
            $class_attribute = 'crm-form-wysiwyg';
        }
        $terms_conditions->setAttribute('class', $class_attribute);

        // Add e-mail templates select options.
        $email_confirmation_template = $this->getElement(
          E::SHORT_NAME . '_email_confirmation_template'
        );
        $templates = civicrm_api3(
          'MessageTemplate',
          'get',
          array(
            'return' => array(
              'id',
              'msg_title',
            ),
            'options' => array(
              'limit' => 0,
            ),
          )
        );
        foreach ($templates['values'] as $template) {
            $email_confirmation_template->_options[] = array(
              'text' => $template['msg_title'],
              'attr' => array(
                'value' => $template['id'],
              ),
            );
        }

        // TODO: Add configuration element for scheduled job frequency.

        // Add "- None -" option for pseudoconstant fields.
        foreach (array(
            'prefix_id',
            'suffix_id',
                 ) as $pseudoconstant_field) {
            if ($this->elementExists(E::SHORT_NAME . '_' . $pseudoconstant_field . '_default')) {
                $pseudoconstant_element = $this->getElement(
                    E::SHORT_NAME . '_' . $pseudoconstant_field . '_default'
                );
                array_unshift(
                    $pseudoconstant_element->_options,
                    array(
                        'text' => E::ts('- None -'),
                        'attr' => array(
                            'value' => '',
                        ),
                    )
                );
            }
        }

        // Make help type fields multivalue.
        foreach (array(
            'help_offered',
            'help_needed'
                 ) as $help_type_field) {
            $help_type_element = $this->getElement(
                E::SHORT_NAME . '_' . $help_type_field . '_default'
            );
            $class_attribute = $help_type_element->getAttribute('class');
            $classes = array_filter(explode(
                ' ',
                (isset($class_attribute) ? $class_attribute : '')
            ));
            $classes += array_diff(array(
                'crm-select2',
                'crm-form-select2'
            ), $classes);
            $class_attribute = implode(' ', $classes);
            $help_type_element->setAttribute('class', $class_attribute);

            $help_type_element->setAttribute('multiple', 'multiple');
        }
    }

    public function validate()
    {
        $values = $this->exportValues();

        // TODO: Validate setting values:
        //       - Country, State/Province, County interdependencies
        //       - Required default values when field is inactive for:
        //         - max_distance
        //         - personal_contact
        //       - Value format for:
        //         - max_distance
        //         - max_persons

        return parent::validate();
    }

    public function postProcess()
    {
        parent::postProcess();
        $values = $this->exportValues();

        // Store the terms_conditions setting again, since it contains HTML, which
        // is being filtered when savin via the base class, since that uses a
        // Setting.create API call, which encodes input parameters.
        // We're using Civi::settings()->set() instead.
        CRM_Mutualaid_Settings::set(
          'terms_conditions',
          $values[E::SHORT_NAME . '_terms_conditions']
        );
    }

}
