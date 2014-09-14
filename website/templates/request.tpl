{include file='design/header.tpl' title="{t t='Request a Lift'}" maps=true}

<!-- section selector script -->
<script type="text/javascript" charset="utf-8">
	function change_select (val) {
		if (val) {
			$('#point_select').show();
			$('#route_select').hide();
		};
		if (!val) {
			$('#point_select').hide();
			$('#route_select').show();
			center = map.getCenter();
          google.maps.event.trigger(map, 'resize');         // fixes map display
          map.setCenter(center);
		};
	}
</script>

{include file="parts/panel_start.tpl" title="{t t='Request'}"}        
<form role="form" action="request.php" method="get" accept-charset="utf-8" class="form-horizontal">
	

	<div class="form-group">
	  <label class="col-lg-2 control-label">{t t='Points or Route'}</label>
    <div class="col-lg-10">
      <!-- section selector -->
      <div class="row">
        <div class="col-lg-12">
          <label>
            <input type="radio" name="route" value="0" checked="checked" onclick="change_select(1)" /> {t t='Select a point-to-point connection'}
          </label>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <label>
            <input type="radio" name="route" value="1" onclick="change_select(0)"/> {t t='Select a route. You will be picked up on its start point and dropped at its end point.'}
          </label>
        </div>
      </div>  
    </div>
  </div>  

	{* Location, use default *}
	<input type="hidden" name="location_id" value="{$user->default_location_id}" />
  {include file='parts/input.tpl' name="location_id" description='Location' value=$user->ui->default_location->name static=true} <a href=defaultlocation.php>({t t='Change location'})</a>

  <hr>

  <div id="route_select" style="display:none">  
    
    {* Map *} 
    {include file="parts/map.tpl"}
    
    <div class="form-group">
      <label class="col-lg-2 control-label" for="route">{t t='Route'}</label>
      <div class="col-lg-4" id="route">
        <script type="text/javascript" charset="utf-8">
          $('#route').load('ajax/ajax_routes.php?onlyforuser=1&add_map=1&lid={$user->default_location_id}');
        </script>
      </div>
    </div>
    
    <div class="form-group">
      <div class="col-lg-offset-2 col-lg-10">
	      <div class="checkbox">
          <label>
            <input type="checkbox" name="reverse" id="1"> {t t='I would like to get a lift for the route in reverse direction'}
          </label>
        </div>
      </div>  
    </div>     
  </div>

  <div id="point_select">  
	  <!-- ############### add pick point selection here #################### -->
    
    <div class="form-group">
      <label class="col-lg-2 control-label" for="map_id">Map</label>
      <div class="col-lg-8">
        <p class="form-control-static">
          <div id="map_canvas_points"></div>
          <script type="text/javascript" charset="utf-8">
            var allMarkers = [],
                inRangeMarkers = [];
            var liftRequestCircleBounds, liftRequestCircle, liftRequestMarker;
            var map_p = null;
            var marker_p = null;
            var blueIcon = 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                redIcon = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                greenIcon = 'http://maps.google.com/mapfiles/ms/icons/green-dot.png';
            var myLatlng_p = new google.maps.LatLng(52.45, 13.3);
            var myOptions_p = {
          	  zoom: 9,
          	  center: myLatlng_p,
          	  mapTypeId: google.maps.MapTypeId.ROADMAP
          	};
          	map_p = new google.maps.Map(document.getElementById("map_canvas_points"), myOptions_p);
          	var bounds = new google.maps.LatLngBounds ();
          	
          	{foreach c2r_pickuppoints_get(0, $user->default_location_id) as $point}
          	  {assign 'helper' split(' ', $point->geo)}
          	  lat = '{$helper[0]}';
          		lng = '{$helper[1]}';
          		var myLatlng = new google.maps.LatLng(lat, lng);
          		bounds.extend (myLatlng);
          		marker_p = new google.maps.Marker({
          			map: map_p, 
          			position: myLatlng,
          			draggable: false,
          			title: '{$point->name}',
          			point_id: {$point->id}
          		});
          		allMarkers.push(marker_p);    
          		{if !$other}
          		  marker_p.setIcon(greenIcon);
          		  {assign 'other' 1}
          		  sp_green = marker_p;
          		  ep_green = marker_p;
          		{else}
          		  marker_p.setIcon(redIcon);
          		{/if}


          		google.maps.event.addListener(marker_p, 'click', function() {
          		  var which_point;
          		  if ($('#cg_sp').hasClass('has-warning')) {
          		    which_point = 'start_point';
          		    $('#cg_ep').addClass('has-warning');
          		    $('#cg_sp').removeClass('has-warning');
          		    if (sp_green != ep_green) sp_green.setIcon(redIcon);            		  
          		    sp_green = this;
          		    this.setIcon(greenIcon);            		  
          		  } else {
          		    which_point = 'end_point';
          		    $('#cg_sp').addClass('has-warning');
          		    $('#cg_ep').removeClass('has-warning');
          		    if (sp_green != ep_green) ep_green.setIcon(redIcon);            		  
          		    ep_green = this;
          		    this.setIcon(greenIcon);            		  
          		  };
                $("#"+which_point+" option[value='"+this.point_id+"']").prop("selected", true);
              });
          	{/foreach}
          	map_p.fitBounds (bounds);
          	
          </script>
        </p>
      </div>
    </div>
     
     <div class="form-group has-warning" id="cg_sp">
        <label class="col-lg-2 control-label">{t t='Start point'}</label>
        <div class="col-lg-4" id="sp1">
        </div>
      </div>
      <div class="form-group" id="cg_ep">
        <label class="col-lg-2 control-label">{t t='End point'}</label>
        <div class="col-lg-4" id="ep1">
        </div>
      </div>
      
  </div>

  <hr>

	{* Earliest Starttime *}
  {include file='parts/input.tpl' name="time_earliest" description="{t t='Earliest departure time'}" placeholder='hh:mm' info="{t t='Use 24h format, e.g. 14:00 for 2 p.m.<br />The Earliest departure time will define the begin of your time window.'}"}
  
  {* Latest Starttime *}
  {include file='parts/input.tpl' name="time_latest" description="{t t='Latest departure time'}" placeholder='hh:mm' info="{t t='Use 24h format, e.g. 14:00 for 2 p.m.<br />The Latest departure time will define the end of your time window.'}"}
	
	{* Startdate *}
  {include file='parts/input.tpl' name="start_date" description="{t t='Departure date'}" value=date('Y-m-d') date=true}
  
  {* Phonenumber *}
  {assign 'phonenumbers' c2r_user_number_get($user->id)}
  {if count($phonenumbers) > 1}
    {include file='parts/input.tpl' name="user_number_id" description="{t t='Phone number'}" value='' values=$phonenumbers select=true info="{t t='Select the phone number on which you would like to receive calls for the matching lifts.'}"}
  {else}
    {assign 'phonenumber' array_shift($phonenumbers)}
    <input type="hidden" name="user_number_id" value="{$phonenumber->id}" />
    {include file='parts/input.tpl' name="user_number_id" description=t('Phone number') value=$phonenumber->number static=true}
  {/if}
	
  {* Submit *}
  {include file='parts/panel_submit.tpl' text='request ride' symbol='ok'}
	
</form>

<script type="text/javascript">
	$(function() {
		$("#time_earliest").val(getComputerTime(10));
		$("#time_latest").val(getComputerTime(70));
	});
</script>

		


{include file="parts/panel_end.tpl"}

<p>
	{t t='For more information visit our <a href="https://opencarpool.zendesk.com/hc/" target="_blank">Knowledge Base</a>.'}
</p>
{include file="design/footer.tpl"}


<script type="text/javascript" charset="utf-8">	
	function changed_location() {
		$('#route1').load('ajax/ajax_routes.php?lid={$user->default_location_id}');
		$('#sp1').load('ajax/ajax_points.php?type=sp&lid={$user->default_location_id}');
		$('#ep1').load('ajax/ajax_points.php?type=ep&lid={$user->default_location_id}');
	}
	
	changed_location();
</script>

