<?php

class see_db_mysqlBase {

    protected $_config = null;
    protected $_handle = null;

    public function __construct( $config )
    {
        $this->_config = $config;
    }

    public function __destruct()
    {
        is_object($$this->_handle) && mysql_close($this->_handle);
    }

    protected function _connect( $c, $long=false )
    {
        if ( $long )
            $this->_handle = mysql_pconnect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        else
            $this->_handle = mysql_connect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        if ( !is_resource($this->_handle) )
            throw new Exception('error to connect database server ['.$c['host'].'].');
        if ( mysql_select_db($c['name'], $this->_handle) === false )
            throw new Exception('error to select database ['.$c['name'].'].');
        if ( mysql_query('SET NAMES utf8', $this->_handle) === false )
            throw new Exception('error to query.');
    }

    protected function _query( $sql )
    {
        try {
            !is_resource($this->_handle) && $this->_connect( $this->_config, false );
        } catch ( Exception $e ) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        if ( false === ( $resource = mysql_query($sql, $this->_handle) ) ) {
            trigger_error(mysql_error($this->_handle).$sql, DEBUG ? E_USER_ERROR : E_USER_WARNING);

            return false;
        }

        return $resource;
    }

    protected function _find( $sql )
    {
        if ( false === ( $resource = $this->_query( $sql ) ) ) return null;

        while ( $row = mysql_fetch_assoc($resource) ) {
            $record[] = $row;
        }

        return $record;
    }

    public function exec( $sql )
    {
        return $this->_query( $sql ) === false ? false : true;
    }

    public function select( $sql )
    {
        return $this->_find( $sql );
    }

    public function stat( $handle=null )
    {
        return mysql_stat($handle ? $handle : $this->_handle);
    }

    public function insertId()
    {
        return mysql_insert_id( $this->_handle );
    }

}
