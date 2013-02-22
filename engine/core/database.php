<?php

class see_engine_database {

    static public function db( $config=null )
    {
        if ( $config ) {
            $class_name = 'see_db_' . $config['engine'];
            $db = new $class_name( $config );
        } else {
            $engine = see_engine_config::load( 'application' )->dbServer;
            $class_name = 'see_db_' . $engine;
            $db = new $class_name( see_engine_config::load( 'database' ) );
        }

        return is_object($db) ? $db : false;
    }

    static public function collection( $table_name )
    {
        if ( false === ($pos = strpos( $table_name, '_' )) ) return false;

        $class_name = 'see_db_collection';
        $app = substr($table_name, 0, $pos);
        $table_name = substr($table_name, $pos + 1);
        $collection = new $class_name( see_engine_kernel::app( $app )->model( $table_name ) );

        return is_object($collection) ? $collection : false;
    }

    static public function schema( $table_name )
    {
        static $schema = array();
        if ( false === ($pos = strpos( $table_name, '_' )) ) return false;

        if ( !is_object($schema[$table_name]) ) {
            $app = substr( $table_name, 0, $pos );
            $table_name = substr( $table_name, $pos + 1 );
            $file_name = ROOT_DIR . '/application/' . $app . '/dbschema/' . $table_name . '.php';
            $s = include $file_name;
            $schema[$table_name] = (object)$s;
        }

        return $schema[$table_name];
    }

    static public function record( $model_obj, $record_arr )
    {
        if ( !is_object($model_obj) ) return false;
        if ( !is_array($record_arr) ) return false;

        $class_name = 'see_db_record';
        $record = new $class_name( $model_obj, $record_arr );

        return is_object($record) ? $record : false;
    }

    static public function quote( $string )
    {
        return self::db()->quote( $string );
    }

    static public function builder( $model_obj )
    {
        if ( !is_object($model_obj) ) return false;

        $class_name = 'see_db_builder';
        $until = new $class_name( $model_obj );

        return is_object($until) ? $until : false;
    }

}
