<?php

class see_plg_base_auth extends see_app_plugin {

    public function info( $user_id )
    {
        $filter  = array(
            'username' => $user_id,
        );

        $result = see_engine_kernel::model('members')->findOne('*', $filter);
        if ( empty($result) ) {
            $this->error('无此用户');
        }

        return $result;
    }

}
