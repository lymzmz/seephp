<?php

class see_kv_kvdb extends see_kv_abstract implements see_kv_interface {

    private $kv = null;

    public function __construct( $config_arr=array() )
    {
        parent::__construct($config_arr);
        $this->kv = new SaeKV();
        $this->kv->init();
    }

    public function fetch( $key, &$value )
    {
        $value = $this->kv->get($key);
    }

    public function store( $key, $value )
    {
        return $this->kv->set($key, $value);
    }

    public function delete( $key )
    {
        return $this->kv->delete($key);
    }

}
