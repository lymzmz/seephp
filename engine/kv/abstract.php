<?php

abstract class see_kv_abstract {

    final public function __construct( $config_arr=array() )
    {
        $this->init( $config_arr );
    }

}
