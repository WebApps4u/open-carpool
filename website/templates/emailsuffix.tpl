{include file='design/header.tpl' title="Email Suffix configuration"}

{if $edit_suffix}
  {include file="parts/panel_start.tpl" title="Edit Emailsuffix"}
  <form role="form" action="emailsuffix.php" method="post" accept-charset="utf-8" class="form-horizontal">
		<input type="hidden" name="do" value="update" id="do" />
		<input type="hidden" name="id" value="{$edit_suffix->id}" id="id" />

    {* Suffix *}
  	{include file='parts/input.tpl' name="suffix" description="Suffix" placeholder="Enter suffix" value=$edit_suffix->suffix}

    {* Location *}
    {include file='parts/input.tpl' name="location_id" description="Location" value=$edit_suffix->location_id values=$locations select=true}

    {* Submit *}
		{include file="parts/panel_submit.tpl" simbol="ok" text="Save Location"}
	</form>
	{include file="parts/panel_end.tpl"}
{/if}

<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
    <tr>
      <th>#</th>
			<th>Location</th>
			<th>Suffix</th>
			<th>Action</th>
    </tr>
  </thead>
  <tbody>
  {foreach $emailsuffixes as $suffix} 
    <tr>
      <td>{$suffix->id}</td>
      <td>{$suffix->location_name}</td>
      <td>{$suffix->suffix}</td>
      <td>
        <a class="btn btn-primary btn-xs" href="emailsuffix.php?do=edit&amp;id={$suffix->id}"><span class="glyphicon glyphicon-edit"></span></a> 
    	  <a class="btn btn-danger btn-xs" onclick="return confirm('Delete Suffix {$suffix->suffix}?')" href="emailsuffix.php?do=delete&amp;id={$suffix->id}"><span class="glyphicon glyphicon-trash"></span></a> 
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>


{include file="parts/toggle.tpl" id="new_suffix" text="New Emailsuffix"}

{include file="parts/panel_start.tpl" id="new_suffix" hidden=true title="New Emailsuffix"}
<form role="form" action="emailsuffix.php" method="post" accept-charset="utf-8" class="form-horizontal">
  <input type="hidden" name="do" value="insert" id="do"/>
                  
  {* Suffix *}
  {include file='parts/input.tpl' name="suffix" description="Emailsuffix" placeholder="Enter Emailsuffix" value=''}
  
  {* Location *}
  {include file='parts/input.tpl' name="location_id" description="Location" value='' values=$locations select=true}
  
  {* Submit *}
  {include file="parts/panel_submit.tpl" simbol="ok" text="Save Emailsuffix"}
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}
