{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">
        {if $elementInfo.$elementName.prefix}
          <span class="prefix">{$elementInfo.$elementName.prefix}</span>
        {/if}
        {$form.$elementName.html}
      {if $elementInfo.$elementName.suffix}
          <span class="suffix">{$elementInfo.$elementName.suffix}</span>
      {/if}
        {if $elementInfo.$elementName.description}
          <div class="description">{$elementInfo.$elementName.description}</div>
        {/if}
    </div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
