<?php

class appAuthListener extends jEventListener
{
    public function onsamlAccountCreated($event)
    {
        jLog::log('User created: '.$event->user->login .' ('.$event->user->email.')');
    }
}
