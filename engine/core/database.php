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
        if ( false === ($pos = strpos( $table_name, '_' )) ) return false;

        $app = substr( $table_name, 0, $pos );
        $table_name = substr( $table_name, $pos + 1 );
        $file_name = ROOT_DIR . '/application/' . $app . '/dbschema/' . $table_name . '.php';
        $schema = include $file_name;

        return is_array($schema) ? (object)$schema : false;
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

    static public function until()
    {
        $class_name = 'see_db_until';
        $until = new $class_name;

        return is_object($until) ? $until : false;
    }

}
