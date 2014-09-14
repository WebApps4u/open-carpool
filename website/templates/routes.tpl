{assign 'title' "{t t='Routes'}"}
{if ($myroutes)}{assign 'title' "{t t='My Routes'}"}{/if}
{include file='design/header.tpl' title=$title}

{if ($edit_rid)}
  {include file="parts/panel_start.tpl" title="{t t='Edit Route'}"}
  <form role="form" action="routes.php" method="post" accept-charset="utf-8" class="form-horizontal">
	<input type="hidden" name="do" value="update" id="do"/>
	<input type="hidden" name="rid" value="{$edit_rid}" id="pid"/>

    {* Name *}
    {include file='parts/input.tpl' name="origin" description="Name" placeholder="{t t='Enter name'}" value=$routes[$edit_rid]->origin}
    
    {* Location *}
    {assign 'help_id' 0}
    {if $myroutes}{assign 'help_id' $user->id}{/if}
    {include file='parts/input.tpl' name="lid_e" description="{t t='Location'}" value=$routes[$edit_rid]->lid values=$locations select=true extra="onchange=\"$('#key_e').load('ajax/ajax_keys.php?uid={$help_id}&lid='+$('#lid_e').val());\""}

    {* Status *}
    {include file='parts/input.tpl' name='status' description='Status' values=c2r_getStatusArray() select=true value=$routes[$edit_rid]->status}
    
    <div class="form-group">
      <label class="col-lg-2 control-label" for="key">{t t='Key'}</label>
      <div class="col-lg-4" id="key_e">
        {assign 'help_id' 0}
        {if $myroutes}{assign 'help_id' $user->id}{/if}
        <script type="text/javascript" charset="utf-8">
          $('#key_e').load('ajax/ajax_keys.php?uid={$help_id}&lid='+$('#lid_e').val());
        </script>
      </div>
    </div>
		
	{* User ID *}
    {if ($myroutes)}
      <input type="hidden" name="user_id" value="{$user->id}" id="user_id"/>
      <input type="hidden" name="my" value="1" id="{t t='my'}"/>
    {else}
      {include file='parts/input.tpl' name="user_id" description="User ID" placeholder="{t t='Enter User ID'}" value=$routes[$edit_rid]->user_id}
    {/if}

    {* Submit *}
	{include file="parts/panel_submit.tpl" simbol="ok" text="{t t='Save Route'}"}
    
  </form>
  {include file="parts/panel_end.tpl"}
{/if}

<div>
{t t='You should only create your own routes in case none of the <a href="allroutes.php">public routes</a> work for you. The routes you create here are private routes only visible to you. If you miss a public route for all participants, please send us your route suggestions via the service tab on the right or by sending a mail to <a href="mailto:support@opencarpool.zendesk.com?subject=Route%20suggestion">support@opencarpool.zendesk.com</a>.'}<br />&nbsp;
</div>


<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
    <tr>
      {if (!$myroutes)}<th>ID</th>{/if}
			<th>{t t='Location'}</th>
			<th>{t t='Name'}</th>
			{if (!$myroutes)}<th>{t t='Status'}</th>{/if}
			<th>Key</th>
			{if (!$myroutes)}<th>{t t='User'}</th>{/if}
			<th>{t t='Action'}</th>
    </tr>
  </thead>
  <tbody>
  {foreach $routes as $rid => $route} 
    {if $route->status != disabled || !$myroutes}
      {assign 'location' $locations[$route->lid]}
      <tr>
        {if (!$myroutes)}<td>{$route->id}</td>{/if}
    	  <td>{$location->name}</td>
    	  <td>{$route->origin}</td>
    	  {if (!$myroutes)}<td>{$route->status}</td>{/if}
    	  <td>{$route->key}</td>
    	  {if (!$myroutes)}<td>{$route->user_id}</td>{/if}
    	  <td>
    	    <a class="btn btn-primary btn-xs" href="routes.php?do=edit&amp;rid={$route->id}{if $myroutes}&amp;my=1{/if}"><span class="glyphicon glyphicon-edit"></span></a> 
    		  <a class="btn btn-primary btn-xs" href="route_points.php?rid={$route->id}{if $myroutes}&amp;my=1{/if}">{t t='Edit route points'}</a>
    		  <a class="btn btn-danger btn-xs" onclick="return confirm('Delete route {$route->origin} - {$route->destination}?')" href="routes.php?do=delete&amp;rid={$route->id}{if $myroutes}&amp;my=1{/if}"><span class="glyphicon glyphicon-trash"></span></a> 
    	  </td>
      </tr>
    {/if}
  {/foreach}
  </tbody>
</table>

{include file="parts/toggle.tpl" id="new_route" text="{t t='New Route'}"}


{include file="parts/panel_start.tpl" id="new_route" hidden=true title="{t t='New Route'}"}
<form role="form" action="routes.php" method="post" accept-charset="utf-8" class="form-horizontal">

  <input type="hidden" name="do" value="insert" id="do"/>
                  
  {* Name *}
  {include file='parts/input.tpl' name="origin" description="{t t='Name'}" placeholder="{t t='Enter name'}" value=''}
  
  {* Location *}
  {assign 'help_id' 0}
  {if $myroutes}{assign 'help_id' $user->id}{/if}
  {include file='parts/input.tpl' name="lid" description="{t t='Location'}" value='' values=$locations select=true extra="onchange=\"$('#key1').load('ajax/ajax_keys.php?uid=$help_id&lid='+$('#lid').val())\""}
    
  {* Status *}
  {include file='parts/input.tpl' name='status' description="{t t='Status'}" values=c2r_getStatusArray() select=true}
  
  <div class="form-group">
    <label class="col-lg-2 control-label" for="key">Key</label>
    <div class="col-lg-4" id="key1">
      {assign 'help_id' 0}
      {if $myroutes}{assign 'help_id' $user->id}{/if}
      <script type="text/javascript" charset="utf-8">
        $('#key1').load('ajax/ajax_keys.php?uid={$help_id}&lid='+$('#lid').val());
      </script>
    </div>
  </div>
          
  {* User ID *}
  {if ($myroutes)}
    <input type="hidden" name="user_id" value="{$user->id}" id="user_id"/>
    <input type="hidden" name="my" value="1" id="my"/>
  {else}
    {include file='parts/input.tpl' name="user_id" description="User ID" placeholder="{t t='Enter User ID'}" value='0'}
  {/if}
  
  {* Submit *}
  {include file="parts/panel_submit.tpl" simbol="ok" text="{t t='Save route'}"}
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}