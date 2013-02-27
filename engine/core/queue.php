<?php

class see_engine_queue {

    private $_server = null;

    public function __construct( $config_arr )
    {
        $class_name = 'see_queue_' . $config_arr['engine'];
        $this->_server = new $class_name( $config_arr );
    }

    public function push( $key , $value )
    {
        return $this->_server->push( $key, $value );
    }

    public function shift( $key )
    {
        return $this->_server->shift( $key );
    }

    public function count( $key )
    {
        return $this->_server->count( $key );
    }

    public function reset( $key )
    {
        return $this->_server->reset( $key );
    }

}
