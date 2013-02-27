<?php

class see_engine_user {

    static private $_instance = array();

    static public function instance( $user_id, $group_id='' )
    {
        if ( !isset(self::$_instance[$user_id]) ) {
            if ( empty($group_id) ) {
                $info = self::info( $user_id );
                $group_id = $info['group'];
            } else
                $info['group'] = $group_id;
            $class_name = 'see_user_'.$group_id;
            if ( !class_exists($class_name) ) $class_name = 'see_user_base';

            self::$_instance[$user_id] = new $class_name( $user_id, $info );
        }

        return self::$_instance[$user_id];
    }

    static public function login( $user_id, $password )
    {
        $info = self::info( $user_id );
        if ( $info['username'] == $user_id && $info['password'] == md5($password) ) {
            $cookie = md5($info['username']).'.'.md5($info['group']).'.'.md5($info['password']);
            $expire = time() + ( 60 * 60 * 24 * 7 );
            setCookie('T', $cookie, $expire, '/', '', false, true);
            setCookie('U', $info['username'], time() + ( 60 * 60 * 24 * 30 ), '/', '', false, false);

            return true;
        } else

            return false;
    }

    static public function logout( $force=false )
    {
        setCookie('T', null, 0, '/', '', false, true);

        return true;
    }

    static public function info( $user_id )
    {
        if ( empty($user_id) )
            $info = array(
                'username' => 'guest', 'password' => '', 'group' => 'guest'
            );
        else
            $info = see_engine_kernel::singleApp( see_engine_request::app() )->plugin( 'auth' )->info( $user_id );

        return $info;
    }

    static public function level()
    {
        return array(
                'guest' => array(),
                'member' => array('normal', 'vip'),
                'admin' => array('normal', 'super')
            );
    }

    static public function group()
    {
        return array(
                'guest' => 'guest', 'member' => 'member', 'admin' => 'admin'
            );
    }

}
