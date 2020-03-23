# de.systopia.mutualaid - Corona Aid Extension for CiviCRM
Fight Corona by organising mutual aid in your neighbourhood with this CiviCRM Extension!

## Introduction
Corona crisis protection measures like social distancing and quarantine are hitting everyone hard and it's time to step up and help those in need. CiviCRM is the perfect tool to organize help and connect those in need with volunteers who are able to support them.

## Scope and Features
This CiviCRM extension enables organisations to connect people in need with local volunteers who are able to help. It provides configurable online forms for people in need and volunteers as well as a matching mechanism to connect individuals based on proximity and other attributes. It's main features are:

* two configurable online forms for people in need and volunteers
* a configurable confirmation email for people who fill out the form
* uses CiviCRM's built in geocoding feature
* a matching algorhytm that connects people in need with volunteers via a realtionship
* pre-configured reports to find and review matched contacts
* advanced contact matching using the [Extended Contact Matcher Extension](https://github.com/systopia/de.systopia.xcm) extension incl. a preconfigured profile 

## Installation and Prerequisites
You need a current CiviCRM instance and have admin priviliges. Add the extension to  CiviCRM via the UI or the regular extension installation routine described [here](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension). The extension has a dependency with the [Extended Contact Matcher Extension](https://github.com/systopia/de.systopia.xcm) which should be installed automatically when you install the Corona Aid Extension for CiviCRM.

You need to have geocoding in CiviCRM set up - information on how to do that can be found [here](https://docs.civicrm.org/user/en/latest/initial-set-up/mapping/). You should also have the CiviReport component enabled (/civicrm/admin/setting/component?reset=1). Any of your staff that is supposed to work with the features in CiviCRM needs appropriate permissions for viewing & editing contacts, accessing reports etc.


## Configuration
1. Select the fields you would like to use in the forms by Navigating to ...
2. If you want the forms to send out emails, set up an email template
3. Enter a text for the privacy agreement to be shown on your forms
4. Link to your forms from anywhere by using the static URLs for the "require assistance form" and the "offer assistance form"
5. Activate / configure the cronjob that triggers the matching mechanism 

* [Optional] If you want to provide help categories for your forms, configure these in the according option group ...
* [Optional] Create a landing page
* [Optional] Adapt the matching rules of XCM
* [Optional] adapt the reports created by the extension


## Description & Usage
Whenever the matching algorhytm is triggered by the cronjob or manually it will find the best match for each individual in the database which is looking for help and create a relationship of the type "..." between the two individuals. The matches are made based on proximity to each other and the number of matching help categories and (if applicable) spoken languages. If help categories and/or languages are used there needs to be at least one match for each. 

The relationship will have the status "needs review" (custom field). Each time the algorhytm is executed it may replace relationships with the status "needs review" with better matches (but not relationships with other status'). 

You can find individuals in need and/or their matches using CiviCRM's built in search features or the preconfigured reports created by the extension. After reviewing the match (which may include contacting the individuals by phone or other means), you should set the relationship status to "confirmed", "communicated" or "cancelled". Once a relationship needs to be ended. you should set an end date to the relationship and change it's status to inactive (those are CiviCRM core features).

You can always create relationships and/or contacts manually if you need to.

Individuals with addresses that cannot be geocoded automatically either need manual geocoding (simply edit the address and add coordinates) or manual creation of a relationship with a matching individual.

The matching algorhytm runs automatically according to your settings and/or can be triggered manually from the menu.

## Remarks and Planned Features
If you think that we should add a feature to this extension, please create a detailed issue. Please be aware that we will try to keep this extension as straight forward as possible for the time beeing so we will only consider feature suggestions that seem to be of general interest. Of course, feel free to clone this repository and adapt it to your own needs if required or approach us if you want individual customizations.
### Custom Forms
We used CiviCRM native forms to make the extension as accessible as possible. In case you want to create your own form you can do so and submit all the information to CiviCRM via it's REST API. All actions such as form submissions and running the matching algorhytm are available via the API.  
### Automated communication
Currently, you will manually need to review relationships, change their status and communicate with the individuals. In case you want to automate this process (e.g. send out an email to helpers including contact details of their match) you could probably do so by using [CiviRules](https://github.com/Kajakaran/org.civicoop.civirules) or other CiviCRM features / extensions. 
