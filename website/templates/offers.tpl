{include file='design/header.tpl' title="{t t='My Offers'}"}

<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
		<tr>

		  {if ($user->group_id>1)}<th>#</th>{/if}
		  <th>{t t='Name'}</th>
		  <th>{t t='Phone Number'}</th>
          <th>{t t='Route'}</th>
    	  <th>{t t='Reverse'}</th>
    	  <th>{t t='Departure Time'}</th>
    	  {if ($user->group_id>1)}<th>{t t='Request Time'}</th>{/if}
          {if ($status!='open')}<th>{t t='Status'}</th>{/if}
          <th></th>
		</tr>
	</thead>
	<tbody>
  {foreach $offers as $key => $o}
	<tr>
	  {if ($user->group_id>1)}<td>{$o->id}</td>{/if}
	  <td>{assign "ruser" c2r_get_user_info_object(c2r_get_user_id_by_user_number_id($o->user_number_id))}<img class="gravatar" src="{$ruser->gravatar_small}" alt="{$ruser->name}" /> {$ruser->name}</td>
	  <td>{$o->user_number}</td>
	  <td>
		  #{$o->route->key} {$o->route->name}
		  <ol>
		    {foreach c2r_route_points_get($o->route->id) as $point}
		      <li>
		        {assign 'pickuppoint' c2r_pickuppoints_get($point->point_id)}
		        {$pickuppoint->name}
		      </li>
		    {/foreach}
		  </ol>
	  </td>
	  <td>{if $o->reverse}{t t='Yes'}{else}{t t='No'}{/if}</td>
	  <td>{$o->start_time|date_format:"Y-m-d H:i"}</td>
	  {if ($user->group_id>1)}<td>{$o->request_time|date_format:'Y-m-d H:i'}</td>{/if}
      {if ($status!='open')}<td>{$o->status}</td>{/if}
	  <td>
		  {if $ruser->id == $user->id || $user->group_id>1}
		    <a href="offers.php?all={$all}&status={$status}&close_id={$o->id}" class="btn btn-xs btn-danger" onclick="return confirm('Really close offer?')">{t t='Close offer'}</a>
		  {/if}
	  </td>
	</tr>
{/foreach}
	</tbody>	
</table>
		
{include file='design/footer.tpl'}