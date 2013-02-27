<?php

abstract class see_app_abstract {

    protected $_app = '';

    public function __construct()
    {
        $class = see_engine_system::parseClassName( get_class($this) );
        $this->_app = $class['app'];
    }

}
