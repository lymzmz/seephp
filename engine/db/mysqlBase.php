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
        is_object($$this->_handle) && mysqli_close($this->_handle);
    }

    protected function _connect( $c, $long=false )
    {
        if ( $long )
            $this->_handle = mysqli_pconnect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        else
            $this->_handle = mysqli_connect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        if ( !is_object($this->_handle) )
            throw new Exception('error to connect database server ['.$c['host'].'].'.mysqli_connect_error());
        if ( mysqli_select_db($this->_handle, $c['name']) === false )
            throw new Exception('error to select database ['.$c['name'].'].');
        if ( mysqli_query($this->_handle, 'SET NAMES utf8') === false )
            throw new Exception('error to query.');
    }

    protected function _query( $sql )
    {
        try {
            !is_object($this->_handle) && $this->_connect( $this->_config, false );
        } catch ( Exception $e ) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        if ( false === ( $resource = mysqli_query($this->_handle, $sql) ) ) {
            trigger_error(mysqli_error($this->_handle).$sql, DEBUG ? E_USER_ERROR : E_USER_WARNING);

            return false;
        }

        return $resource;
    }

    protected function _find( $sql )
    {
        if ( false === ( $resource = $this->_query( $sql ) ) ) return null;

        while ( $row = mysqli_fetch_assoc($resource) ) {
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
        return mysqli_stat($handle ? $handle : $this->_handle);
    }

    public function insertId()
    {
        return mysqli_insert_id( $this->_handle );
    }

}
