<?php

class see_engine_kvstore {

    static public function instance( $config=null )
    {
        if ( $config ) {
            $class_name = 'see_kv_' . $config['engine'];
            $kv = new $class_name( $config );
        } else {
            $engine = see_engine_config::load( 'application' )->kvServer;
            $class_name = 'see_kv_' . $engine;
            $kv = new $class_name( see_engine_config::load( 'kvstore' ) );
        }

        return $kv;
    }

}
