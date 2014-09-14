<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../../functions/functions.php';
require_once '../../functions/gravatar.php';

// user default_location_id from actual user
$lid = $user->default_location_id;

$data = '{"method": "departure_timetable_get", "params": [ '.$lid.' ], "id": 0}';
$resultString = post_getstring($data);
$result = json_decode($resultString);

if ($result->error == null) {
    echo '{"error": null, "id": '.$result->id.', "result": [';
    $length = count($result->result);
    $oldId = 0;
    $newRow = true;
    for ($rowIndex = 0; $rowIndex < $length; $rowIndex++) {
        $row = $result->result[$rowIndex];
        $id = $row[0];
        if ($id != $oldId && $rowIndex > 0) {
            echo '{';
            echo '"time": "'.$time.'",';
            echo '"route": "'.implode(" - ", $points).'",';
            echo '"gravatarUrl": "'.$gravatarUrl.'",';
            echo '"driver": "'.$driver.'",';
            echo '"phone": "'.$phone.'"';
            echo '},';
            $newRow = true;
        }

        $oldId = $id;

        if ($newRow) {
            $time = $row[1];
            $points = array();
            $driver = $row[3];
            $phone = $row[4];
            $gravatarUrl = get_gravatar($row[5], $s=32);
            $newRow = false;
        } else {
            $points[] = $row[2];
        }
    }
    echo '{';
    echo '"time": "'.$time.'",';
    echo '"route": "'.implode(" - ", $points).'",';
    echo '"gravatarUrl": "'.$gravatarUrl.'",';
    echo '"driver": "'.$driver.'",';
    echo '"phone": "'.$phone.'"';
    echo '}]}';
} else {
    echo $resultString;
}

