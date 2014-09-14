{include file='design/header.tpl' title="{t t='My Requests'}"}

<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
		<tr>
		{if ($user->group_id>1)}<th>ID</th>{/if}
		  <th>{t t='Name'}</th>
		  <th>{t t='Phone Number'}</th>
		  <th>{t t='Start Point'}</th>
		  <th>{t t='End Points'}</th>
		  <th>{t t='Earliest Departure Time'}</th>
		  <th>{t t='Latest Departure Time'}</th>
		  {if ($user->group_id > 1)}<th>{t t='Request Time'}</th>{/if}
		  {if ($status!='open')}<th>{t t='Status'}</th>{/if}
			<th></th>
	  </tr>
	</thead>
	<tbody>
{foreach $requests as $key => $r}
	<tr>
		{if ($user->group_id>1)}
		  <td>{$r->id}</td>
		{/if}
		<td>{assign "ruser" c2r_get_user_info_object(c2r_get_user_id_by_user_number_id($r->user_number_id))}<img class="gravatar" src="{$ruser->gravatar_small}" alt="{$ruser->name}" /> {$ruser->name}</td>
		<td>{$r->user_number}</td>
		<td>{$r->start_point->name}</td>
		<td>{$r->end_point->name}</td>	
		<td>{$r->earliest_start_time|date_format:"Y-m-d H:i"}</td>
		<td>{$r->latest_start_time|date_format:"Y-m-d H:i"}</td>
		{if ($user->group_id > 1)}<td>{$r->request_time|date_format:"Y-m-d H:i"}</td>{/if}
		{if ($status!='open')}<td>{$r->status}</td>{/if}
		<td>
		  {if $ruser->id == $user->id || $user->group_id>1}
		    <a href="requests.php?all={$all}&status={$status}&close_id={$r->id}" class="btn btn-xs btn-danger" onclick="return confirm('Really close request?')">{t t='Close request'}</a>
		  {/if}
		</td>
	</tr>
{/foreach}
	</tbody>	
</table> 

{include file='design/footer.tpl'}