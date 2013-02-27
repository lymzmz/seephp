<?php

class see_ctl_base_member extends see_app_controller {

    public function authGroup()
    {
        return array('member');
    }

    public function index()
    {
        echo '你已经登录';
    }

}
