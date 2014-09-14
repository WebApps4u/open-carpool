<?php
/* Standard include */
require_once '../functions/functions.php';

/* Check User rights */
checkMinGroup(1);

if (count($_GET)) {
	$sdate = explode('-', $_GET['start_date']);
	$sdate = join('', $sdate);
	$time_earliest = checkTime($_GET['time_earliest']);
	$time_latest = checkTime($_GET['time_latest']);
	$has_error = false;
	if (!only_digits($time_earliest)) {
		$has_error = true;
		$e_msg = 'Time earliest: '.$time_earliest;
		addErrorMessage($e_msg);
		c2r_log_error('Request time format', $e_msg.' - '.$_GET['time_earliest']);
	}
	if (!only_digits($time_latest)) {
		$has_error = true;
		$e_msg = t('Time latest: ').$time_latest;
	  addErrorMessage($e_msg);
		c2r_log_error('Request time format', $e_msg.' - '.$_GET['time_latest']);
	}
	// Check if earliest before latest
	if (!$has_error && $time_latest < $time_earliest) {
		$has_error = true;
		$e_msg = t('The latest start time is before earliest start time: ').$time_latest.' &lt; '.$time_earliest.t(' Please change the order.');
		addErrorMessage($e_msg);
		c2r_log_error('Request time order', $e_msg);
	}
	if (!$_GET['route'] && $_GET['start_point'] == $_GET['end_point']) {
		$has_error = true;
		$e_msg = t('Start- and end point are the same. Please select different points.');
		addErrorMessage($e_msg);
		c2r_log_error('Request', $e_msg);
	}
	if (!$has_error) {
		if ($_GET['route']) {
			$r = c2r_routes_get($_GET['route_id']);
  			$res = c2r_request_ride_route($_GET['user_number_id'], $_GET['location_id'], $r->key, $_GET['reverse'], $time_earliest, $time_latest, $sdate);
		} else {
  			$sp = c2r_pickuppoints_get($_GET['start_point']);
			$ep = c2r_pickuppoints_get($_GET['end_point']);
  			$res = c2r_request_ride($_GET['user_number_id'], $_GET['location_id'], $sp->key, $ep->key, $time_earliest, $time_latest, $sdate);
		}
		if ($res) {
			addInfoMessage($res);
			header("Location: ".OCP_BASE_URL."matchingoffers.php"); /* Redirect browser */
    	exit;
		} else {
			addErrorMessage(t('An error occurred'));
		}
	}	
}

smarty_display('request');
exit;

$title = 'Request';
$page_id = 'index';
include 'design/header.php';

?>

<script type="text/javascript" charset="utf-8">
	function change_select (val) {
		if (val) {
			$('#point_select').hide();
			$('#route_select').show();
		};
		if (!val) {
			$('#point_select').show();
			$('#route_select').hide();
		};
	}
	
	function changed_location() {
		$('#route1').load('ajax/ajax_routes.php?lid='+$('#location_id').val());
		$('#sp1').load('ajax/ajax_points.php?type=sp&lid='+$('#location_id').val());
		$('#ep1').load('ajax/ajax_points.php?type=ep&lid='+$('#location_id').val());
	}
</script>

<h1>{t t='Request a Ride'}</h1>

<?php messages($msgs, $errors) ?>

<form action="request.php" method="get">

	<fieldset>  
	<legend>{t t='Main Info'}</legend>

	<ol>
	
	<li>	
	<label for="user_number_id">{t t='Phone number'}</label>
	<?php global $user; echo c2r_getHmtlSelectUserNumbers($user->id) ?><br/>
	</li>
	
	<li>
	<label for="location_id">{t t='Location'}</label>
	<?php 
		$locations = c2r_locations_get();
		echo c2r_getHtmlSelect("location_id", $locations, 'name', null, 'onchange="changed_location()"');
	?>
	</li>

	<li>
	<label for="time_earliest">{t t='Earliest departure time'}</label>
	<input type="text" name="time_earliest" /> (hh:mm)
	</li>
	
	<li>
	<label for="time_latest">{t t='Latest departure time}</label> 
	<input type="text" name="time_latest" /> (hh:mm)
	</li>
	
	<li>
	<label for="start_date">{t t='Departure date'}</label> 
	<input type="text" name="start_date" id="start_date" value="<?php echo date('Y-m-d') ?>" />
	</li>
	<script type="text/javascript">
	$(function() {
		$("#start_date").datepicker({ dateFormat: 'yy-mm-dd' });
	});
	</script>

	<li>
	{t t='Select by points'}<input type="radio" name="route" value="0" checked="checked" onclick="change_select(0)" />
	{t t='Route'}<input type="radio" name="route" value="1" onclick="change_select(1)"/>
	</li>

	</fieldset>

	<fieldset id="point_select" style="display:block">  
	<legend>{t t='Points'}</legend>
	<ol>
		<li id="sp1" ></li>
		<li id="ep1" ></li>
	</ol>
	</fieldset>

	<fieldset id="route_select" style="display:none">  
	<legend>{t t='Route'}</legend>
	<ol>
	<li id="route1"></li>
	
	<li>
	<label for="reverse">{t t='Reverse'}</label>
	<input type="checkbox" name="reverse" value="1" id="reverse"/>
	</li>
	
	</ol>
	</fieldset>
	
	</ol>
	</fieldset>
	
	<fieldset class="submit">  
		<input type="submit" name="submit" value="request ride" />
	</fieldset>

</form>

<script type="text/javascript" charset="utf-8">
	changed_location();
</script>
		
<?php 

	include 'design/footer.php'; 

?>
