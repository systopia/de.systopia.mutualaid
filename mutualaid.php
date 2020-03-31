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

require_once 'mutualaid.civix.php';

use CRM_Mutualaid_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function mutualaid_civicrm_config(&$config)
{
    _mutualaid_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function mutualaid_civicrm_xmlMenu(&$files)
{
    _mutualaid_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function mutualaid_civicrm_install()
{
    _mutualaid_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function mutualaid_civicrm_postInstall()
{
    _mutualaid_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function mutualaid_civicrm_uninstall()
{
    _mutualaid_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function mutualaid_civicrm_enable()
{
    _mutualaid_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function mutualaid_civicrm_disable()
{
    _mutualaid_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function mutualaid_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _mutualaid_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function mutualaid_civicrm_managed(&$entities)
{
    _mutualaid_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function mutualaid_civicrm_caseTypes(&$caseTypes)
{
    _mutualaid_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function mutualaid_civicrm_angularModules(&$angularModules)
{
    _mutualaid_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function mutualaid_civicrm_alterSettingsFolders(&$metaDataFolders = null)
{
    _mutualaid_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function mutualaid_civicrm_entityTypes(&$entityTypes)
{
    _mutualaid_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function mutualaid_civicrm_themes(&$themes)
{
    _mutualaid_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
 * function mutualaid_civicrm_preProcess($formName, &$form) {
 *
 * } // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function mutualaid_civicrm_navigationMenu(&$menu)
{
    // add top level node
    $menu[] = [
      'attributes' => [
        'label' => E::ts('Mutual Help'),
        'name' => 'MutualHelp',
        'permission' => 'access CiviCRM',
        'url' => null,
        'icon' => 'crm-i fa-handshake-o',
        'weight' => 100,
        'operator' => '',
        'separator' => null,
        'parentID' => null,
        'active' => "1",
      ],
    ];

    // add reports
    $mutualhelp_unconfirmed_url = CRM_Mutualaid_Upgrader::getReportURL(
      'mutualaid_unconfirmed'
    );
    if ($mutualhelp_unconfirmed_url) {
        _mutualaid_civix_insert_navigation_menu(
          $menu,
          'MutualHelp',
          [
            'label' => E::ts("Unconfirmed Matches"),
            'name' => 'mutualaid_unconfirmed',
            'url' => $mutualhelp_unconfirmed_url,
            'permission' => 'access CiviCRM',
            'icon' => 'crm-i fa-list-alt',
            'operator' => 'OR',
            'separator' => 0,
          ]
        );
    }

    // add form links
    _mutualaid_civix_insert_navigation_menu(
      $menu,
      'MutualHelp',
      [
        'label' => E::ts('Help Offer Form'),
        'name' => 'mutualhelp_help_offer_form',
        'url' => CRM_Utils_System::url(
          'civicrm/mutualaid/offer-help',
          'reset=1'
        ),
        'permission' => 'access CiviCRM',
        'icon' => 'crm-i fa-external-link',
        'operator' => 'OR',
        'separator' => 0,
      ]
    );

    _mutualaid_civix_insert_navigation_menu(
      $menu,
      'MutualHelp',
      [
        'label' => E::ts('Help Request Form'),
        'name' => 'mutualhelp_help_request_form',
        'url' => CRM_Utils_System::url(
          'civicrm/mutualaid/request-help',
          'reset=1'
        ),
        'permission' => 'access CiviCRM',
        'icon' => 'crm-i fa-external-link',
        'operator' => 'OR',
        'separator' => 0,
      ]
    );

    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label' => E::ts('Configuration'),
            'name' => 'mutualhelp_configuration',
            'url' => CRM_Utils_System::url(
                'civicrm/admin/setting/mutualaid',
                'reset=1'
            ),
            'permission' => 'administer CiviCRM',
            'icon' => 'crm-i fa-cog',
            'operator' => 'OR',
            'separator' => 0,
        ]
    );

    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label' => E::ts('Match Now'),
            'name' => 'mutualhelp_configuration',
            'url' => CRM_Utils_System::url('civicrm/mutualaid/matchnow'),
            'permission' => 'access CiviCRM',
            'icon' => 'crm-i fa-users',
            'operator' => 'OR',
            'separator' => 0,
        ]
    );

    _mutualaid_civix_navigationMenu($menu);
}
