<?php namespace evilportal;

abstract class Portal
{
    protected $request;
    protected $response;
    protected $error;

    protected $AUTHORIZED_CLIENTS_FILE = "/tmp/EVILPORTAL_CLIENTS.txt";
    private $BASE_EP_COMMAND = '/pineapple/modules/EvilPortal/executable/executable';

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
            //exec("iptables -t nat -I PREROUTING -s {$clientIP} -j ACCEPT");
            exec("{$this->BASE_EP_COMMAND} add {$clientIP}");
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
    #I think this can now be deleted, we do not want a message that we are authorized we want to be online right away!
    protected function showSuccess()
    {
        echo "You have been authorized successfully.";
    }

    protected function showError()
    {
        echo "You have not been authorized.";
    }

    protected function isClientAuthorized($clientIP)
    {
        $authorizeClients = file_get_contents($this->AUTHORIZED_CLIENTS_FILE);
        return strpos($authorizeClients, $clientIP);
    }
}
