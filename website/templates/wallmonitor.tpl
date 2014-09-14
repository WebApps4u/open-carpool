<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Open CarPool Departures</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="lib/bootstrap/dist/css/bootstrap.css">
        <link rel="stylesheet" href="css/wallmonitor.css">
    </head>
    <body>
        <header>
            <button id="fullScreen">full screen</button>
            {if $ocp_logourl}<a href="/index.php"><img id="ocp_logo" src="{$ocp_logourl}"></a>{/if}
            <h1>Open CarPool Departures</h1>
        </header>
        
        <div id="timetable" class="container-fluid">
            <div id="timetable-headers" class="row">
                <div class="col-md-1">Time</div>
                <div class="col-md-6">Route</div>
                <div class="col-md-3">Driver</div>
                <div class="col-md-2">Phone</div>
            </div>
            <span id="offers">
            </span>
        </div>
        
        <script src="lib/bootstrap/assets/js/jquery.js"></script>
        <script src="js/wallmonitor-timetable.js"></script>
        <script src="js/wallmonitor-init.js"></script>
    </body>
</html>