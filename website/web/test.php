<?php

require_once '../functions/functions.php';

$data = '{"method": "get_user_info", "params": [183], "id": 0}';
echo "$data<hr>";

$result = post($data, true);
echo "<hr><pre>";

print_r($result);

echo "</pre>";
?>