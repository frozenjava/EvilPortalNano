<?php namespace evilportal;

abstract class Portal
{
    protected $request;
    protected $response;
    protected $error;

    protected $AUTHORIZED_CLIENTS_FILE = "/tmp/EVILPORTAL_CLIENTS.txt";
    private $BASE_EP_COMMAND = 'module EvilPortal';

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

    protected function execBackground($command)
    {
        return exec("echo \"{$command}\" | at now");
    }

    protected function authorizeClient($clientIP)
    {
        if (!$this->isClientAuthorized($clientIP)) {
            exec("iptables -t nat -I PREROUTING -s {$clientIP} -j ACCEPT");
//            exec("{$this->BASE_EP_COMMAND} add {$clientIP}");
            file_put_contents($this->AUTHORIZED_CLIENTS_FILE, "{$clientIP}\n", FILE_APPEND);
            $this->redirect();
            return true;
        } else {
            return false;
        }
    }

    protected function handleAuthorization()
    {
        if (isset($this->request->target)) {
            $this->authorizeClient($_SERVER['REMOTE_ADDR']);
            $this->onSuccess();
            $this->redirect();
        } elseif ($this->isClientAuthorized($_SERVER['REMOTE_ADDR'])) {
            $this->redirect();
        } else {
            $this->showError();
        }
    }

    protected function redirect()
    {
        header("Location: {$this->request->target}", true, 302);
    }

    /**
     * Override this to do something when the client is successfully authorized.
     * By default it just notifies the Web UI.
     */
    protected function onSuccess()
    {
        $this->execBackground("notify New client authorized through EvilPortal!");
    }

    /**
     * If an error occurs then do something here.
     * Override to provide your own functionality.
     */
    protected function showError()
    {
        echo "You have not been authorized.";
    }

    /**
     * Checks if the client has been authorized.
     * @param $clientIP: The IP of the client to check.
     * @return bool|int: True if the client is authorized else false.
     */
    protected function isClientAuthorized($clientIP)
    {
        $authorizeClients = file_get_contents($this->AUTHORIZED_CLIENTS_FILE);
        return strpos($authorizeClients, $clientIP);
    }
}
