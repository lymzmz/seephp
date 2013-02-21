<?php

class see_db_collection extends see_db_abstract {

    private $_model = null;
    private $_builder = array();
    private $_records = array();//只在listall时保存值
    private $_unitWork = array();
    private $_records_object = array();//所有转换过的值都会保存在这里

    public function __construct( $model_obj, $records_arr=array() )
    {
        $this->_model = $model_obj;
        $this->_records = $records_arr;
    }

    public function __destruct()
    {
        $this->performOperations();
    }

    public function __call( $method, $params )
    {
        if ( strtoupper(substr($method, 0, 6)) == 'DUMPBY' ) {
            $column = substr($method, 6);
            $result = $this->setBuilder( $params[1], array($column=>$params[0]) )->listOne();
        } else if ( strtoupper(substr($method, 0, 6)) == 'LISTBY' ) {
            $column = substr($method, 6);
            $result = $this->setBuilder( $params[1], array($column=>$params[0]) )->listAll();
        } else {
            trigger_error('unknow method name: '.$method, E_USER_ERROR);

            exit(E_ERROR);
        }

        return $result;
    }

    public function record( $record )
    {
        if ( !is_array($record) ) return false;

        $key = $this->_global_key( $record );
        if ( !isset($this->_records_object[$key]) ) {
            $this->_records_object[$key] = see_engine_database::record( $this->_model, $record );
        }

        return $this->_records_object[$key];
    }

    public function performOperations( $type=null )
    {
        $unitWork = $this->_unitWork;
        if ( (is_null($type) || $type == 'add') && is_array($unitWork['add']) && count($unitWork['add']) ) {
            $this->_model->insert( $unitWork['add'], true );
            $this->_unitWork['add'] = null;
        }

        if ( (is_null($type) || $type == 'del') && is_array($unitWork['del']) && count($unitWork['del']) ) {
            $unitWork['del']['is_resolver'] = true;
            $this->_model->delete( $unitWork['del'], true );
            $this->_unitWork['del'] = null;
        }
    }

    public function setBuilder( $columns='*', $filter=null, $order='', $group='', $page=1, $limit=100 )
    {
        $columns = see_engine_database::until()->resolverColumns( $columns, $this->_model->getTableName() );
        $filter = see_engine_database::until()->resolverFilter( $filter, $this->_model->getTableName() );
        $tables = $columns['tables'] + $filter['tables'];
        $tables = see_engine_database::until()->resolverTables( $tables );
        $builder =  array(
                'select' => $columns['select'],
                'update' => $columns['update'],
                'tables' => $tables,
                'filter' => $filter['filter'],
                'page' => (int)$page,
                'limit' => (int)$limit,
                'order' =>  see_engine_database::until()->resolverOrder( $order, $this->_model->getTableName() ),
                'group' =>  see_engine_database::until()->resolverGroup( $group, $this->_model->getTableName() )
            );
        $this->_builder = $builder;

        return $this;
    }

    public function getBuilder()
    {
        return empty($this->_builder) ? false : (object)$this->_builder;
    }

    public function callback( $auto_page=false, $function, $type=true )
    {
        $return = array();
        while ( ($data  = $this->listing()) ) {
            $data = $type ? $data : array($data);
            $return = array_merge( $return, array_map( $function, $data ) );
            if ( $auto_page === true )
                $this->_builder['page'] ++;
            else
                break;
        }

        return $return;
    }

    public function listAll( $toRecord=false )
    {
        $b = $this->_builder;
        $offset = ( $b['page'] - 1 ) * $b['limit'];
        $this->_records = $this->_model->findList( $b['select'], $b['filter'], $b['tables'], $b['order'], $b['group'], $offset, $b['limit'] );

        return $this->_records;
    }

    public function listOne( $toRecord=false )
    {
        $b = $this->_builder;
        $result = $this->_model->findOne( $b['select'], $b['filter'], $b['tables'], $b['order'], $b['group'] );

        return $result;
    }

    public function add( $record, $realtime=false )
    {
        if ( $realtime === true ) {
            $result = $this->_model->insert( $record, false );

            return $result;
        } else
            $this->_unitWork['add'][] = $record;
    }

    /**
     * @param mixed $sets string(key1=val1,key2=val2)|array(array(key1=>val1,key2=>val2))|object(db_record)
     * @param mixed $filter string(key1=val1,key2>val2)|array(array(key1|bthan=>val1))|object(db_record)
     */
    public function up( $sets=null, $filter=null )
    {
        if ( $sets ) {
            if ( is_object($sets) ) {
                $filter = $filter ? $filter : $sets->toFilter();
                $sets = $sets->toArray();
            }
            $sets = see_engine_database::until()->resolverColumns( $sets, $this->_model->getTableName() );
            $columns = $sets['update'];
            $filter = see_engine_database::until()->resolverFilter( $filter, $this->_model->getTableName() );
            $filter = $filter['filter'];
        } else {
            $columns = $this->_builder['update'];
            $filter = $this->_builder['filter'];
        }
        $filter['is_resolver'] = $columns['is_resolver'] = true;
        $result = $this->_model->update( $columns, $filter );

        return $result;
    }

    public function del( $filter=null, $realtime=false )
    {
        if ( $realtime === true ) {
            $filter = $filter ? see_engine_database::until()->resolverFilter( $filter, $this->_model->getTableName() ) : ( $this->_builder['filter'] ? $this->_builder['filter'] : 1 );
            $filter['is_resolver'] = true;
            $result = $this->_model->delete( $filter, false );

            return $result;
        } else {
            if ( $filter ) {
                $filter = see_engine_database::until()->resolverFilter( $filter, $this->_model->getTableName() );
                $filter = $filter['filter'];
            }
            $this->_unitWork['del'][] = $filter ? $filter : ( $this->_builder['filter'] ? $this->_builder['filter'] : 1 );
        }
    }

    private function _global_key( $mixed )
    {
        return md5(serialize($mixed));
    }

}
