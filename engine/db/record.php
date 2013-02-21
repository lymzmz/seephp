<?php

class see_db_record {

    private $_model = null;
    private $_info = array();

    public function __construct( $model_obj, $record_arr )
    {
        $this->_model = $model_obj;
        $this->_info = $record_arr;
    }

    public function __set( $key, $value )
    {
        $this->_info[$key] = $value;
    }

    public function __get( $key )
    {
        return isset($this->_info[$key]) ? $this->_info[$key] : null;
    }

    public function __toString()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return $this->_info;
    }

    public function toFilter( $filter_columns_mixed=null )
    {
        if ( $filter_columns_mixed && is_string($filter_columns_mixed) )
            $filter_columns_mixed = explode(',', $filter_columns_mixed);
        if ( empty($filter_columns_mixed) && ($pKey = $this->_model->getSchema()->primaryKey) && !empty($pKey) ) {
            $filter_columns_mixed = $pKey;
        }
        if ( $filter_columns_mixed )
            foreach ( $filter_columns_mixed as $val ) $filter[$val] = $this->_info[$val];
        else
            $filter = $this->_info;

        return $filter;
    }

}
