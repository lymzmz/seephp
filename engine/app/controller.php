<?php

class see_app_controller extends see_app_abstract {

    protected $_is_cache = true;
    protected $pagedata = array();

    public function enableCache()
    {
        return isset($this->_is_cache) && $this->_is_cache === false ? false : true;
    }

    public function display( $file_name )
    {
        $render = see_engine_kernel::single()->view();
        $render->setApp( $this->_app );
        $render->setAssign( $this->pagedata );
        $render->display( $file_name );
    }

}
