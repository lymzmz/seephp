<?php

class see_db_model extends see_db_abstract {

    private $_table_name = '';
    private $_dbServer = null;
    private $_builder = null;

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

    public function setBuilder( $builder_obj )
    {
        $this->_builder = $builder_obj;

        return $this;
    }

    public function findList( $columns='*', $filter=null, $from=null, $order='', $group='', $offset=0, $limit=-1 )
    {
        if ( is_object($this->_builder) ) {
            $columns = implode(',', $this->_builder->select);
            $from = implode(',', $this->_builder->tables);
            $filter = implode(' and ', $this->_builder->filter);
            $group = $this->_builder->group;
            $order = $this->_builder->order;
            $limit = $this->_builder->limit;
            $offset = ($this->_builder->page - 1) * $this->_builder->limit;
            $this->_builder = null;
        }
        $sql = 'select ' . $columns;
        $sql .= ' from ' . $from;
        $sql .= ' where '. ( $filter ? $filter : '1' );
        $sql .= $group ? ' group by '.$group : '';
        $sql .= $order ? ' order by '.$order : '';
        $sql .= $limit > 0 ? ' limit '.$offset.','.$limit : '';

        return $this->_dbServer->select( $sql );
    }

    public function findOne( $columns='*', $filter=null, $from=null, $order='', $group='' )
    {
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
        if ( $many ) {
            $builder = see_engine_database::builder( $this );
            foreach ( $filter as $val ) {
                $fil = $builder->resolver( null, $val );
                $where[] = implode(' and ', $fil->filter);
            }
        } else {
            $where[] = see_engine_database::builder( $this )->resolver( null, $filter );
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
        $builder = see_engine_database::builder( $this )->resolver( $columns, $filter );
        if ( !($columns = $builder->update) || empty($columns) ) return false;

        foreach ( $builder->update as $key => $val ) {
            $columns_value[] = $key . '=' . see_engine_database::quote( $val );
        }
        $sql .= implode(',', $columns_value);
        $sql .= ' where ' . implode(' and ', $builder->filter);
        $result = $this->_dbServer->exec( $sql );

        return $result;
    }

}
