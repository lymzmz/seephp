<?php

abstract class see_app_abstract {

    protected $_app = '';

    final public function __construct()
    {
        $class = see_engine_system::parseClassName( get_class($this) );
        $this->_app = $class['app'];
    }

}
