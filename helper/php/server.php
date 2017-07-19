<?php

/* set timezone */
date_default_timezone_set('Europe/Berlin');

/* collect some informations */
$load         = sys_getloadavg();
$time         = time();
$timeFormated = date('Y-m-d H:i:s');
$appName      = exec('friends-of-bash version apache-host-viewer');
$osName       = exec('friends-of-bash osName');

$response = array();

$response = array_merge(
    $response,
    array(
        'timezone'            => date_default_timezone_get(),
        'created-at-formated' => $timeFormated,
        'created-at'          => $time,
        'created-by'          => $appName,
        'os-name-full'        => $osName,
        'load-average'        => array(
            1  => $load[0],
            5  => $load[1],
            15 => $load[2],
        )
    )
);

/* return the response object */
echo json_encode($response);

