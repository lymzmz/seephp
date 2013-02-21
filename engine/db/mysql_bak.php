<?php
/**
 * mysql cache
 *
 * Example:
 * $db->find( $sql ); //normal
 * $db->noCache()->find( $sql ); //not cache the find result
 * $db->skipCache()->find( $sql ); //skip cache to get the result, realtime to find result
 * $db->closeCache()->find( $sql ); //completely closed cache, and it not to touch also
 * $db->openCache()->find $sql ); //open the cached
 *
 * @author ymz <lymz.86@gmail.com>
 * @since 2013-1-30 06:47:13
 * @version 1.0.0
 */

class mysql {

    private $_config = null;
    static private $_handles = array();
    private $_sql_cache = array();
    private $_cache_dir = './cache';
    const TABLE_MODIFY_TIME_FILE = 'mysql.table.modify.time.php';
    private $_cache_store = true;
    private $_cache_fetch = true;

    public function __construct( $config )
    {
        $this->_config = $config;
        $this->_config->cache = isset($this->_config->cache) ? $this->_config->cache : true;
        $this->_cache_dir = empty($this->_config->cache_dir) ? realpath($this->_cache_dir) : $this->_config->cache_dir;
    }

    public function __destruct()
    {
        if ( !is_array(self::$_handles) ) return;
        foreach ( self::$_handles as $val )
            is_object($val) && mysql_close($val);
    }

    private function _connect( $k, $c, $long=false )
    {
        if ( $long )
            self::$_handles[$k] = mysql_pconnect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        else
            self::$_handles[$k] = mysql_connect($c['host'].':'.$c['port'], $c['username'], $c['password']);
        if ( !is_resource(self::$_handles[$k]) )
            trigger_error('error to connect database server.', E_USER_ERROR);
        if ( mysql_select_db($c['name'], self::$_handles[$k]) === false )
            trigger_error('error to select database.', E_USER_ERROR);
        if ( mysql_query('SET NAMES utf8', self::$_handles[$k]) === false )
            trigger_error('error to query.', E_USER_ERROR);
    }

    private function _query( $sql )
    {
        if ( ($query_sign = self::_match_query_sign( $sql )) && strtoupper($query_sign) == 'SELECT' ) {
            $c = $this->_config->slave[0];
            $k = md5(sprintf('mysql://%s@%s:%s_%d/%s', $c['username'], $c['password'], $c['host'], $c['port'], $c['name']));
            if ( !is_resource(self::$_handles[$k]) ) $this->_connect( $k, $c );
        } else {
            $c = $this->_config->master;
            $k = md5(sprintf('mysql://%s@%s:%s_%d/%s', $c['username'], $c['password'], $c['host'], $c['port'], $c['name']));
            if ( !is_resource(self::$_handles[$k]) ) $this->_connect( $k, $c );
        }

        if ( false === ( $resource = mysql_query($sql, self::$_handles[$k]) ) ) {
            trigger_error('error to query.', E_USER_WARNING);

            return false;
        }

        return $resource;
    }

    private function _find( $sql )
    {
        if ( empty($sql) ) trigger_error('the query language can not be empty.', E_USER_WARNING);
        if ( $this->fetchCache( $sql, $record ) ) {
            DEBUG && DEBUG('db cache: ', $sql);

            return $record;
        }

        if ( false === ( $resource = $this->_query( $sql ) ) ) return null;

        $row = mysql_fetch_assoc($resource);
        if ( empty($row) ) return null;
        foreach ( $row as $key => $val ) {
            if ( strtolower(substr($key, -3)) == '_id' ) {
                $ident = $key;
                break;
            }
        }
        do {
            $k = isset($ident) ? $row[$ident] : ( isset($k) ? $k + 1 : 0 );
            $record[$k] = $row;
        } while ( $row = mysql_fetch_assoc($resource) );

        $this->storeCache( $sql, $record );
        DEBUG && DEBUG('db realtime: ', $sql);

        return $record;
    }

    public function find( $sql )
    {
        return $this->findList( $sql );
    }

    public function noCache()
    {
        $this->_cache_store = false;

        return $this;
    }

    public function skipCache()
    {
        $this->_cache_fetch = false;

        return $this;
    }

    public function openCache()
    {
        $this->_config->cache = true;

        return $this;
    }

    public function closeCache()
    {
        $this->_config->cache = false;

        return $this;
    }

    public function exec( $sql )
    {
        if ( $this->_query( $sql ) === false ) return false;

        $this->touchCache( $sql );

        return true;
    }

    public function findList( $sql )
    {
        return $this->_find( $sql );
    }

