{include file='design/header.tpl' title="{t t='Matching Offers'}"}

<div class="panel-group" id="accordion">

{if count($requests)}
  {assign 'first' 1}
  {foreach $requests as $request}
      <div class="panel panel-default">
        <div class="panel-heading">
          <div class="pull-right"><a href="matchingoffers.php?close_id={$request->id}" class="btn btn-xs btn-danger" onclick="return confirm('Really close offer?')">Close request</a></div>
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{$request->id}">
              {$request->earliest_start_time|date_format:"Y-m-d H:i"}
              /
              {$request->latest_start_time|date_format:"Y-m-d H:i"}
              :
              {$request->start_point->name}
              -
              {$request->end_point->name}
            </a>
          </h4>
        </div>
        <div id="collapse{$request->id}" class="panel-collapse collapse {if $first}in{/if}">
          <div class="panel-body">
            
            {assign 'offers_for_request' c2r_get_matches_for_request($request->id)}
            {if count($offers_for_request)}
              <table class="table table-condensed table-striped table-hover table-datatable">
                <thead>
            		  <tr>
            		    {if ($user->group_id>1)}<th>#</th>{/if}  
            		    <th>{t t='Name'}</th>
            		    <th>{t t='Phone Number'}</th>
            			<th>{t t='Route'}</th>
            			<th>{t t='Reverse'}</th>
            			<th>{t t='Start Time'}</th>
            			{if ($user->group_id>1)}<th>{t t='Request Time'}</th>{/if}
            		  </tr>
            	  </thead>
            	  <tbody>
                  {foreach $offers_for_request as $offer_id}
                  {assign 'o' c2r_offer_get($offer_id)}
            	    <tr>
            	      {if ($user->group_id>1)}<td>{$o->id}</td>{/if}
            		  <td>
                        {assign "ruser" c2r_get_user_info_object(c2r_get_user_id_by_user_number_id($o->user_number_id))}<img class="gravatar" src="{$ruser->gravatar_small}" alt="{$ruser->name}" /> {$ruser->name}
            		  </td>
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
            		  {if ($user->group_id>1)}
            		    <td>{$o->request_time|date_format:'Y-m-d H:i'}</td>
            		  {/if}
            	    </tr>
                  {/foreach}
            	  </tbody>	
              </table>
            {else}
              <strong>{t t="{t t='No matching offers!'}"}</strong>
            {/if}
          </div>
        </div>
      </div>
    {assign 'first' 0}
    {/foreach}
{else}
    <p>{t t='Sorry, but we could not find any matching offers :-('}</p>
{/if}

</div>

{include file='design/footer.tpl'}