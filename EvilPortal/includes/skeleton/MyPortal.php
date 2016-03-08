<?php namespace evilportal;

class MyPortal extends Portal
{

	public function route()
	{

		switch($this->request->action) {
			case 'authorize':
				$this->handleAuthorization();
				break;
		}

	}

	private function handleAuthorization()
	{
		$success = $this->authorizeClient($_SERVER['REMOTE_ADDR']);
		$this->response = array("authorized" => $success);
	}

}