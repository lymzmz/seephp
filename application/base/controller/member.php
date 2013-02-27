<?php

class see_ctl_base_member extends see_app_controller {

    public function authGroup()
    {
        $group = parent::authGroup();
        unset($group[0]);
        return $group;
    }

    public function index()
    {
        echo '你已经登录';
    }

}