    public function findRow( $sql, $row=1 )
    {
        $sql .= ' limit ' . ($row - 1) . ',1';
        $record = $this->findList( $sql );
        if ( empty($record) ) return null;

        return array_shift($record);
    }

    public function findResult( $sql )
    {
        $record = $this->findRow( $sql );
        if ( empty($record) ) return null;

        return array_shift($record);
    }

    public function fetchCache( $sql, &$record=null )
    {
        /*if ( isset($this->_sql_cache[md5($sql)]) ) {
            $record = $this->_sql_cache[md5($sql)];

            return true;
        }*///memory cache disabled
        if ( $this->_config->cache === false ) return false;/*close cache*/
        if ( $this->_cache_fetch === false && $this->_cache_fetch = true ) return false;/*fetching not from cache*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) != 'SELECT' ) return false;/*not select, not cache*/

        $table_modify_time = file_exists($this->_cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE) ? include $this->_cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE : array();
        $sql_cache_file = $this->_cache_dir.'/'.md5($sql).'.php';
        $tables = self::_match_table_name( $sql, 'select' );
        $time = 0;
        foreach ( $tables as $val ) {
            $time = empty($table_modify_time[$val]) || $table_modify_time[$val] < $time ? $time : $table_modify_time[$val];
        }
        $file_modify_time = file_exists($sql_cache_file) ? stat($sql_cache_file) : null;
        if ( !empty($file_modify_time) && $file_modify_time['mtime'] >= $time ) {
            $record = include $sql_cache_file;
            /*$this->_sql_cache[md5($sql)] = $record;*///memory cache disabled

            return true;
        } else

            return false;
    }

    public function storeCache( $sql, $record )
    {
        if ( $this->_config->cache === false ) return false;/*close cache*/
        if ( $this->_cache_store === false && $this->_cache_store = true ) return false;/*cache not to stored*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) != 'SELECT' ) return false;/*not select, not cache*/

        $sql_cache_file = $this->_cache_dir.'/'.md5($sql).'.php';
        $string = "<?php\nreturn ".var_export($record, true).";\n?>";
        file_put_contents($sql_cache_file, $string);

        return true;
    }

    public function touchCache( $sql )
    {
        if ( $this->_config->cache === false ) return false;/*close cache*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) == 'SELECT' ) return false;/*select not need to touch*/

        $tables = self::_match_table_name( $sql, $query_sign );
        $table_modify_time = file_exists($this->_cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE) ? include $this->_cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE : array();
        foreach ( $tables as $val ) {
            $table_modify_time[$val] = time();
        }
        $string = "<?php\nreturn ".var_export($table_modify_time, true).";\n?>";

        $retry_nums = 1;
        do {
            if ( file_put_contents($this->_cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE, $string, LOCK_EX) ) return true;
            usleep(1000);
        } while ( $retry_nums ++ > 3 );

        return false;
/*
        $handle = fopen('mysql.table.modifyTime.php', 'w');
        if ( flock($handle, LOCK_EX) ) {
            fwrite($handle, $string);
            flock($handle, LOCK_UN);
            fclose($handle);

            return true;
        } else {
            fclose($handle);
            if ( $retry_nums ++ > 4 ) return false;

            return self::touchCache( $sql );
        }
*/
    }

    static private function _match_table_name( $sql, $query_sign )
    {
        $pattern = array(
                'select' => array('/\bFROM\b\s+([]0-9a-z_:"`.@[-]*)/is', '/\bJOIN\b\s+([]0-9a-z_:"`.@[-]*)/is'),
                'insert' => array('/INSERT\s+INTO\s+([]0-9a-z_:"`.@[-]*)\s*/is'),
                'update' => array('/UPDATE\s+([]0-9a-z_:"`.@[-]*)\s+/is'),
                'delete' => array('/DELETE\s+FROM\s+([]0-9a-z_:"`.@[-]*)/is')
            );
        $table_name = array();
        foreach ( $pattern[$query_sign] as $val ) {
            if ( preg_match_all($val, $sql, $matchs) ) {
                $table_name = array_merge($table_name, $matchs[1]);
            }
        }
        foreach ( $table_name as $key => $val ) {
            $table_name[$key] = strtoupper(trim(str_replace(array('`','"',"'"), array('','',''), $val)));
        }

        return $table_name;
    }

    static private function _match_query_sign( $sql )
    {
        $pattern = '/^\s*(SELECT|INSERT|UPDATE|DELETE)\s+/is';
        if ( preg_match($pattern, $sql, $matchs) ) return $matchs[1];

        return null;
    }

}
