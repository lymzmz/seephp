<?php

class see_queue_redis implements see_queue_interface {

    private $_server = null;

    public function __construct( $config_arr )
    {
        $this->_server = new Redis();
        $this->_server->connect( $config_arr['host'], $config_arr['port'] );
        $this->_server->setOption( Redis::OPT_SERIALIZER, REDIS::SERIALIZER_PHP );
    }

    public function push( $key, $value )
    {
        $result = $this->_server->rPush( $key, $value );

        return empty($result) ? false : true;
    }

    public function shift( $key )
    {
        $value = $this->_server->lPop( $key );

        return $value;
    }

    public function count( $key )
    {
        $result = $this->_server->llen( $key );

        return (int)$result;
    }

    public function reset( $key )
    {
        $result = $this->_server->delete( $key );

        return $result == true ? true : false;
    }

}
