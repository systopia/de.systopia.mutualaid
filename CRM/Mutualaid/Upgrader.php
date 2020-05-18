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
 * Collection of upgrade steps.
 */
class CRM_Mutualaid_Upgrader extends CRM_Mutualaid_Upgrader_Base
{

    /**
     * Create the required custom fields
     */
    public function install()
    {
        $customData = new CRM_Mutualaid_CustomData(E::LONG_NAME);

        $customData->syncOptionGroup(
          E::path('resources/option_group_help_status.json')
        );
        $customData->syncOptionGroup(
          E::path('resources/option_group_help_types.json')
        );

        $customData->syncEntities(E::path('resources/relationship_types.json'));
        $customData->syncCustomGroup(
          E::path('resources/custom_group_relationship_mutualaid.json')
        );

        foreach (CRM_Mutualaid_Settings::getContactCustomFieldResources() as $resource) {
            $customData->syncCustomGroup($resource);
        }
    }

    /**
     * PostInstall: anything to do here?
     */
    public function postInstall()
    {
        // make sure matcher job exists
        $this->installScheduledJob();
    }

    /**
     * On enabling: Do some verification
     */
    public function enable()
    {
        // Make sure the "mutual_help" XCM profile exists.
        $this->installXcmProfile(
          'mutualaid',
          file_get_contents(E::path('resources/xcm_profile_mutualaid.json'))
        );

        // install reports
        $this->installReport(
          'mutualaid_unconfirmed',
          file_get_contents(
            E::path('resources/report_unconfirmed.json')
          )
        );
        $this->installReport(
            'mutualaid_issues',
            file_get_contents(
                E::path('resources/report_issues.json')
            )
        );

        // install dashboard
        $this->installDashboard();

        // finally: run some tests
        $geo_coder = Civi::settings()->get('geoProvider');
        if (empty($geo_coder)) {
            CRM_Core_Session::setStatus(
              E::ts(
                "Your system doesn't have a GeoCoder service configured. A GeoCoder can assign geo coordinates to a given address.<br/>"
              ) .
              E::ts(
                "This is really important for this extension, since it is needed to calculate the distance between two people.<br/>"
              ) .
              E::ts(
                "Please configure your GeoCoder <a href=\"%1\">HERE</a>.<br/>",
                [1 => CRM_Utils_System::url('civicrm/admin/setting/mapping')]
              ) .
              E::ts(
                "If you don't want to use the built-in ones we recommend <a href=\"%1\">OpenStreetMap</a>.<br/>",
                [1 => 'https://github.com/bjendres/de.systopia.osm/releases/latest']
              ),
              E::ts("No GeoCoder configured"),
              'warn'
            );
        }
    }

    /**
     * Disabled: do anything?
     */
    public function disable()
    {
    }



    /*************************************************************************
     *****                           UPGRADER                             ****
     *************************************************************************/

    /**
     * Upgrade to 1.1:
     *   add scheduled job
     *
     * @return TRUE on success
     * @throws Exception
     */
     public function upgrade_0110()
     {
         $this->ctx->log->info('Applying update 0110');

         // make sure matcher job exists
         $this->installScheduledJob();

         return true;
     }

    /**
     * Upgrade to 1.1-1:
     *   rebuild menu
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0111()
    {
        $this->ctx->log->info('Applying update 0111');

        // rebuild menu
        CRM_Core_Invoke::rebuildMenuAndCaches();

        return true;
    }

    /**
     * Upgrade to 1.1-1:
     *   rebuild menu
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0112()
    {
        $this->ctx->log->info('Applying update 0111');

        // rebuild menu to add managed entity
        CRM_Core_Invoke::rebuildMenuAndCaches();

        // add report
        $this->installReport(
            'mutualaid_issues',
            file_get_contents(
                E::path('resources/report_issues.json')
            )
        );

        return true;
    }

    /**
     * Upgrade to 1.2
     *   let users know about permissions
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0120()
    {
        $this->ctx->log->info('Applying update 0120');

        CRM_Core_Session::setStatus(
            E::ts("MutualAid now has separate permissions for submitting help requests/offers and administration. Be sure to grant these permissions the right users/roles, before you continue!"),
            E::ts("Grant Permissions Now!"),
            'warn'
        );

        return true;
    }

    /**
     * Upgrade to 1.2.1
     *   add dashboard
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0121()
    {
        $this->ctx->log->info('Applying update 0121');

        $this->installDashboard();

        return true;
    }



    /*************************************************************************
     *****                       HELPER FUNCTIONS                         ****
     *************************************************************************/

