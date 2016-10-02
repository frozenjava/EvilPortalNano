<?php
require_once('helper.php');

// Get the information about the client and map
// it to the rule key specified in the route.json file
$MAPPED_RULES = [
    "mac" => getClientMac($_SERVER['REMOTE_ADDR']),
    "ssid" => getClientSSID($_SERVER['REMOTE_ADDR']),
    "hostname" => getClientHostName($_SERVER['REMOTE_ADDR']),
    "useragent" => $_SERVER['HTTP_USER_AGENT']
];

// Read the json
$routeData = json_decode(file_get_contents("route.json"), true);

// This variable represents the page to include
$includePage = null;

// Check rules to find the page
foreach ($routeData['rule_order'] as $key) {
    $includePage = handle_rule($routeData['rules'][$key], $MAPPED_RULES[$key]);
    if ($includePage != null) {
        include $includePage;
        break;
    }
}

// We have to display something.
// If the includePage variable is still null after checking the rules
// then include the default page.
if ($includePage == null) {
    include $routeData['default'];
}

/**
 * Checks if a given rule matches a given value
 * @param $rules: The rules to check the client data against
 * @param $client_data: The data to check if the rules match
 * @return string: If a rule matches it returns the page to include, null otherwise
 */
function handle_rule($rules, $client_data) {
    $return_value = null;
    foreach ($rules as $key => $val) {
        switch($key) {
            case "exact": // exact matches
                if (isset($val[$client_data])) {
                    $return_value = $val[$client_data];
                    break 2; // break out of the loop
                }
                break 1;

            case "regex": // regex matches
                foreach($val as $expression => $destination) {
                    if (preg_match($expression, $client_data)) {
                        $return_value = $destination;
                        break 1; // match was found. Exit this loop
                    }

                    if ($return_value != null)
                        break 2; // break out of the main loop
                }
                break 1;
        }
    }
    return $return_value;
}
