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
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function mutualaid_civicrm_install()
{
    _mutualaid_civix_civicrm_install();
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
            'label'      => E::ts('Mutual Help'),
            'name'       => 'MutualHelp',
            'permission' => 'administer MutualAid',
            'url'        => null,
            'icon'       => 'crm-i fa-handshake-o',
            'weight'     => 100,
            'operator'   => '',
            'separator'  => null,
            'parentID'   => null,
            'active'     => "1",
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
                'label'      => E::ts("Unconfirmed Matches"),
                'name'       => 'mutualaid_unconfirmed',
                'url'        => $mutualhelp_unconfirmed_url,
                'permission' => 'administer MutualAid',
                'icon'       => 'crm-i fa-list-alt',
                'operator'   => 'OR',
                'separator'  => 0,
            ]
        );
    }

    // add reports
    $mutualhelp_issues_url = CRM_Mutualaid_Upgrader::getReportURL(
        'mutualaid_issues'
    );
    if ($mutualhelp_issues_url) {
        _mutualaid_civix_insert_navigation_menu(
            $menu,
            'MutualHelp',
            [
                'label'      => E::ts("Matching Issues"),
                'name'       => 'mutualaid_issues',
                'url'        => $mutualhelp_issues_url,
                'permission' => 'access CiviCRM',
                'icon'       => 'crm-i fa-exclamation-triangle',
                'operator'   => 'OR',
                'separator'  => 0,
            ]
        );
    }

    // add form links
    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label'      => E::ts('Help Offer Form'),
            'name'       => 'mutualhelp_help_offer_form',
            'url'        => CRM_Utils_System::url(
                'civicrm/mutualaid/offer-help',
                'reset=1'
            ),
            'permission' => 'offer help',
            'icon'       => 'crm-i fa-external-link',
            'operator'   => 'OR',
            'separator'  => 0,
        ]
    );

    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label'      => E::ts('Help Request Form'),
            'name'       => 'mutualhelp_help_request_form',
            'url'        => CRM_Utils_System::url(
                'civicrm/mutualaid/request-help',
                'reset=1'
            ),
            'permission' => 'request help',
            'icon'       => 'crm-i fa-external-link',
            'operator'   => 'OR',
            'separator'  => 0,
        ]
    );

    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label'      => E::ts('Configuration'),
            'name'       => 'mutualhelp_configuration',
            'url'        => CRM_Utils_System::url(
                'civicrm/admin/setting/mutualaid',
                'reset=1'
            ),
            'permission' => 'administer MutualAid',
            'icon'       => 'crm-i fa-cog',
            'operator'   => 'OR',
            'separator'  => 0,
        ]
    );

    _mutualaid_civix_insert_navigation_menu(
        $menu,
        'MutualHelp',
        [
            'label'      => E::ts('Match Now'),
            'name'       => 'mutualhelp_configuration',
            'url'        => CRM_Utils_System::url('civicrm/mutualaid/matchnow'),
            'permission' => 'administer MutualAid',
            'icon'       => 'crm-i fa-users',
            'operator'   => 'OR',
            'separator'  => 0,
        ]
    );

    _mutualaid_civix_navigationMenu($menu);
}

function mutualaid_civicrm_permission(&$permissions)
{
    $prefix = E::ts('MutualAid') . ': ';

    $permissions += [
        'administer MutualAid' => [
            $prefix . E::ts('Administer MutualAid'),
            E::ts('Grants necessary permissions for administering MutualAid extension'),
        ],
    ];
    $permissions += [
        'request help' => [
            $prefix . E::ts('Request help'),
            E::ts('Request help via MutualAid extension'),
        ],
    ];
    $permissions += [
        'offer help' => [
            $prefix . E::ts('Offer help'),
            E::ts('Offer help for others via MutualAid extension'),
        ],
    ];
}
