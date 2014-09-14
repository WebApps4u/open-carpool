{include file='design/header.tpl' title="{t t='Matching Requests'}"}

<div class="panel-group" id="accordion">

{if count($offers)}
    {assign 'first' 1}
    {foreach $offers as $offer}
      <div class="panel panel-default">
        <div class="panel-heading">
          <div class="pull-right"><a href="matchingrequests.php?close_id={$offer->id}" class="btn btn-xs btn-danger" onclick="return confirm('Really close offer?')">Close offer</a></div>
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{$offer->id}">
              {$offer->start_time|date_format:"Y-m-d H:i"}:
              Route #{$offer->route->key} {$offer->route->name}
              {if $offer->reverse}reverse{/if}
            </a>
          </h4>
        </div>
        <div id="collapse{$offer->id}" class="panel-collapse collapse {if $first}in{/if}">
          <div class="panel-body">
            
            {assign 'requests_for_offer' c2r_get_matches_for_offer($offer->id)}
            {if count($requests_for_offer)}
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
            	  </tr>
            	</thead>
            	<tbody>
            {foreach $requests_for_offer as $request_id}
              {assign 'r' c2r_request_get($request_id)}
            	<tr>
            		{if ($user->group_id>1)}
            		  <td>{$r->id}</td>
            		{/if}
            		<td>
            		    <a href="#">
            		        {assign "ruser" c2r_get_user_info_object(c2r_get_user_id_by_user_number_id($r->user_number_id))}<img class="gravatar" src="{$ruser->gravatar_small}" alt="{$ruser->name}" /> {$ruser->name}
            		    </a>
            		    {include file="parts/panel_start.tpl" title=t("Send a message")}   
            		    <form role="form" action="message.php" method="get" accept-charset="utf-8" class="form-horizontal message-popup" style="background: url({$ruser->gravatar_big}) no-repeat right 20px;">
            		        <h3>{$ruser->name}</h3>
            		        <p>{t t='Phone Number'}: {$r->user_number}</p>
            		        <p>{t t='Send a message'}:</p>
            		        <p><textarea class="form-control" name="message" cols="50" rows="5"></textarea></p>
            		        {* Submit *}
                            {include file='parts/panel_submit.tpl' text='Send message' symbol='ok'}
                        </form>
                        {include file="parts/panel_end.tpl"}
            		</td>
            		<td>{$r->user_number}</td>
            		<td>{$r->start_point->name}</td>
            		<td>{$r->end_point->name}</td>	
            		<td>{$r->earliest_start_time|date_format:"Y-m-d H:i"}</td>
            		<td>{$r->latest_start_time|date_format:"Y-m-d H:i"}</td>
            		{if ($user->group_id > 1)}<td>{$r->request_time|date_format:"Y-m-d H:i"}</td>{/if}
            	</tr>
            {/foreach}
            	</tbody>	
            </table>
            {else}
              <strong>{t t='No matching requests!'}</strong>
            {/if}
            
          </div>
        </div>
      </div>
    {assign 'first' 0}
    {/foreach}
{else}
    <p>{t t='Sorry, but we could not find any matching requests :-('}</p>
{/if}
</div>
{include file='design/footer.tpl'}