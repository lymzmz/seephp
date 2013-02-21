<?php

interface see_kv_interface {

    public function fetch( $key, &$value );

    public function store( $key, $value );

    public function delete( $key );

}
