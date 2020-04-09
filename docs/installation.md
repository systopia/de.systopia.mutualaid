# Installation

## Requirements

You need a current CiviCRM instance and have admin privileges.

You need to have geocoding in CiviCRM set up - information on how to do that can be found [here](https://docs.civicrm.org/user/en/latest/initial-set-up/mapping/).

If you don't want to use Google Maps, you will need to install the [Open Street Map geocoder](https://github.com/bjendres/de.systopia.osm/releases) extension.

You should also have the CiviReport component enabled (/civicrm/admin/setting/component?reset=1). Any of your staff that is supposed to work with the features in CiviCRM needs appropriate permissions for viewing & editing contacts, accessing reports, etc.

To send emails with the system you will need CiviMail configured with an email account ([more info](https://docs.civicrm.org/sysadmin/en/latest/setup/civimail/)).


## Installation

Add the extension to CiviCRM via the UI or the regular extension installation routine described [here](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension).

The extension has a dependency with the [Extended Contact Matcher Extension](https://github.com/systopia/de.systopia.xcm) which should be installed automatically when you install the Corona Aid Extension for CiviCRM.

After installation you should see a new dropdown menu in your CiviCRM menu: Mutual Aid


## Configuration

1. Visit the **Configure Page** (Navigation Menu > Mutual Aid > Configure) and configure the fields you would like to use in the forms as well as if they should be mandatory and have default values. Many of these fields can be turned off, tho some such as postcode, country, help needed, and email address are needed for the system to work.
1. The forms can send out one email to both requestees and volunteers. To configure this, set up an email template (Navigation Menu > Administer > CiviMail > Message Templates –> "Add Message Template") and then select it on the Configure Page.
1. To add extra options for the types of help offered or available, go to (Navigation Menu >  Administer > Custom Data & Screens > Dropdown Options -> **Mutual Aid Help Types**).
1. Enter text for the privacy agreement to be shown on your forms.
1. Link to your forms from anywhere by using the static URLs for the **Help Request Form** and the **Help Offer Form**.
1. The matching process can be automated with a cronjob, which you can setup at (Navigation Menu >  Administer > System Settings > Scheduled Jobs -> **Mutual Aid**).

!!! caution ""
    Note: if you change the Contact display fields outside of the extension here: civicrm/admin/setting/preferences/display - then you need to reset CiviCRM's cache by visiting *civicrm/menu/rebuild*.

## Permissions

**Drupal only**: you can visit the permissions settings page and assign the MutualAid permissions to roles, i.e. *request for help* for anonymous, *offer help* to authenticated users, and *administer for MutualAid* to administrators.
