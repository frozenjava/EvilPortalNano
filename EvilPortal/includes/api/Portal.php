<?php namespace evilportal;

abstract class Portal
{
	protected $request;
	protected $response;
	protected $error;

	protected $AUTHORIZED_CLIENTS_FILE = "/tmp/EVILPORTAL_CLIENTS.txt";

	abstract public function route();

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getResponse()
	{
		if (empty($this->error) && !empty($this->response)) {
            return $this->response;
        } elseif (empty($this->error) && empty($this->response)) {
            return array('error' => 'API returned empty response');
        } else {
            return array('error' => $this->error);
        }
	}

	protected function authorizeClient($clientIP) 
	{
		if (!$this->isClientAuthorized($clientIP)) {
			exec("iptables -t nat -I PREROUTING -s {$clientIP} -j ACCEPT");
			file_put_contents($this->AUTHORIZED_CLIENTS_FILE, "{$clientIP}\n", FILE_APPEND);
			return true;
		} else {
			return false;
		}
	}

	protected function isClientAuthorized($clientIP)
	{
		$authorizeClients = file_get_contents($this->AUTHORIZED_CLIENTS_FILE);
		return strpos($authorizeClients, $clientIP);
	}

}
