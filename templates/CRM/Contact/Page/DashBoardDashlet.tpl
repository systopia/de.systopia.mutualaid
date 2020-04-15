{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{include file="CRM/common/dashboard.tpl"}
{include file="CRM/common/chart.tpl"}
{* Alerts for critical configuration settings. *}
{$communityMessages}

<ul id="column-0" class="column ui-sortable" style="width: 100%;">
	<li class="empty-placeholder" style="display: none;"></li>
	<li class="widget widget-getting-started" id="widget-2"><div class="widget-wrapper">  
		<div class="widget-controls"><h2 class="ui-sortable-handle" style="margin: 0;">CiviCRM Mutual Aid Dashboard</h2></div>  
		<div class="widget-content">
			<div id="civicrm-getting-started">
				<div class="crm-block crm-content-block" style="padding-right: 15px;">
					
				<br />
					<div class="row">
						<div style="width: calc(67% - 30px); margin: 0 10px; float: left;" style="padding: 0;">
					<div class="row">
						<div style="margin: 0 10px; width: calc(50% - 20px); float: left;">
					<h3>Gather <i class="crm-i fa-cloud-upload float-right"></i></h3><table><tbody>
						<tr><td width="8"><i class="crm-i fa-sign-in"></i></td><td><a href="{crmURL p='civicrm/import/contact' q="reset=1&cid=`$row.source_contact_id`"}">Import data</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-user-plus"></i></td><td><a href="{crmURL p='civicrm/contact/add' q="reset=1&ct=Individual"}">Add individual</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-life-ring"></i></td><td><a href="{crmURL p='civicrm/mutualaid/offer-help' q="reset=1"}">Help offer form</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-microphone"></i></td><td><a href="{crmURL p='civicrm/mutualaid/request-help' q="reset=1"}">Help request form</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-user-md" style="opacity: 0.35"></i></td><td><a style="opacity: 0.35">Create admin user accounts</a></td></tr></tbody></table>
						</div>
						<div style="margin: 0 10px; width: calc(50% - 20px); float: left;">
						<h3>Communicate <i class="crm-i fa-comments-o float-right"></i></h3><table><tbody>
							<tr><td width="8"><i class="crm-i fa-search"></i></td><td><a href="{crmURL p='civicrm/contact/search' q="reset=1"}">Find people</a> (<a href="{crmURL p='civicrm/contact/search/advanced' q="reset=1"}">Advanced search</a>)</td></tr>
							<tr><td width="8"><i class="crm-i fa-users"></i></td><td><a href="{crmURL p='civicrm/group' q="reset=1"}">Manage contact groups</a></td></tr>
							<tr><td width="8"><i class="crm-i fa-sign-in"></i></td><td><a href="{crmURL p='civicrm/mailing/send' q="reset=1"}">Send bulk email</td></tr>
							<tr><td width="8"><i class="crm-i fa-comments-o" style="opacity: 0.35"></i></td><td><a style="opacity: 0.35">Send bulk SMS</a></td></tr>
							
							<tr><td width="8"><i class="crm-i fa-vcard-o" style="opacity: 0.35"></i></td><td><a style="opacity: 0.35">Print address labels/sheets</a></td></tr>
							
							</tbody></table>
						</div>
						<div style="margin: 0 10px; width: calc(50% - 20px); float: left;">
						
						<h3>Connect <i class="crm-i fa-chain float-right"></i></h3>
					<table><tbody><tr><td width="8"><i class="crm-i fa-arrows-h"></i></td><td><a href="{crmURL p='civicrm/mutualaid/matchnow' q="reset=1"}">Match requests and offers</a></td></tr><tr><td width="8"><i class="crm-i fa-chain"></i></td><td><a href="{crmURL p='civicrm/report/instance/38' q="reset=1"}">Unconfirmed matches</a></td></tr><tr><td width="8"><i class="crm-i fa-chain-broken"></i></td><td><a href="{crmURL p='civicrm/report/instance/39' q="reset=1"}">Unmatched requests</a></td></tr>
					<tr><td width="8"><i class="crm-i fa-check-square-o" style="opacity: 0.35"></i></td><td><a style="opacity: 0.35">Confirmed matches</a></td></tr>
					</tbody></table>
						</div>
						<div style="margin: 0 10px; width: calc(50% - 20px); float: left;">
						<h3>Configure and setup <i class="crm-i fa-cog float-right"></i></h3>
					<table><tbody>
						<tr><td width="8"><i class="crm-i fa-map-o"></i></td><td><a href="{crmURL p='civicrm/admin/setting/mapping' q="reset=1"}">Mapping/Geocoding setup</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-puzzle-piece"></i></td><td><a href="{crmURL p='civicrm/admin/setting/mutualaid' q="reset=1"}">General configuration</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-clone"></i></td><td><a href="{crmURL p='civicrm/admin/options' q="action=browse&reset=1"}">Specify help categories</a></td></tr>
						<tr><td width="8"><i class="crm-i fa-envelope"></i></td><td><a href="{crmURL p='civicrm/admin/messageTemplates' q="reset=1"}">Create default emails</a></td></tr></tbody></table>
						</div>
						</div>
						</div>
						<div style="width: 33%; margin-right: 10px;float: left;">
					<h3 style="margin: 0 10px;">Status <i class="crm-i fa-bar-chart float-right"></i></h3>
					<h3 style="margin: 0 10px;"><table style="background: transparent;"><tbody>
			<tr><td width="8"><i class="crm-i fa-map"></i></td><td><a href="{crmURL p='civicrm/contact/search/advanced' q="_qf_Map_display=true&qfKey=cdbf888a55c36a2fb87045d787416582_2698"}">Map of offers & requests</a></td></tr>
			<!--<tr><td width="8" style="opacity: 0.35"><strong>X</strong></td><td><a href="https://chat.civicrm.org?src=gs" target="_blank">Offers</a></td></tr>
			<tr><td width="8" style="opacity: 0.35"><strong>Y</strong></td><td><a href="https://chat.civicrm.org?src=gs" target="_blank">Requests</a></td></tr>
			<tr><td width="8" style="opacity: 0.35"><strong>Z</strong></td><td><a href="https://chat.civicrm.org?src=gs" target="_blank">Matched requests</a></td></tr>
			<tr><td width="8"><strong style="color: #942a25;">18</strong></td><td><a href="https://chat.civicrm.org?src=gs" target="_blank">Unmatched requests</a></td></tr>-->
			</tbody></table></h3>
					<div id="help">CiviCRM Mutual Aid is a new free extension in Beta – please try it out, test it and feedback any issues on <a href="https://github.com/systopia/de.systopia.mutualaid">Github</a> or <a href="https://chat.civicrm.org/civicrm/channels/covid-19">Mattermost</a>.<!-- If you'd like to support the project you can<a href="https://civicrm.org/civicrm/contribute/transact?reset=1&amp;id=47&amp;src=gs" target="_blank"> donate here</a>-->.
					</div>
						</div>
					</div>
					
					</div></div>

</div></div></li></ul>


<div class="crm-submit-buttons crm-dashboard-controls">
<a href="#" id="crm-dashboard-configure" class="crm-hover-button show-add">
  <i class="crm-i fa-wrench"></i> {ts}Configure this Dashboard{/ts}
</a>

<a style="float:right;" href="#" class="crm-hover-button show-refresh" style="margin-left: 6px;">
  <i class="crm-i fa-refresh"></i> {ts}Refresh Dashboard Data{/ts}
</a>

</div>
<br />
<br />

<div class="clear"></div>
<div class="crm-block crm-content-block">
{* Welcome message appears when there are no active dashlets for the current user. *}
<div id="empty-message" class='hiddenElement'>
    <div class="status">
        <div class="font-size12pt bold">{ts}Welcome to this, your Home Dashboard{/ts}</div>
        <div class="display-block">
            {ts}Your dashboard provides a one-screen view of the data that's most important to you. Graphical or tabular data is pulled from the reports you select, and is displayed in 'dashlets' (sections of the dashboard).{/ts} {help id="id-dash_welcome" file="CRM/Contact/Page/Dashboard.hlp"}
        </div>
    </div>
</div>

<div id="configure-dashlet" class='hiddenElement' style="min-height: 20em;"></div>
<div id="civicrm-dashboard">
  {* You can put anything you like here.  jQuery.dashboard() will remove it. *}
  <noscript>{ts}Javascript must be enabled in your browser in order to use the dashboard features.{/ts}</noscript>
</div>
<div class="clear"></div>
{literal}
<script type="text/javascript">
  CRM.$(function($) {
    $('#crm-dashboard-configure').click(function(e) {
      e.preventDefault();
      $(this).hide();
      if ($("#empty-message").is(':visible')) {
        $("#empty-message").fadeOut(400);
      }
      $("#civicrm-dashboard").fadeOut(400, function() {
        $(".crm-dashboard-controls").hide();
        $("#configure-dashlet").fadeIn(400);
      });
      CRM.loadPage(CRM.url('civicrm/dashlet', 'reset=1'), {target: $("#configure-dashlet")});
    });
  });
</script>
{/literal}
</div>
