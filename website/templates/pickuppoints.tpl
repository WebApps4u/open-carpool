{include file='design/header.tpl' title="Pick-Up Points" maps=true}



<script type="text/javascript">
<!--

var map = null;
var map_insert = null;
var marker = null;

$(function () {
  $('#toggle_new_point').click(function() { 
    window.setTimeout("google.maps.event.trigger(map_insert, 'resize');map_insert.setCenter(new google.maps.LatLng(51, 11));", 200);
  });
});

function doInit() {
	var myLatlng = new google.maps.LatLng(51, 11);
	var myOptions = {
	  zoom: 4,
	  center: myLatlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};      		
  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	var mpos = map.getCenter();
	if ('{$pickuppoints[$edit_pid]->geo}') {
		{assign 'helper' split(' ', $pickuppoints[$edit_pid]->geo)}
	    lat = '{$helper[0]}';
    	lng = '{$helper[1]}';
		mpos = new google.maps.LatLng(lat, lng);
	}
	marker = new google.maps.Marker({
		map: map, 
		position: mpos,
		draggable: true
	});
	map.setCenter(mpos);

	google.maps.event.addListener(marker, 'dragend', function() {
	 	var pos = marker.getPosition();
		lat = pos.lat();
		lng = pos.lng();
		document.getElementById("geo_e").value = lat+" "+lng;
	});

}

function doInitInsert() {
	var xmyLatlng = new google.maps.LatLng(51, 11);
	var xmyOptions = {
	  zoom: 3,
	  center: xmyLatlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};    
  map_insert = new google.maps.Map(document.getElementById("map_canvas_insert"), xmyOptions);

	var mpos_insert = map_insert.getCenter();	
	marker_insert = new google.maps.Marker({
		map: map_insert, 
		position: mpos_insert,
		draggable: true
	});
	map_insert.setCenter(new google.maps.LatLng(51, 11));
  
	google.maps.event.addListener(marker_insert, 'dragend', function() {
	 	var pos = marker_insert.getPosition();
		lat = pos.lat();
		lng = pos.lng();
		document.getElementById("geo").value = lat+" "+lng;
	});
}
//-->
</script>

{if ($edit_pid)}
  {include file="parts/panel_start.tpl" title='Edit Pickuppoint'}
  <form role="form" action="pickuppoints.php" method="post" accept-charset="utf-8" class="form-horizontal">
    <input type="hidden" name="do" value="update" id="do"/>
    <input type="hidden" name="pid" value="{$edit_pid}" id="pid"/>
      		
    {* Name *}
    {include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value=$pickuppoints[$edit_pid]->name}

    {* Location *}
    {include file='parts/input.tpl' name="lid_e" description="Location" value=$pickuppoints[$edit_pid]->lid values=$locations select=true extra="onchange=\"$('#key_e').load('ajax/ajax_keys_points.php?pid=$edit_pid&lid='+$('#lid_e').val())\""}
    
    {* Geo *}
    {include file='parts/input.tpl' name="geo_e" description="Geo" placeholder="Enter Geo" value=$pickuppoints[$edit_pid]->geo}
          
    <div class="form-group">
      <label class="col-lg-2 control-label" for="map_id">Map</label>
      <div class="col-lg-4">
        <p class="form-control-static">
          <div id="map_canvas" style="width:100%; height:285px; border:1px dotted">
            <script type="text/javascript">
              doInit();
            </script>
          </div>
        </p>
      </div>
    </div>
    			
    {* Key *}
    {include file="parts/input.tpl" name="key" description="Key" value=$pickuppoints[$edit_pid]->key static=true}
    <input type="hidden" name="key" value="{$pickuppoints[$edit_pid]->key}" />
          
    {* Submit *}
    {include file="parts/panel_submit.tpl" symbol="ok" text="Update Pickuppoint"}
          
  </form>
  {include file="parts/panel_end.tpl"}
{/if}

<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
    <tr>
      <th>ID</th>
			<th>Name</th>
			<th>Location</th>
			<th>Geo</th>
			<th>Key</th>
			<th>Action</th>
    </tr>
  </thead>
  <tbody>
{foreach $pickuppoints as $pid => $point} 
	{assign 'location' $locations[$point->lid]}
  <tr>
		<td>{$point->id}</td>
		<td>{$point->name}</td>
		<td>{$location->name}</td>
		<td>{$point->geo}</td>
		<td>{$point->key}</td>
		<td>
		  <a class="btn btn-primary btn-xs" href="pickuppoints.php?do=edit&amp;pid={$point->id}"><span class="glyphicon glyphicon-edit"></span></a> 
		<!-- should be replaced by a desctivate function instead of deletion in order to maintain referential integrity ---------
		 <a class="btn btn-danger btn-xs" onclick="return confirm('Really delete {$point->name}')" href="pickuppoints.php?do=delete&amp;pid={$point->id}"><span class="glyphicon glyphicon-trash"></span></a>
		 -->
		</td>
  </tr>
{/foreach}
  </tbody>
</table>

{include file="parts/toggle.tpl" id='new_point' text='New Pickuppoint'}

{include file="parts/panel_start.tpl" id='new_point' title='New Pickuppoint' hidden=true}
<form role="form" action="pickuppoints.php" method="post" accept-charset="utf-8" class="form-horizontal">
  <input type="hidden" name="do" value="insert" id="do"/>
        	
  {* Name *}
  {include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value='' info='Do not use special characters or umlauts such as &Uuml;, &szlig; or &eacute; for the route point name!'}

  {* Location *}
  {include file='parts/input.tpl' name="lid" description="Location" value='' values=$locations select=true extra="onchange=\"$('#key').load('ajax/ajax_keys_points.php?lid='+$('#lid').val())\""}
    
  {* Geo *}
  {include file='parts/input.tpl' name="geo" description="Geo" placeholder="Enter Geo" value='' info='Enter the geo coordinates by using the map below. In case the map doesn`t show the right area or is missing the pointer, please resize the browser window and map.'}
                    
  <div class="form-group">
    <label class="col-lg-2 control-label" for="map_id">Map</label>
    <div class="col-lg-4">
      <p class="form-control-static">
        <div id="map_canvas_insert" style="width:100%; height:285px; border:1px dotted">
          <script type="text/javascript">
            doInitInsert();
          </script>
        </div>
      </p>
    </div>
  </div>
          
  <div class="form-group">
    <label class="col-lg-2 control-label" for="key">Key</label>
    <div class="col-lg-4" id="key1">
      <script type="text/javascript" charset="utf-8">
        $('#key1').load('ajax/ajax_keys_points.php?lid='+$('#lid').val());
      </script>
    </div>
  </div>

  {* Submit *}
  {include file="parts/panel_submit.tpl" simbol="ok" text="Save Pickuppoint"}
          
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}