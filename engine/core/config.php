<?php

class see_engine_config {

    static private $_data = array();

    static public function load( $type )
    {
        if ( empty($type) ) return null;

        if ( !isset(self::$_data[$type]) ) {
            self::$_data[$type] = require ROOT_DIR.'/config/'.$type.'Config.php';
            self::$_data[$type] = (object)self::$_data[$type];
            DEBUG && DEBUG($type, ' config: ', self::$_data[$type]);
        }

        return self::$_data[$type];
    }

}
