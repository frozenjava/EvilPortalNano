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
		}
	}

	public function handleDepends() {
		$this->response = array(
			"error" => "Error installing dependencies."
		);
	}

	public function getControlValues() {
		$this->response = array(
				"dependencies" => $this->checkDepends(),
				"running" => $this->checkRunning(),
				"autostart" => $this->checkAutoStart()
			);
	}

	public function checkDepends() {
		$splash = true;
		if (exec("opkg list-installed | grep nodogsplash") == '') {
    		$splash = false;
		}
    	return $splash;
	}

	public function checkRunning() {
		if (exec("ps -aux | grep -v grep | grep -o nodogsplash") == '') {
			return false;
		} else {
			return true;
		}
	}

	public function checkAutoStart() {
		if (exec("ls /etc/rc.d/ | grep nodogsplash") == '') {
			return false;
		} else {
			return true;
		}
	}

	public function installDepends() {
		exec("opkg update");
		exec("opkg install nogdogsplash");
		if ($this->checkDepends()) {
			$this->response = array(
				"dependencies" => "Successfully installed dependencies."
			);
		} else {
			$this->response = array(
				"error" => "Error installing dependencies."
			);
		} 
	}

}