    /**
     * Install a new report (unless already there)
     *
     * @param $name string
     *    report name
     *
     * @param $raw_json_data string
     *    unparsed json data (needs some string replacement)
     */
    protected function installReport($name, $raw_json_data)
    {
        // first: check if already installed
        $existing_reports = civicrm_api3(
          'ReportInstance',
          'get',
          [
            'name' => $name,
            'return' => 'id',
          ]
        );
        if ($existing_reports['count'] > 0) {
            // report already installed
            return;
        }

        // to some ID lookups in the string
        $custom_field_id_help_status = CRM_Mutualaid_CustomData::getCustomField(
          'mutualaid',
          'help_status'
        );
        $raw_json_data = preg_replace(
          '/%%custom_field_id_help_status%%/',
          $custom_field_id_help_status['id'],
          $raw_json_data
        );

        $report_data = json_decode($raw_json_data, true);
        // translations (top-level only)
        foreach (array_keys($report_data) as $key) {
            if (substr($key, 0, 3) == 'ts_') {
                $report_data[substr($key, 3)] = E::ts($report_data[$key]);
                unset($report_data[$key]);
            }
        }
        civicrm_api3('ReportInstance', 'create', $report_data);
    }

    /**
     * Installs the dashboard page as a dashlet
     */
    protected function installDashboard()
    {
        // check if dashboard is installed
        $dashboard_installed = civicrm_api3('Dashboard', 'getcount', ['name' => 'mutualaid_dashboard']);
        if (!$dashboard_installed) {
            // dashboard not installed -> do it now
            civicrm_api3(
                'Dashboard',
                'create',
                [
                    'name'           => 'mutualaid_dashboard',
                    'label'          => E::ts("MutualAid Dashboard"),
                    'url'            => 'civicrm/mutualaid/dashlet',
                    'permission'     => 'access CiviCRM',
                    'fullscreen_url' => 'civicrm/mutualaid/dashlet',
                    'cache_minutes'  => 7200,
                    'is_active'      => 1,
                ]
            );
        }
    }

    /**
     * Installs an XCM profile, if it does not exist.
     *
     * @param $name
     *   The XCM profile name.
     * @param $raw_json_data
     *   The XCM profile data in JSON format.
     */
    protected function installXcmProfile($name, $raw_json_data)
    {
        $profile_list = CRM_Xcm_Configuration::getProfileList();
        if (!isset($profile_list[$name])) {
            // not here? create!
            $profile_data = Civi::settings()->get('xcm_config_profiles');
            $profile = json_decode(
              $raw_json_data,
              true
            );

            // Resolve custom field names.
            foreach (array(
              'fill_fields',
                'override_fields',
                     ) as $fields) {
                $definition = array_flip($profile['options'][$fields]);
                CRM_Mutualaid_CustomData::resolveCustomFields($definition);
                $profile['options'][$fields] = array_flip($definition);
            }

            $profile_data[$name] = $profile;
            Civi::settings()->set('xcm_config_profiles', $profile_data);
        }
    }

    /**
     * Get the URL for a given report name
     *
     * @param $report_name string
     *  name of the report
     *
     * @return string|null
     *  URL of the report, or null if not found
     */
    public static function getReportURL($report_name)
    {
        // look up report
        $existing_reports = civicrm_api3(
          'ReportInstance',
          'get',
          [
            'name' => $report_name,
            'return' => 'id',
          ]
        );

        // return URL
        if (isset($existing_reports['id'])) {
            return CRM_Utils_System::url(
              "civicrm/report/instance/{$existing_reports['id']}",
              "reset=1"
            );
        } else {
            return null;
        }
    }

    /**
     * Install a scheduled job for the help request matching
     */
    public function installScheduledJob()
    {
        // install matcher scheduled job
        $existing_job_count = civicrm_api3('Job', 'getcount', [
            'api_entity' => 'MutualAid',
            'api_action' => 'match'
        ]);
        if (empty($existing_job_count)) {
            civicrm_api3('Job', 'create', [
                'name'          => E::ts("Match MutualAid Requests"),
                'description'   => E::ts("Will match open help requests to help offers. Unconfirmed help assignments might be changed or deleted."),
                'run_frequency' => 'Daily',
                'api_entity'    => 'MutualAid',
                'api_action'    => 'match',
                'is_active'     => 0,
            ]);
        }
    }
}
