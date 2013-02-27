<?php

interface see_queue_interface {

    public function push( $key, $value );

    public function shift( $key );

    public function count( $key );

    public function reset( $key );

}
