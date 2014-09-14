{include file='design/header.tpl' title="{t t='Route Points'} {$route->origin} ({$location->name})"}

<div class="row">
  <div class="col-lg-9">
    <p>
	{t t='Here you can define your own routes by lining up pick-up points towards your office location. 
	Select up to 10 points on your route starting from the point closest to your home. 
	End at the office or at the start point of another public route. Your route should end at the office.
	Below each route point enter the estimated step time it takes to get this point from the start point of your route. 
	This allows to calculate the time you will roughly pass these points to potentially pick up colleagues there. 
	The pick-up points are predefined. If you need additional pick-up points, contact us using the <a  href="#" onclick="Zenbox.render(); return false;">Service tab</a> on the right side of this page.'}
    </p>
  </div>
  <div class="col-lg-3">
    <a class="btn btn-primary pull-right" href="routes.php{if $my}?my=1{/if}">{if $my}{t t='Back to my routes'}{else}{t t='Back to routes'}{/if}</a>
  </div>
</div>

<script type="text/javascript" charset="utf-8">
  point_counter = 0;
	function add_point() {
	  $('#routepointlist').append('<div id="li_rp_'+point_counter+'" class="routepoint"><div class="form-group"><label class="col-lg-2 control-label" for="">Pickuppoint</label><div class="col-lg-4 pointsselect"></div><p class="form-control-static"><a href="#" onclick="delete_point('+point_counter+')" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span></a></p></div><div class="form-group"><label class="col-lg-2 control-label" for="">Steptime</label><div class="col-lg-4 steptime"></div></div><hr></div>');

  	$('#li_rp_'+point_counter+' .pointsselect').load(
  		'ajax/ajax_points.php?type=rp&lid={$route->lid}&sel={$rp->point_id}&count={$count}&time={substr($rp->steptime, 0, -3)}'
  	);
  	$('#li_rp_'+point_counter+' .steptime').load(
  		'ajax/ajax_points.php?rp_steptime=1&type=rp&lid={$route->lid}&sel={$rp->point_id}&count={$count}&time={substr($rp->steptime, 0, -3)}'
  	);
		point_counter++;	  
	}
	function delete_point(pointid) {
		if ($('#routepointlist div.routepoint').size() > 2) {
			$('#li_rp_'+pointid).remove();
		} else {
			alert('You need at least start and end point')
		}
	}
</script>


{include file="parts/panel_start.tpl" title="Route Points {$route->origin} ({$location->name})"}
<form action="route_points.php" method="post" accept-charset="utf-8" role="form" class="form-horizontal">
	<input type="hidden" name="rid" value="{$rid}" id="rid"/>

  <div id="routepointlist">

    {assign 'count' 0}
    {foreach $rps as $key => $rp}
      {assign 'count' $count+1}
      <div id="li_rp_{$count}" class="routepoint">
        <div class="form-group">
          <label class="col-lg-2 control-label" for="">
            {t t='Pick-up Point'}
          </label>
          <div class="col-lg-4 pointsselect">
          </div>
          <p class="form-control-static">{include file="parts/info.tpl" info="{t t='Select up to 10 points on your route starting from the point closest to your home. End either at the office or at the start point of another public route.'}"}
            <a href="#" onclick="delete_point({$count})" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span></a>
          </p>
        </div>
        <div class="form-group">
          <label class="col-lg-2 control-label" for="">
            Step Time
          </label>
          <div class="col-lg-4 steptime">	
          </div>
	   {include file="parts/info.tpl" info="{t t='Enter the estimated time it takes to get from the start point to this point. This allows to calculate the time you will roughly pass these points to potentially pick up colleagues there. Use the time format hh:mm.'}"}
        </div>

        <hr>
      </div>
      
      <script type="text/javascript" charset="utf-8">
    		$('#li_rp_{$count} .pointsselect').load(
    			'ajax/ajax_points.php?type=rp&lid={$route->lid}&sel={$rp->point_id}&count={$count}&time={substr($rp->steptime, 0, -3)}'
    		);
    		$('#li_rp_{$count} .steptime').load(
    			'ajax/ajax_points.php?rp_steptime=1&type=rp&lid={$route->lid}&sel={$rp->point_id}&count={$count}&time={substr($rp->steptime, 0, -3)}'
    		);
    		point_counter = {$count + 1};
    	</script>

    {/foreach}
  </div>
  
  <div class="form-group">
    <div class="col-lg-offset-2 col-lg-10">
      <a class="btn btn-primary" onclick="add_point()" href="#"><span class="glyphicon glyphicon-plus-sign"></span> {t t='Add a point'}</a>
    </div>
  </div>

  {include file="parts/panel_submit.tpl" symbol="ok" text="{t t='Save Route'}"}
</form>
{include file="parts/panel_end.tpl"}



{if (count($rps)==0)}	
  <script type="text/javascript" charset="utf-8">add_point();</script>
  <script type="text/javascript" charset="utf-8">add_point();</script>
{/if}

{include file='design/footer.tpl'}