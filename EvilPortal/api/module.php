<?php namespace pineapple;

class EvilPortal extends Module
{

    // CONSTANTS
    private $CLIENTS_FILE = '/tmp/EVILPORTAL_CLIENTS.txt';
    private $ALLOWED_FILE = '/pineapple/modules/EvilPortal/data/allowed.txt';
    private $STORAGE_LOCATIONS = array("sd" => "/sd/portals/", "internal" => "/root/portals/");

    // CONSTANTS

    public function route()
    {
        switch ($this->request->action) {
            case 'getControlValues':
                $this->getControlValues();
                break;

            case 'startStop':
                $this->handleRunning();
                break;

            case 'enableDisable':
                $this->handleEnable();
                break;

            case 'portalList':
                $this->handleGetPortalList();
                break;

            case 'portalFiles':
                $this->getPortalFiles();
                break;

            case 'deletePortal':
                $this->handleDeletePortal();
                break;

            case 'deletePortalFile':
                $this->deletePortalFile();
                break;

            case 'activatePortal':
                $this->activatePortal();
                break;

            case 'deactivatePortal':
                $this->deactivatePortal();
                break;

            case 'getPortalCode':
                $this->getPortalCode();
                break;

            case 'submitPortalCode':
                $this->submitPortalCode();
                break;

            case 'getList':
                $this->getList();
                break;

            case 'addToList':
                $this->addToList();
                break;

            case 'removeFromList':
                $this->removeFromList();
                break;

            case 'createNewPortal':
                $this->handleCreateNewPortal();
                break;

            case 'getPortalRules':
                $this->getPortalRules();
                break;

            case 'savePortalRules':
                $this->savePortalRules();
                break;
        }
    }

    public function getPortalCode()
    {
        $portalName = $this->request->name;
        $portalFile = $this->request->portalFile;
        $storage = $this->STORAGE_LOCATIONS[$this->request->storage];

        $message = "";
        $code = "";

        if (file_exists($storage . $portalName . "/" . $portalFile)) {
            $code = file_get_contents($storage . $portalName . "/" . $portalFile);
            $message = $portalFile . " is ready for editting.";
        } else {
            $message = "Error finding " . $storage . $portalName . "/" . $portalFile . ".";
        }

        $this->response = array("message" => $message, "code" => $code);

    }

