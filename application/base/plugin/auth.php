<?php

class see_plg_base_auth extends see_app_plugin {

    public function info( $user_id )
    {
        return array(
                'username' => 'rick',
                'password' => md5('rick'),
                'group' => 'member'
            );
    }

}
