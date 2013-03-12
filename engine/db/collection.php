<?php

class see_db_collection extends see_db_abstract {

    private $_model = null;
    private $_builder = null;
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
        $this->_builder = see_engine_database::builder( $this->_model )->resolver( $columns, $filter, $order, $group, $page, $limit );

        return $this;
    }

    public function callback( $auto_page=false, $function, $type=true )
    {
        $return = array();
        while ( ($data  = $this->listing()) ) {
            $data = $type ? $data : array($data);
            $return = array_merge( $return, array_map( $function, $data ) );
            if ( $auto_page === true )
                $this->_builder->page ++;
            else
                break;
        }

        return $return;
    }

    public function listAll( $toRecord=false )
    {
        $this->_records = $this->_model->setBuilder( $this->_builder )->findList();

        return $this->_records;
    }

    public function listOne( $toRecord=false )
    {
        $result = $this->_model->setBuilder( $this->_builder )->findOne();

        return $result;
    }

    public function add( $record, $realtime=false )
    {
        if ( $realtime === true ) {
            $insert_id = $this->_model->insert( $record, false );

            return $insert_id;
        } else
            $this->_unitWork['add'][] = $record;
    }

    /**
     * @param mixed $sets string(key1=val1,key2=val2)|array(array(key1=>val1,key2=>val2))|object(db_record)
     * @param mixed $filter string(key1=val1,key2>val2)|array(array(key1|bthan=>val1))|object(db_record)
     */
/*    public function up( $sets=null, $filter=null )
    {
        if ( $sets ) {
            $builder = see_engine_database::builder( $this->_model )->resolver( $sets, $filter );
        } else {
            $builder = $this->_builder;
        }
        $result = $this->_model->setBuilder( $builder )->update();

        return $result;
    }*/

/*    public function del( $filter=null, $realtime=false )
    {
        if ( $filter ) {
            $builder = see_engine_database::builder( $this->_model )->resolver( null, $filter );
            $filter = $builder->filter;
            unset($builder);
        } else if ( $this->_builder->filter )
            $filter = $this->_builder->filter;
        else
            $filter = 1;
        if ( $realtime === true ) {
            $result = $this->_model->delete( $filter, false );

            return $result;
        } else {
            $this->_unitWork['del'][] = $filter;
        }
    }*/

    private function _global_key( $mixed )
    {
        return md5(serialize($mixed));
    }

}
