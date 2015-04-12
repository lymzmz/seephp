<?php

class see_bsn_base_members extends see_app_business {

    public function register( $data )
    {
        if ( empty($data['username']) || empty($data['password']) ) {
            $this->error('用户名或密码不能为空');

            return;
        }

        $_data = array(
            'username' => $data['username'],
            'password' => md5($data['password']),
            'group' => 'member',
            'create_time' => time(),
        );
        $result = see_engine_kernel::model('members')->insert($_data);

        return $result;
    }

}
