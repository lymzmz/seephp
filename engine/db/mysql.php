<?php
/**
 * mysql cache
 *
 * Example:
 * $db = new mysqlProxy( config::load( 'database' ), new mysql );//get instance
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

class see_db_mysql extends see_db_mysqlBase {

    private $_deploy = null;
    static private $_handles = array();
    private $_sql_cache = array();
    const TABLE_MODIFY_TIME_FILE = 'mysql.table.modify.time.php';
    private $_cache_store = true;
    private $_cache_fetch = true;

    public function __construct( $deploy )
    {
        $this->_deploy = $deploy;
        $this->_deploy->cache = isset($this->_deploy->cache) ? $this->_deploy->cache : true;
        $this->_deploy->cache_dir = empty($this->_deploy->cache_dir) ? ROOT_DIR.'/cache/' : $this->_deploy->cache_dir;
    }

    public function __destruct()
    {
        if ( !is_array(self::$_handles) ) return;
        foreach ( self::$_handles as $val )
            is_object($val) && mysql_close($val);
    }

    protected function _query( $sql )
    {
        if ( ($query_sign = self::_match_query_sign( $sql )) && strtoupper($query_sign) == 'SELECT' ) {
            $this->_config = $this->chooseServer( $this->_deploy->slave );
        } else {
            $this->_config = $this->_deploy->master;
        }
        $ident = self::_mk_ident( $this->_config );
        $this->_handle = is_resource(self::$_handles[$ident]) ? self::$_handles[$ident] : null;
        $resource = parent::_query( $sql );
        if ( !is_resource(self::$_handles[$ident]) ) {
            self::$_handles[$ident] = $this->_handle;
        }

        return $resource;
    }

    protected function _find( $sql )
    {
        /*if ( isset($this->_sql_cache[md5($sql)]) ) {
            DEBUG && DEBUG('db memory: ', $sql);

            return $this->_sql_cache[md5($sql)];
        }*///memory cache disabled
        if ( $this->fetchCache( $sql, $record ) ) {
            DEBUG && DEBUG('db cache: ', $sql);

            return $record;
        }
        if ( ($record = parent::_find( $sql )) ) {
            $this->storeCache( $sql, $record );
            DEBUG && DEBUG('db realtime: ', $sql);
        }

        return $record;
    }

    public function exec( $sql )
    {
        if ( parent::exec( $sql ) ) {
            $this->touchCache( $sql );

            return true;
        }

        return false;
    }

    public function quote( $string )
    {
        return '\''.mysql_escape_string($string).'\'';
    }

    static private function _mk_ident( $c )
    {
        return md5(sprintf('mysql://%s@%s:%s_%d/%s', $c['username'], $c['password'], $c['host'], $c['port'], $c['name']));
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
        $this->_deploy->cache = true;

        return $this;
    }

    public function closeCache()
    {
        $this->_deploy->cache = false;

        return $this;
    }

    public function fetchCache( $sql, &$record=null )
    {
        if ( $this->_deploy->cache === false ) return false;/*close cache*/
        if ( $this->_cache_fetch === false && $this->_cache_fetch = true ) return false;/*fetching not from cache*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) != 'SELECT' ) return false;/*not select, not cache*/

        $table_modify_time = file_exists($this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE) ? include $this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE : array();
        $sql_cache_file = $this->_deploy->cache_dir.'/'.md5($sql).'.php';
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
        if ( $this->_deploy->cache === false ) return false;/*close cache*/
        if ( $this->_cache_store === false && $this->_cache_store = true ) return false;/*cache not to stored*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) != 'SELECT' ) return false;/*not select, not cache*/

        $sql_cache_file = $this->_deploy->cache_dir.'/'.md5($sql).'.php';
        $string = "<?php\nreturn ".var_export($record, true).";\n?>";
        file_put_contents($sql_cache_file, $string);

        return true;
    }

    public function touchCache( $sql )
    {
        if ( $this->_deploy->cache === false ) return false;/*close cache*/
        if ( !($query_sign = self::_match_query_sign( $sql )) || strtoupper($query_sign) == 'SELECT' ) return false;/*select not need to touch*/

        $tables = self::_match_table_name( $sql, $query_sign );
        $table_modify_time = file_exists($this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE) ? include $this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE : array();
        foreach ( $tables as $val ) {
            $table_modify_time[$val] = time();
        }
        $string = "<?php\nreturn ".var_export($table_modify_time, true).";\n?>";

        $retry_nums = 1;
        do {
            if ( file_put_contents($this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE, $string, LOCK_EX) ) return true;
            usleep(1000);
        } while ( $retry_nums ++ > 3 );

        return false;
/*
        $handle = fopen($this->_deploy->cache_dir.'/'.self::TABLE_MODIFY_TIME_FILE, 'w');
        if ( flock($handle, LOCK_EX) ) {
            fwrite($handle, $string);
            flock($handle, LOCK_UN);
            fclose($handle);

            return true;
        } else {
            fclose($handle);
            if ( $retry_nums ++ > 4 ) return false;

            return $this->touchCache( $sql );
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

    public function chooseServer( $cluster )
    {
        if ( !is_array($cluster) ) return $cluster;
        if ( count($cluster) < 2 ) return array_shift($cluster);

        return $cluster[array_rand($cluster)];

        shuffle($cluster);
        foreach ( $cluster as $key => $val ) {
            $ident = self::_mk_ident($val);
            if ( !is_resource(self::$_handles[$ident]) ) {
                try {
                    $this->_connect( $val, false );
                    self::$_handles[$ident] = $this->_handle;
                } catch ( Exception $e ) { continue; }
            }
            $stat = $this->stat( self::$_handles[$ident] );
            list( , $avg_time) = explode(':', $stat[7]);
            if ( !isset($pre_avg_time) || $pre_avg_time > $avg_time ) {
                $pre_avg_time =  $avg_time;
                $server = $val;
            }
        }

        return $server;
    }

}
