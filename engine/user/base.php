<?php

class see_user_base {

    private $_user_id = 'guest';
    private $_info = array();

    public function __construct( $user_id_str, $info_arr=array() )
    {
        $this->_user_id = $user_id_str;
        $this->_info = $info_arr;
    }

    /**
     * 等级验证
     *
     * @param array $levels_arr 验证等级范围
     *
     * @return bool
     */
    public function verifyLevel( $levels_arr )
    {
        return true;
    }

    /**
     * 用户组验证
     *
     * @param array $groupIds_arr 验证用户组范围
     *
     * @return bool
     */
    public function verifyGroup( $groupIds_arr )
    {
        return in_array($this->_info['group'], $groupIds_arr) ? true : false;
    }

    /**
     * 有效性验证
     *
     * @param string $token 据此token做验证
     *
     * @return bool
     */
    public function verifyAccount( $token )
    {
        if ( empty($token) ) return false;
        $token = explode('.', $token);
        if ( md5($this->_user_id) != $token[0] ) return false;
        if ( md5($this->_info['group']) != $token[1] ) return false;
        if ( md5($this->_info['password']) != $token[2] ) return false;

        return true;
    }

    public function __get( $key )
    {
        if ( !isset($this->_info[$key]) ) {
            $this->_info = see_engine_user::info( $this->_user_id );
        }
        return isset($this->_info[$key]) ? $this->_info[$key] : null;
    }

}
