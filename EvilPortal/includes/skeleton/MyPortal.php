<?php namespace evilportal;

class MyPortal extends Portal
{

    public function handleAuthorization()
    {
        if (isset($this->request->target)) {
            $this->authorizeClient($_SERVER['REMOTE_ADDR']);
            $this->showSuccess();
        } elseif ($this->isClientAuthorized($_SERVER['REMOTE_ADDR'])) {
            $this->showSuccess();
        } else {
            $this->showError();
        }
    }

    public function showSuccess()
    {
        //Calls default function, override here
        parent::showSuccess();
    }

    public function showError()
    {
        //Calls default function, override here
        parent::showError();
    }
}
