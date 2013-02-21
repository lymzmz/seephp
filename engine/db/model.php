<?php

class see_db_model extends see_db_abstract {

    private $_table_name = '';
    private $_dbServer = null;

    final public function __construct( $params=null, $table_name='' )
    {
        if ( empty($this->_table_name) ) {
            if ( !$table_name ) $table_name = __CLASS__;
            $this->_table_name = substr($table_name, 4);
        }
        $this->_dbServer = see_engine_kernel::single()->database();
    }

    public function getTableName( $split=false )
    {
        if ( $split === true ) {
            $pos = strpos($this->_table_name, '_');
            $app = substr($this->_table_name, 0, $pos);
            $table_name = substr($this->_table_name, $pos + 1);

            return array($app, $table_name);
        }

        return $this->_table_name;
    }

    public function getSchema()
    {
        list($app, $table_name) = $this->getTableName( true );
        $schema = see_engine_kernel::singleApp( $app )->dbSchema( $table_name );

        return $schema;
    }

    public function findList( $columns='*', $filter=null, $from=null, $order='', $group='', $offset=0, $limit=100 )
    {//todo 如何防止两次做resolver collection一次 model二次
        //$columns = database::until()->resolverColumns( $columns );
        $columns = implode(',', $columns);
        $sql = 'select ' . $columns;
        //$tables = database::until()->resolverTables( $from );
        $sql .= ' from ' . $from;
        $filter = implode(' and ', $filter);
        $sql .= ' where '.$filter;//database::until()->resolverFilter( $filter );
        $sql .= $group ? ' group by '.$group : '';
        $sql .= $order ? ' order by '.$order : '';
        $sql .= ' limit '.$offset.','.$limit;

        return $this->_dbServer->select( $sql );
    }

    public function findOne( $columns='*', $filter=null, $from=null, $order='', $group='' )
    {
        $sql .= ' limit 1,1';
        $record = $this->findList( $columns, $filter, $from, $order, $group, 0, 1 );
        if ( empty($record) ) return false;

        return array_shift($record);
    }

    public function findResult(  $columns='*', $filter=null, $from=null, $order='', $group='' )
    {
        $record = $this->findOne( $columns, $filter, $from, $order, $group );
        if ( empty($record) ) return false;

        return array_shift($record);
    }

    /**
     * @param mixed $filter string|array|object(db_record)
     */
    public function delete( $filter=null, $many=false )
    {
        $many === false && ($filter = array($filter));
        if ( !is_array($filter) || !isset($filter['is_resolver']) || !$filter['is_resolver'] ) {
            foreach ( $filter as $val ) {
                $fil = see_engine_database::until()->resolverFilter( $val );
                $fil = implode(' and ', $fil['filter']);
                $where[] = $fil;
            }
        } else {
            unset($filter['is_resolver']);
            foreach ( $filter as $val ) $where[] = implode(' and ', $val);
        }
        $sql = 'delete from '.$this->getTableName().' where (' . implode(' or ', $where) . ')';
        $result = $this->_dbServer->exec( $sql );

        return $result;
    }

    /**
     * @param mixed $data array|object(db_record)
     */
    public function insert( $data, $many=false )
    {
        if ( (!is_array($data) || !count($data)) && !is_object($data) ) return false;
        $many === false && ($data = array($data));

        $sql = 'insert into '.$this->getTableName();
        foreach ( $this->getSchema()->columns as $key => $val ) {
            if ( $val['extra'] == 'auto_increment' ) continue;
            $columns[$key] = $val;
        }
        $sql .= '(' . implode(',', array_keys($columns)) . ') values';
        reset($data);
        while ( ($row = each($data)) ) {
            if ( is_object($row['value']) ) $row['value'] = $row['value']->toArray();
            $columns_value = array();
            foreach ( $columns as $key => $val ) {
                if ( isset($row['value'][$key]) ) {
                    //todo 类型检查
                } else {
                    $row['value'][$key] = isset($val['default']) ? $val['default'] : '';
                }
                $columns_value[] = see_engine_database::quote( $row['value'][$key] );
            }
            $sql .= '(' . implode(',', $columns_value) . '),';
        }
        $sql = substr($sql, 0, -1);
        if ( false !== ($result = $this->_dbServer->exec( $sql )) ) $result = $this->_dbServer->insertId();

        return $result;
    }

    public function update( $columns, $filter=null )
    {
        $sql = 'update '.$this->getTableName().' set ';
        if ( !is_array($columns) || !isset($columns['is_resolver']) || !$columns['is_resolver'] ) {
            $columns = see_engine_database::until()->resolverColumns( $columns, $this->getTableName() );
            if ( !($columns = $columns['update']) || empty($columns) ) return false;
        } else
            unset($columns['is_resolver']);

        foreach ( $columns as $key => $val ) {
            $columns_value[] = $key . '=' . see_engine_database::quote( $val );
        }
        $sql .= implode(',', $columns_value);
        if ( !is_array($filter) || !isset($filter['is_resolver']) || !$filter['is_resolver'] ) {
            $filter = see_engine_database::until()->resolverFilter( $filter, $this->getTableName() );
            $filter = $filter['filter'];
        }
        $sql .= ' where ' . implode(' and ', $filter);
        $result = $this->_dbServer->exec( $sql );

        return $result;
    }

}
