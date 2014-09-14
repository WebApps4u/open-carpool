<?php
/* Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009-2014 Oliver Pintat, Clemens Rath */

require_once '../../functions/functions.php'; ?>
<?php
$rid = $_GET['rid'];
$route = c2r_routes_get($rid);
$points = c2r_route_points_get($route->id);
$gpoints = array();
foreach ($points as $key => $point) {
	$p = c2r_pickuppoints_get($point->point_id);
	$gpoints[] = $p;
}
?>

<script type="text/javascript">
<!--
var map = null;
var marker = null;
function doInit() {
	<?php $helper = split(' ', $gpoints[count($gpoints)-1]->geo); ?>
  lat = <?php echo $helper[0] ?>;
	lng = <?php echo $helper[1] ?>;
	var myLatlng = new google.maps.LatLng(lat, lng);
	var myOptions = {
	  zoom: 9,
	  center: myLatlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};      		
  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	var bounds = new google.maps.LatLngBounds ();
	  
	var first_point = null;
	var last_point = null;
	var waypts = [];
	<?php foreach ($gpoints as $key => $value): 
		$helper = split(' ', $value->geo); ?>
	  lat = '<?php echo $helper[0] ?>';
		lng = '<?php echo $helper[1] ?>';
		var myLatlng = new google.maps.LatLng(lat, lng);
		bounds.extend (myLatlng);
		//new google.maps.Marker({
		//	map: map, 
		//	position: myLatlng,
		//	draggable: false
		//});
		if (!first_point) { 
		  first_point = myLatlng; 
		} else {
		  waypts.push({ location: myLatlng, stopover:true});
		}
		last_point = myLatlng;
		
	<?php endforeach ?>
  
  // remove last Element fom waypoint, because this is the endpoint
  waypts.pop();
  map.fitBounds(bounds);
  
  
  var directionsDisplay;
  var directionsService = new google.maps.DirectionsService();
  directionsDisplay = new google.maps.DirectionsRenderer();
  directionsDisplay.setMap(map);
  var request = {
    origin:first_point,
    destination:last_point,
    waypoints: waypts,
    travelMode: google.maps.TravelMode.DRIVING
  };
  directionsService.route(request, function(response, status) {
    if (status == google.maps.DirectionsStatus.OK) {
      directionsDisplay.setDirections(response);
    }
  });
  
}
doInit();
//-->
</script>