    public function getPortalFiles()
    {
        $portalName = $this->request->name;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];
        $allFiles = array();
        $nonDeletableBasicFiles = array("MyPortal.php", "helper.php", "index.php", "jquery-2.2.1.min.js");
        $nonDeletableTargetedFiles = array("MyPortal.php", "helper.php", "index.php", "jquery-2.2.1.min.js", "default.php", "route.json");
        if (file_exists($dir . $portalName)) {
            $portal_files = scandir($dir . $portalName);
            $targeted = (file_get_contents($dir . $portalName . "/" . $portalName . ".ep") == "targeted") ? true : false;
            foreach ($portal_files as $file) {
                if (is_file($dir . $portalName . "/" . $file) && !$this->endsWith($file, ".ep")) {
                    if ($targeted) {
                        if (in_array($file, $nonDeletableTargetedFiles)) {
                            $a = array("name" => $file, "deletable" => false);
                            array_push($allFiles, $a);
                        } else {
                            $a = array("name" => $file, "deletable" => true);
                            array_push($allFiles, $a);
                        }
                    } else {
                        if (in_array($file, $nonDeletableBasicFiles)) {
                            $a = array("name" => $file, "deletable" => false);
                            array_push($allFiles, $a);
                        } else {
                            $a = array("name" => $file, "deletable" => true);
                            array_push($allFiles, $a);
                        }
                    }
                    //array_push($allFiles, $file);
                }
            }
        }
        $this->response = array("portalFiles" => $allFiles);
    }

    public function deletePortalFile()
    {
        $portalName = $this->request->portal;
        $fileName = $this->request->name;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];
        $message = "Unable to delete file.";
        if (file_exists($dir . $portalName . "/" . $fileName)) {
            unlink($dir . $portalName . "/" . $fileName);
            $message = "Successfully deleted " . $fileName;
        }

        $this->response = array("message" => $message);

    }

    public function activatePortal()
    {
        $portalName = $this->request->name;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];

        $message = "";
        $portalPath = escapeshellarg($dir . $portalName);
        if (file_exists($dir . $portalName)) {
            exec("ln -s /pineapple/modules/EvilPortal/includes/api /www/captiveportal");
            $portal_files = scandir($dir . $portalName);
            foreach ($portal_files as $file) {
                if (file_exists("/www/{$file}")) {
                    rename("/www/{$file}", "/www/{$file}.ep_backup");
                }
                exec("ln -s {$portalPath}/{$file} /www/{$file}");
            }
            $message = $portalName . " is now active.";
        } else {
            $message = "Couldn't find " . $portalPath . ".";
        }

        $this->response = array("message" => $message);

    }

    public function deactivatePortal()
    {
        $portalName = $this->request->name;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];

        $message = "Couldn't find " . $portalName;
        $deactivateSuccess = false;
        if (file_exists($dir . $portalName)) {
            $portal_files = scandir($dir . $portalName);
            foreach ($portal_files as $file) {
                unlink("/www/{$file}");
            }
            $www_files = scandir("/www/");
            foreach ($www_files as $file) {
                if ($this->endsWith($file, ".ep_backup")) {
                    rename("/www/{$file}", "/www/" . str_replace(".ep_backup", "", $file));
                }
            }
            $message = "Deactivated {$portalName}.";
            $deactivateSuccess = true;
        }

        $this->response = array("message" => $message, "deactivateSuccess" => $deactivateSuccess);

    }

    /* Credits to SteveRusin at http://php.net/manual/en/ref.strings.php */
    private function endsWith($str, $sub)
    {
        return (substr($str, strlen($str) - strlen($sub)) === $sub);
    }

    public function handleDeletePortal()
    {
        $portalName = $this->request->name;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];

        exec("rm -rf " . escapeshellarg($dir . $portalName));

        $message = "";

        if (!file_exists($dir . $portalName)) {
            $message = "Deleted " . $portalName;
        } else {
            $message = "Error deleting " . $portalName;
        }

        $this->response = array("message" => $message);

    }

    public function submitPortalCode()
    {
        $code = $this->request->portalCode;
        $portalName = $this->request->name;
        $fileName = $this->request->fileName;

        $dir = $this->STORAGE_LOCATIONS[$this->request->storage];

        $message = (!file_exists($dir . $portalName . "/" . $fileName)) ? "Created " . $portalName : "Updated " . $portalName;
        file_put_contents($dir . $portalName . "/" . $fileName, $code);
        
        $this->response = array(
            "message" => $message,
            "fullPath" => $dir . $portalName . "/" . $fileName
        );

    }

    public function getPortalRules()
    {
        $portalName = $this->request->name;
        $path = $this->STORAGE_LOCATIONS[$this->request->storage];

        if ($path == null) {
            $this->response = array("message" => "Invalid portal storage", "success" => false);
            return;
        }

        if (is_file($path . $portalName . '/route.json')) {
            $file_contents = json_decode(file_get_contents($path . $portalName . '/route.json'), true);
            $this->response = array(
                "message" => "Found portal rules",
                "data" => $file_contents,
                "success" => true
            );
            return;
        } else {
            $this->response = array("message" => "Unable to find portal.", "success" => false);
            return;
        }

    }

    public function savePortalRules()
    {
        $portalName = $this->request->portal;
        $path = $this->STORAGE_LOCATIONS[$this->request->storage];
        $rules = $this->request->rules;

        if ($path == null) {
            $this->response = array("message" => "Invalid portal storage", "success" => false);
            return;
        }

        if (is_file($path . $portalName . '/route.json')) {
            file_put_contents($path . $portalName . '/route.json', $rules);
            $this->response = array(
                "message" => "Saved portal rules",
                "success" => true
            );
            return;
        } else {
            $this->response = array("message" => "Unable to find portal.", "success" => false);
            return;
        }

    }

    public function handleGetPortalList()
    {
        $internal_path = $this->STORAGE_LOCATIONS['internal'];
        $sd_path = $this->STORAGE_LOCATIONS['sd'];

        if (!file_exists($internal_path)) {
            mkdir($internal_path);
        }

        // create path if it doesn't exist and the SD card is available
        if (!file_exists($sd_path) && $this->isSDAvailable()) {
            mkdir($sd_path);
        }

        $all_portals = array();
        $root_portals = preg_grep('/^([^.])/', scandir($internal_path));

        foreach ($root_portals as $portal) {
            if (!is_file($portal)) {
                $active = (file_exists("/www/{$portal}.ep"));
                $portalType = (trim(file_get_contents($internal_path . $portal . "/" . $portal . ".ep")) == "targeted") ? "targeted": "basic";
                $obj = array("title" => $portal, "location" => "internal", "active" => $active, "type" => $portalType);
                array_push($all_portals, $obj);
            }
        }

        // get portals stored on the sd card
        if ($this->isSDAvailable()) {
            $sd_portals = preg_grep('/^([^.])/', scandir($sd_path));
            foreach ($sd_portals as $portal) {
                if (!is_file($portal)) {
                    $active = (file_exists("/www/{$portal}.ep"));
                    $portalType = (trim(file_get_contents($sd_path . $portal . "/" . $portal . ".ep")) == "targeted") ? "targeted": "basic";
                    $obj = array("title" => $portal, "location" => "sd", "active" => $active, "type" => $portalType);
                    array_push($all_portals, $obj);
                }
            }
        }

        //$active = array("title" => "splash.html", "location" => "active");
        //$active = array();
        //array_push($all_portals, $active);

        $this->response = $all_portals;
    }

    public function handleCreateNewPortal()
    {
        $portalName = strtolower(str_replace(' ', '_', $this->request->portalName));
        $portalType = $this->request->portalType;
        $portalPath = $this->STORAGE_LOCATIONS[$this->request->storage];

        if ($portalPath == $this->STORAGE_LOCATIONS['sd'] && !$this->isSDAvailable()) {
            $this->response = array("create_success" => false, "create_message" => "There is no SD card inserted");
            return;
        }

        if (!file_exists($portalPath)) {
            mkdir($portalPath);
        }

        if (file_exists($portalPath . $portalName)) {
            $this->response = array("create_success" => false, "create_message" => "A portal named {$portalName} already exists.");
            return;
        }

        mkdir($portalPath . $portalName);

        switch ($portalType) {
            case 'targeted':
                exec("cp /pineapple/modules/EvilPortal/includes/targeted_skeleton/* {$portalPath}{$portalName}/");
                file_put_contents($portalPath . $portalName . "/" . $portalName . ".ep", "targeted");
                break;

            default:
                exec("cp /pineapple/modules/EvilPortal/includes/skeleton/* {$portalPath}{$portalName}/");
                file_put_contents($portalPath . $portalName . "/" . $portalName . ".ep", "basic");
                break;
        }

        $this->response = array("create_success" => true, "create_message" => "Created {$portalName}");

    }

    public function handleEnable()
    {
        $response_array = array();
        if (!$this->checkAutoStart()) {
            //exec("/etc/init.d/firewall disable");
            //exec("/etc/init.d/nodogsplash enable");
            copy("/pineapple/modules/EvilPortal/includes/evilportal.sh", "/etc/init.d/evilportal");
            chmod("/etc/init.d/evilportal", 0755);
            exec("/etc/init.d/evilportal enable");
            $enabled = $this->checkAutoStart();
            $message = "EvilPortal is now enabled on startup.";
            if (!$enabled) {
                $message = "Error enabling EvilPortal on startup.";
            }

            $response_array = array(
                "control_success" => $enabled,
                "control_message" => $message
            );

        } else {
            exec("/etc/init.d/evilportal disable");
            //exec("/etc/init.d/firewall enable");
            $enabled = !$this->checkAutoStart();
            $message = "EvilPortal now disabled on startup.";
            if (!$enabled) {
                $message = "Error disabling EvilPortal on startup.";
            }

            $response_array = array(
                "control_success" => $enabled,
                "control_message" => $message
            );
        }
        $this->response = $response_array;
    }

    public function checkCaptivePortalRunning()
    {
        return exec("iptables -t nat -L PREROUTING | grep 172.16.42.1") == '' ? false : true;
    }

    public function startCaptivePortal()
    {

        // Delete client tracking file if it exists
        if (file_exists($this->CLIENTS_FILE)) {
            unlink($this->CLIENTS_FILE);
        }

        // Enable forwarding. It should already be enabled on the pineapple but do it anyways just to be safe
        exec("echo 1 > /proc/sys/net/ipv4/ip_forward");
        exec("ln -s /pineapple/modules/EvilPortal/includes/api /www/captiveportal");

        // Insert allowed clients into tracking file
        $allowedClients = file_get_contents($this->ALLOWED_FILE);
        file_put_contents($this->CLIENTS_FILE, $allowedClients);

        // Configure other rules
        exec("iptables -t nat -A PREROUTING -s 172.16.42.0/24 -p tcp --dport 80 -j DNAT --to-destination 172.16.42.1:80");
        exec("iptables -A INPUT -p tcp --dport 53 -j ACCEPT");

        // Add rule for each allowed client
        $lines = file($this->CLIENTS_FILE);
        foreach ($lines as $client) {
            $this->authorizeClient($client);
            //exec("iptables -t nat -I PREROUTING -s {$client} -j ACCEPT");
        }

        // Drop everything else
        exec("iptables -I INPUT -p tcp --dport 443 -j DROP");

        return $this->checkCaptivePortalRunning();

    }

    private function authorizeClient($client)
    {
        exec("iptables -t nat -I PREROUTING -s {$client} -j ACCEPT");
    }

    private function revokeClient($client)
    {
        exec("iptables -t nat -D PREROUTING -s {$client}");
        exec("iptables -t nat -D PREROUTING -s {$client} -j ACCEPT");
    }

    public function stopCaptivePortal()
    {
        if (file_exists($this->CLIENTS_FILE)) {
            $lines = file($this->CLIENTS_FILE);
            foreach ($lines as $client) {
                $this->revokeClient($client);
                //exec("iptables -t nat -D PREROUTING -s {$client} -j ACCEPT");
            }
            unlink($this->CLIENTS_FILE);
        }

        exec("iptables -t nat -D PREROUTING -s 172.16.42.0/24 -p tcp --dport 80 -j DNAT --to-destination 172.16.42.1:80");
        exec("iptables -D INPUT -p tcp --dport 53 -j ACCEPT");
        exec("iptables -D INPUT -j DROP");

        return $this->checkCaptivePortalRunning();

    }

    public function handleRunning()
    {
        $response_array = array();
        if (!$this->checkCaptivePortalRunning()) {
            //exec("/etc/init.d/nodogsplash start");
            //$running = $this->checkRunning("nodogsplash");
            $running = $this->startCaptivePortal();
            $message = "Started EvilPortal.";
            if (!$running) {
                $message = "Error starting EvilPortal.";
            }

            $response_array = array(
                "control_success" => $running,
                "control_message" => $message
            );
        } else {
            //exec("/etc/init.d/nodogsplash stop");
            //sleep(1);
            //$running = !$this->checkRunning("nodogsplash");
            $running = !$this->stopCaptivePortal();
            $message = "Stopped EvilPortal.";
            if (!$running) {
                $message = "Error stopping EvilPortal.";
            }

            $response_array = array(
                "control_success" => $running,
                "control_message" => $message
            );
        }

        $this->response = $response_array;
    }

    public function getList()
    {
        $response_array = array();
        $contents = null;
        $message = "Successful";
        switch ($this->request->listName) {
            case "whiteList":
                if (!file_exists($this->ALLOWED_FILE)) {
                    $message = "White List file doesn't exist.";
                } else {
                    $contents = file_get_contents($this->ALLOWED_FILE);
                    $contents = ($contents == null) ? "No White Listed Clients" : $contents;
                }
                break;

            case "accessList":
                if (!file_exists($this->CLIENTS_FILE)) {
                    $contents = "No Authorized Clients.";
                } else {
                    $contents = file_get_contents($this->CLIENTS_FILE);
                    $contents = ($contents == null) ? "No Authorized Clients." : $contents;
                }
                break;
        }

        if ($contents != null) {
            $response_array = array(
                "list_success" => true,
                "list_contents" => $contents,
                "list_message" => $message
            );
        } else {
            $response_array = array("list_success" => false, "list_contents" => "", "list_message" => $message);
        }

        $this->response = $response_array;
    }

    public function addToList()
    {
        $valid = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $this->request->clientIP);
        if ($valid) {
            switch ($this->request->listName) {
                case "whiteList":
                    file_put_contents($this->ALLOWED_FILE, $this->request->clientIP . "\n", FILE_APPEND);
                    $this->response = array("add_success" => true, "add_message" => "Successful");
                    break;

                case "accessList":
                    file_put_contents($this->CLIENTS_FILE, $this->request->clientIP . "\n", FILE_APPEND);
                    $this->authorizeClient($this->request->clientIP);
                    $this->response = array("add_success" => true, "add_message" => "Successful");
                    break;

                default:
                    $this->response = array("add_success" => false, "add_message" => "Unkown list.");
                    break;
            }
        } else {
            $this->response = array("add_success" => false, "add_message" => "Invalid IP Address.");
        }

    }

    public function removeFromList()
    {
        $valid = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $this->request->clientIP);
        if ($valid) {
            switch ($this->request->listName) {
                case "whiteList":
                    $data = file_get_contents($this->ALLOWED_FILE);
                    $data = str_replace($this->request->clientIP . "\n", '', $data);
                    file_put_contents($this->ALLOWED_FILE, $data);
                    $this->response = array("remove_success" => true, "remove_message" => "Successful");
                    break;

                case "accessList":
                    $data = file_get_contents($this->CLIENTS_FILE);
                    $data = str_replace($this->request->clientIP . "\n", '', $data);
                    file_put_contents($this->CLIENTS_FILE, $data);
                    $this->revokeClient($this->request->clientIP);
                    $this->response = array("remove_success" => true, "remove_message" => "Successful");
                    break;

                default:
                    $this->response = array("remove_success" => false, "remove_message" => "Unkown list.");
                    break;

            }
        } else {
            $this->response = array("remove_success" => false, "remove_message" => "Invalid IP Address.");
        }
    }

    public function getControlValues()
    {
        $this->response = array(
            //"dependencies" => true,
            "running" => $this->checkCaptivePortalRunning(),
            "autostart" => $this->checkAutoStart(),
            "sdAvailable" => $this->isSDAvailable()
        );
    }

    public function checkAutoStart()
    {
        if (exec("ls /etc/rc.d/ | grep evilportal") == '') {
            return false;
        } else {
            return true;
        }
    }

}
