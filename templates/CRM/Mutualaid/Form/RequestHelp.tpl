{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">
      {if $elementInfo.$elementName.prefix}
        <div class="prefix huge">{$elementInfo.$elementName.prefix}</div>
      {/if}
      {if $elementInfo.$elementName.field_prefix}
        <span class="field-prefix">{$elementInfo.$elementName.field_prefix}</span>
      {/if}
      {$form.$elementName.html}
      {if $elementInfo.$elementName.field_suffix}
        <span class="field-suffix">{$elementInfo.$elementName.field_suffix}</span>
      {/if}
      {if $elementInfo.$elementName.description}
        <div class="description huge">{$elementInfo.$elementName.description}</div>
      {/if}
      {if $elementInfo.$elementName.suffix}
        <div class="suffix huge">{$elementInfo.$elementName.suffix}</div>
      {/if}
    </div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
