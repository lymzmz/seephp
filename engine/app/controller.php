<?php

class see_app_controller extends see_app_abstract {

    protected $_cache = true;
    protected $pagedata = array();

    public function enableCache()
    {
        return isset($this->_cache) && $this->_cache === false ? false : true;
    }

    public function authGroup()
    {
        return see_engine_user::group();
    }

    public function display( $file_name )
    {
        $render = see_engine_kernel::single()->view();
        $render->setApp( $this->_app );
        $render->setAssign( $this->pagedata );
        $render->display( $file_name );
    }

    public function error( $msg )
    {
        throw new Exception( $msg );
    }

    public function redirect( $url )
    {
        header( 'Location:' . $url );
        DEBUG && DEBUG('<script>location.href="'.$url.'";</script>');

        exit();
    }

}
