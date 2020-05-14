<div id="civicrm-mutual-aid" style="overflow: auto;">
	<div class="crm-block crm-content-block">
		<div id="help"><em>{ts}For feedback and support with CiviCRM Mutual Aid please check <a href="https://github.com/systopia/de.systopia.mutualaid">Github</a> or <a href="https://chat.civicrm.org/civicrm/channels/covid-19">Mattermost</a>{/ts}.</em></div>
		<h3>{ts}Configure and setup{/ts} <i class="crm-i fa-cog float-right"></i></h3>
			<table><tbody>
				<tr><td width="8"><i class="crm-i fa-map-o"></i></td><td><a href="{crmURL p='civicrm/admin/setting/mapping' q="reset=1"}">{ts}Mapping/Geocoding setup{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-puzzle-piece"></i></td><td><a href="{crmURL p='civicrm/admin/setting/mutualaid' q="reset=1"}">{ts}General configuration{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-clone"></i></td><td><a href="{crmURL p='civicrm/admin/options' q="action=browse&reset=1"}">{ts}Specify help categories{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-envelope"></i></td><td><a href="{crmURL p='civicrm/admin/messageTemplates' q="reset=1"}">{ts}Create default emails{/ts}</a></td></tr>
			</tbody></table>
		<h3>{ts}Gather{/ts} <i class="crm-i fa-cloud-upload float-right"></i></h3>
			<table><tbody>
				<tr><td width="8"><i class="crm-i fa-sign-in"></i></td><td><a href="{crmURL p='civicrm/import/contact' q="reset=1&cid=`$row.source_contact_id`"}">{ts}Import data{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-user-plus"></i></td><td><a href="{crmURL p='civicrm/contact/add' q="reset=1&ct=Individual"}">{ts}Add individual{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-life-ring"></i></td><td><a href="{crmURL p='civicrm/mutualaid/offer-help' q="reset=1"}">{ts}Help offer form{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-microphone"></i></td><td><a href="{crmURL p='civicrm/mutualaid/request-help' q="reset=1"}">{ts}Help request form{/ts}</a></td></tr>
			</tbody></table>
		<h3>{ts}Communicate{/ts} <i class="crm-i fa-comments-o float-right"></i></h3>
			<table><tbody>
				<tr><td width="8"><i class="crm-i fa-search"></i></td><td><a href="{crmURL p='civicrm/contact/search' q="reset=1"}">{ts}Find people{/ts}</a> (<a href="{crmURL p='civicrm/contact/search/advanced' q="reset=1"}">{ts}Advanced search{/ts}</a>)</td></tr>
				<tr><td width="8"><i class="crm-i fa-users"></i></td><td><a href="{crmURL p='civicrm/group' q="reset=1"}">{ts}Manage contact groups{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-sign-in"></i></td><td><a href="{crmURL p='civicrm/mailing/send' q="reset=1"}">{ts}Send bulk email{/ts}</td></tr>
			</tbody></table>
		<h3>{ts}Connect{/ts} <i class="crm-i fa-chain float-right"></i></h3>
			<table><tbody>
				<tr><td width="8"><i class="crm-i fa-arrows-h"></i></td><td><a href="{crmURL p='civicrm/mutualaid/matchnow' q="reset=1"}">{ts}Match requests and offers{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-chain"></i></td><td><a href="{crmURL p='civicrm/report/instance/38' q="reset=1"}">{ts}Unconfirmed matches{/ts}</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-chain-broken"></i></td><td><a href="{crmURL p='civicrm/report/instance/39' q="reset=1"}">{ts}Unmatched requests{/ts}</a></td></tr>
				<!--<tr><td width="8"><i class="crm-i fa-check-square-o" style="opacity: 0.35"></i></td><td><a style="opacity: 0.35">Confirmed matches</a></td></tr>
				<tr><td width="8"><i class="crm-i fa-map"></i></td><td><a href="{crmURL p='civicrm/contact/search/advanced' q="_qf_Map_display=true&qfKey=X"}">Map of offers & requests</a></td></tr>-->
			</tbody></table>
	</div>
</div>
