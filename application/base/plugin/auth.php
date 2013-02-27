<?php

class see_plg_base_auth extends see_app_plugin {

    public function info( $user_id )
    {
        return array(
                'username' => 'mick',
                'password' => md5('mick'),
                'group' => 'member'
            );
    }

}
