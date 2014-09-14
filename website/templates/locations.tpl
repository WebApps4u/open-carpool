{include file='design/header.tpl' title='Locations'}

{if ($edit_lid)}
  {include file="parts/panel_start.tpl" title="Edit Location"}

      <form role="form" action="locations.php" method="post" accept-charset="utf-8" class="form-horizontal">
        	<input type="hidden" name="do" value="update" id="do" />
        	<input type="hidden" name="lid" value="{$edit_lid}" id="cid" />
        
            {* Name *}
          	{include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value=$locations[$edit_lid]->name}
        
            {* Company *}
          	{if ($user->group_id < 3)}
              <input type="hidden" name="cid" value="{$user->company_id}" />
            {else}
              {include file='parts/input.tpl' name="cid" description="Company" values=$companies value=$locations[$edit_lid]->cid select=true}
            {/if}
            
            {* Timezone *}
            {include file='parts/input.tpl' name="timezone" description="Timezone" values=c2r_getTimeZonesArray() value=$locations[$edit_lid]->timezone select=true}
            
            {* Phone *}
            {include file='parts/input.tpl' name="phone" description="Phonenumber" value=$locations[$edit_lid]->phone}
            
            {* Submit *}
            {include file="parts/panel_submit.tpl" simbol="ok" text="Save Location"}
      </form>
  {include file="parts/panel_end.tpl"}
{/if}


<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
		<tr>
		  <th>Name</th>
          <th>Company</th>
          <th>Timezone</th>
          <th>Phone</th>
          <th>Action</th>
	  </tr>
	</thead>
	<tbody>
        {foreach $locations as $lid => $location}
          {assign 'company' c2r_companies_get($location->cid)}
        	<tr>
        		<td>{$location->name}</td>
        		<td>{$company->name}</td>
        		<td>{$location->timezone}</td>
        		<td>{$location->phone}</td>
        		<td>
        		  <a class="btn btn-primary btn-xs" href="locations.php?do=edit&amp;lid={$location->id}"><span class="glyphicon glyphicon-edit"></span></a> 
          		<a class="btn btn-danger btn-xs" onclick="return confirm('Really delete {$location->name}')" href="locations.php?do=delete&amp;lid={$location->id}"><span class="glyphicon glyphicon-trash"></span></a>
        		</td>
        	</tr>
        {/foreach}
	</tbody>	
</table>

{include file="parts/toggle.tpl" id="new_location" text="New Location"}


{include file="parts/panel_start.tpl" title="New Location" hidden=true id="new_location"}
<form role="form" action="locations.php" method="post" accept-charset="utf-8" class="form-horizontal">
  <input type="hidden" name="do" value="insert">
  
    {* Name *}
	{include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value=''}
	
	{* Company *}
	{if ($user->group_id < 3)}
        <input type="hidden" name="cid" value="{$user->company_id}" />
    {else}
        {include file='parts/input.tpl' name="cid" description="Company" values=$companies value='' select=true}
    {/if}
  
    {* Timezone *}
    {include file='parts/input.tpl' name="timezone" description="Timezone" values=c2r_getTimeZonesArray() value='' select=true}
  
    {* Phone *}
    {include file='parts/input.tpl' name="phone" description="Phonenumber" value=''}
  
    {* Submit *}
	{include file="parts/panel_submit.tpl" simbol="ok" text="Save Location"}
  
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}