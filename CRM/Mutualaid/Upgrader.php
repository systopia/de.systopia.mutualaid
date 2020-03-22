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
class CRM_Mutualaid_Upgrader extends CRM_Mutualaid_Upgrader_Base {

  /**
   * Create the required custom fields
   */
  public function install() {
    $customData = new CRM_Mutualaid_CustomData(E::LONG_NAME);

    $customData->syncOptionGroup(E::path('resources/option_group_help_status.json'));
    $customData->syncOptionGroup(E::path('resources/option_group_help_types.json'));

    $customData->syncEntities(E::path('resources/relationship_types.json'));
    $customData->syncCustomGroup(E::path('resources/custom_group_relationship_mutualaid.json'));

    $customData->syncCustomGroup(E::path('resources/custom_group_individual_language.json'));
    $customData->syncCustomGroup(E::path('resources/custom_group_individual_needs_help.json'));
    $customData->syncCustomGroup(E::path('resources/custom_group_individual_offers_help.json'));
  }

  /**
   * PostInstall: anything to do here?
   */
  public function postInstall() {
  }

  /**
   * On enabling: Do some verification
   */
  public function enable() {
    // make sure the 'mutual_help' profile is there
    $profile_list = CRM_Xcm_Configuration::getProfileList();
    if (!isset($profile_list['mutual_help'])) {
      // not here? create!
      $profile_data = Civi::settings()->get('xcm_config_profiles');
      $mutual_help_profile = json_decode(file_get_contents(E::path('resources/xcm_matching_profile.json')), true);
      $mutual_help_profile['label'] = E::ts("Mutual Aid Submissions");
      $profile_data['mutual_help'] = $mutual_help_profile;
      Civi::settings()->set('xcm_config_profiles', $profile_data);
    }

    // install reports
    $this->installReport('mutualhelp_unconfirmed', file_get_contents(E::path('resources/report_unconfirmed.json')));

    // finally: run some tests
    $geo_coder = Civi::settings()->get('geoProvider');
    if (empty($geo_coder)) {
      CRM_Core_Session::setStatus(
        E::ts("Your system doesn't have a GeoCoder service configured. A GeoCoder can assign geo coordinates to a given address.<br/>").
        E::ts("This is really important for this extension, since it is needed to calculate the distance between two people.<br/>").
        E::ts("Please configure your GeoCoder <a href=\"%1\">HERE</a>.<br/>", [1 => CRM_Utils_System::url('civicrm/admin/setting/mapping')]).
        E::ts("If you don't want to use the built-in ones we recommend <a href=\"%1\">OpenStreetMap</a>.<br/>", [1 => 'https://github.com/bjendres/de.systopia.osm/releases/latest']),
        E::ts("No GeoCoder configured"),
        'warn'
      );
    }
  }

  /**
   * Disabled: do anything?
   */
  public function disable() {
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Install a new report (unless already there)
   *
   * @param $name string
   *    report name
   *
   * @param $raw_json_data string
   *    unparsed json data (needs some string replacement)
   */
  protected function installReport($name, $raw_json_data) {
    // first: check if already installed
    $existing_reports = civicrm_api3('ReportInstance', 'get', [
      'name'   => $name,
      'return' => 'id'
    ]);
    if ($existing_reports['count'] > 0) {
      // report already installed
      return;
    }

    // to some ID lookups in the string
    $custom_field_id_help_status = CRM_Mutualaid_CustomData::getCustomField('mutualaid', 'help_status');
    $raw_json_data = preg_replace('/%%custom_field_id_help_status%%/', $custom_field_id_help_status['id'], $raw_json_data);

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
   * Get the URL for a given report name
   *
   * @param $report_name string
   *  name of the report
   *
   * @return string|null
   *  URL of the report, or null if not found
   */
  public static function getReportURL($report_name) {
    // look up report
    $existing_reports = civicrm_api3('ReportInstance', 'get', [
      'name'   => $report_name,
      'return' => 'id'
    ]);

    // return URL
    if (isset($existing_reports['id'])) {
      return CRM_Utils_System::url("civicrm/report/instance/{$existing_reports['id']}", "reset=1");
    } else {
      return null;
    }
  }
}
