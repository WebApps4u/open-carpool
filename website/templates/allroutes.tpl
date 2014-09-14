{include file='design/header.tpl' titletag="{t t='All Routes'}" maps=true}

<div class="row">
  <div class="col-lg-4">
    <h1>{t t='Public Routes'}</h1>
    <h3>{$user->ui->default_location->name}<br><small>(<a href="defaultlocation.php">{t t='change default location'}</a>)</small></h3>
    <p>
      {t t='Hover over the route in the list below to highlight it on the map.'}
    </p>
    <table class="table table-condensed table-striped table-hover table-datatable">
      <thead>
        <tr>
			    <th>{t t='Name'}</th>
			    <th>{t t='Route ID'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach $routes as $rid => $route} 
        <tr id="tr_{$rid}">  
    	    <td>{$route->origin}</td>
    	    <td>{$route->key}</td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    
    <div id="log">
    </div>
  </div>
  <div class="lg-col-8">
    <div id="map_canvas2" style="height:400px"></div>
  </div>
</div>

<script>
var map;
var directionsDisplay;
var directionsService = new google.maps.DirectionsService();
var request;
var dirArray = Array();
var dirArrayDisplay = Array();

function initialize() {
  var mapOptions = {
    zoom: 1,
    center: new google.maps.LatLng(52.45, 13.3),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map = new google.maps.Map(document.getElementById('map_canvas2'),
      mapOptions);      
  var bounds = new google.maps.LatLngBounds ();
  
  {foreach $routes as $rid => $route} 
    /* ****************** {$route->name} ***************** */
    {assign 'route_points_array' c2r_route_points_get($rid)}
    {assign 'first_point' array_shift($route_points_array)}
    {assign 'first_pp' c2r_pickuppoints_get($first_point->point_id)}
    {assign 'first_geo_array' split(' ', $first_pp->geo)}
    {assign 'first_geo' "{$first_geo_array[0]},{$first_geo_array[1]}"}
    {assign 'last_point' array_pop($route_points_array)}    
    {assign 'last_pp' c2r_pickuppoints_get($last_point->point_id)}
    {assign 'last_geo_array' split(' ', $last_pp->geo)}
    {assign 'last_geo' "{$last_geo_array[0]},{$last_geo_array[1]}"}
    
    {if $first_geo_array[0] && $first_geo_array[1]}
      bounds.extend(new google.maps.LatLng({$first_geo_array[0]},{$first_geo_array[1]}));
    {else}
      /* ERROR First Point {$route->name} */
    {/if}
    {if $last_geo_array[0] && $last_geo_array[1]}
      bounds.extend(new google.maps.LatLng({$last_geo_array[0]},{$last_geo_array[1]}));
    {else}
      /* ERROR Last Point {$route->name} */
    {/if}
      
    var waypts = [];
    {foreach $route_points_array as $rp}
      {assign 'rp_pp' c2r_pickuppoints_get($rp->point_id)}
      {assign 'rp_geo_array' split(' ', $rp_pp->geo)}
      {assign 'rp_geo' "{$rp_geo_array[0]},{$rp_geo_array[1]}"}
    
      {if $rp_geo_array[0] && $rp_geo_array[1]}
        bounds.extend(new google.maps.LatLng({$rp_geo_array[0]},{$rp_geo_array[1]}));
      {else}
        /* ERROR  Waypoint {$route->name} */
      {/if}
      
      waypts.push({
        location: '{$rp_geo}',
        stopover:true});
    {/foreach}
    
    request = {
         origin: '{$first_geo}',
         destination: '{$last_geo}',
         waypoints: waypts,
         //optimizeWaypoints: true,
         travelMode: google.maps.TravelMode.DRIVING
     };
     directionsService.route(request, function(response, status) {
         if (status == google.maps.DirectionsStatus.OK) {
           dirArray['tr_{$rid}'] = response;
           dirArrayDisplay['tr_{$rid}'] = showRoute('blue', response);
         }
       });
  {/foreach}
  
  map.fitBounds(bounds);
}

function showRoute(color, response) {
  {literal}var directionsDisplay = new google.maps.DirectionsRenderer({preserveViewport: true, polylineOptions:{strokeColor: color}});{/literal}
  directionsDisplay.setMap(map);
  directionsDisplay.setDirections(response);
  return directionsDisplay;
}

$(window).load(function () { 
  initialize();
  $("tbody tr").mouseenter(function() {
    response = dirArray[$(this).attr('id')];
    //dirArrayDisplay[$(this).attr('id')].setMap(null);
    for (var x in dirArrayDisplay) {
      dirArrayDisplay[x].setMap(null);
    }
    dirArrayDisplay[$(this).attr('id')] = showRoute('red', response);    
  });
  $("tbody tr").mouseleave(function() {
    //response = dirArray[$(this).attr('id')];
    try {
      dirArrayDisplay[$(this).attr('id')].setMap(null);
    } catch(e) {}
    //dirArrayDisplay[$(this).attr('id')] = showRoute('blue', response);    
    for (var x in dirArrayDisplay) {
      response = dirArray[x];
      try { 
        dirArrayDisplay[x] = showRoute('blue', response);
      } catch(e) {}
    }
  });
});
  
</script>

{include file='design/footer.tpl'}