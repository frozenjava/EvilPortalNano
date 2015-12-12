<?php namespace pineapple;

class EvilPortal extends Module
{
	public function route()
	{
		switch($this->request->action) {
			case 'getControlValues':
				$this->getControlValues();
				break;

			case 'handleDepends':
				$this->handleDepends();
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
		}
	}

	public function handleGetPortalList() {
		if (!file_exists("/root/portals"))
			mkdir("/root/portals");

		$all_portals = array();
		$root_portals = preg_grep('/^([^.])/', scandir("/root/portals"));

		foreach ($root_portals as $portal) {
			$obj = array("title" => $portal, "location" => "/root/portals");
			array_push($all_portals, $obj);
		}

		$this->response = $all_portals;
	}

	public function handleEnable() {
		$response_array = array();
		if (!$this->checkAutoStart()) {
			exec("/etc/init.d/nodogsplash enable");
			$enabled = $this->checkAutoStart();
			$message = "NoDogSplash is now enabled on startup.";
			if (!$enabled)
				$message = "Error enabling NoDogSplash on startup.";

			$response_array = array(
					"control_success" => $enabled,
					"control_message" => $message
				);

		} else {
			exec("/etc/init.d/nodogsplash disable");
			$enabled = !$this->checkAutoStart();
			$message = "NoDogSplash now disabled on startup.";
			if (!$enabled)
				$message = "Error disabling NoDogSplash on startup.";

			$response_array = array(
					"control_success" => $enabled,
					"control_message" => $message
				);
		}
		$this->response = $response_array;
	}

	public function handleRunning() {
		$response_array = array();
		if (!$this->checkRunning("nodogsplash")) {
			exec("/etc/init.d/nodogsplash start");
			$running = $this->checkRunning("nodogsplash");
			$message = "Started NoDogSplash.";
			if (!$running)
				$message = "Error starting NoDogSplash.";

			$response_array = array(
					"control_success" => $running,
					"control_message" => $message
				);
		} else {
			exec("/etc/init.d/nodogsplash stop");
			sleep(1);
			$running = !$this->checkRunning("nodogsplash");
			$message = "Stopped NoDogSplash.";
			if (!$running)
				$message = "Error stopping NoDogSplash.";

			$response_array = array(
					"control_success" => $running,
					"control_message" => $message
				);
		}

		$this->response = $response_array;
	}

	public function handleDepends() {
		$response_array = array();
		if (!$this->checkDependency("nodogsplash")) {
			$installed = $this->installDependency("nodogsplash");
			$message = "Successfully installed dependencies.";
			if (!$installed) {
				$message = "Error installing dependencies.";
			} else {
				$this->uciSet("nodogsplash.@instance[0].enabled", true);
				$this->uciAddList("nodogsplash.@instance[0].users_to_router", "allow tcp port 1471");
			}
			$response_array = array(
					"control_success" => $installed,
					"control_message" => $message
				);
			
		} else {
			exec("opkg remove nodogsplash");
			$removed = !$this->checkDependency("nodogsplash");
			$message = "Successfully removed dependencies.";
			if ($installed) {
				$message = "Error removing dependencies.";
			}
			$response_array = array(
					"control_success" => $removed,
					"control_message" => $message
				);
		}

		$this->response = $response_array;

	}

	public function getControlValues() {
		$this->response = array(
				"dependencies" => $this->checkDependency("nodogsplash"),
				"running" => $this->checkRunning("nodogsplash"),
				"autostart" => $this->checkAutoStart()
			);
	}

	public function checkAutoStart() {
		if (exec("ls /etc/rc.d/ | grep nodogsplash") == '') {
			return false;
		} else {
			return true;
		}
	}

	public function checkDepends() {
		$splash = true;
		if (exec("opkg list-installed | grep nodogsplash") == '') {
    		$splash = false;
		}
    	return $splash;
	}

	public function checkRunning() {
		if (exec("ps | grep -v grep | grep -o nodogsplash") == '') {
			return false;
		} else {
			return true;
		}
	}

}