<?php

abstract class see_kv_abstract {

    protected $_config;

    public function __construct( $config_arr=array() )
    {
        $this->_config = $config_arr;
    }

}
