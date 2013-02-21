<?php

class see_db_until extends see_db_abstract {

    static public function resolverColumns( $columns, $mainTableName='' )
    {
        is_object($columns) && ($columns = $columns->toArray());
        $tables[] = $mainTableName;
        if ( empty($columns) || $columns == '*' ) {

            return array('select' => array($mainTableName . '.*'), 'update' => null, 'tables' => $tables);
        } else if ( is_string($columns) ) {
            $cols = explode(',', $columns);
            $columns = array();
            foreach ( $cols  as $val ) {
                if ( strpos($val, '=') && ($val = explode('=', $val)) ) {
                    $columns[$val[0]] = $val[1];
                } else
                    $columns[$val] = '';
            }
        }
        foreach ( $columns as $key => $val ) {
            $foreignTableName = '';
            strpos($key, '.') && ($key = explode('.', $key)) && ($foreignTableName = $key[0]) && ($key = $key[1]);
            $tableName = $foreignTableName ? $foreignTableName : $mainTableName;
            $key = $tableName . '.' . $key . '';
            $select[] = $key;
            $val && ($update[$key] = $val);
            !in_array($tableName, $tables) && ($tables[] = $tableName);
        }

        return array('select' => $select, 'update' => $update, 'tables' => $tables);
    }

    static public function resolverFilter( $filter, $mainTableName='' )
    {
        if ( empty($filter) ) return 1;
        is_object($filter) && ($filter = $filter->toFilter());
        $tables[] = $mainTableName;
        if ( is_string($filter) ) {
            foreach ( explode(',', $filter) as $val ) {
                if ( strpos($val, '=') && ($val = explode('=', $val)) ) {
                    $columns[$val[0]] = $val[1];
                } else
                    $columns[$val] = '';
            }
            $filter = $columns;
        }

        foreach ( $filter as $key => $val ) {
            strpos($key, '.') && ($key = explode('.', $key)) && ($foreignTableName = $key[0]) && ($key = $key[1]);
            $tableName = $foreignTableName ? $foreignTableName : $mainTableName;
            strpos($key, '|') && ($key = explode('|', $key)) && ($sign = $key[1]) && ($key = $key[0]);
            $key = $tableName . '.' . $key . '';
            $sign = $sign ? $sign : '=';
            $where[] = is_array($val) ? $key . ' in (' . implode(',', $val) . ')' : $key . $sign . $val;
            !in_array($tableName, $tables) && ($tables[] = $tableName);//todo 多表联合删除
        }

        return array('filter' => $where ? $where : 1, 'tables' => $tables);
    }

    static public function resolverTables( $tables )
    {
        if ( !is_array($tables) || !count($tables) ) return false;

        if ( count($tables) == 1) return $tables[0];

        foreach ( $tables as $key1 => $table1 ) {
            $mainForeignKey = see_engine_database::schema( $table1 )->foreignKey;
            if ( $mainForeignKey ) {
                $pos = strpos($mainForeignKey['reference'], '.');
                $mainReferenceTable = substr($mainForeignKey['reference'], 0, $pos);
            }
            foreach ( $tables as $key2 => $table2 ) {
                if ( $key1 >= $key2 ) continue;
                $foreignKey = see_engine_database::schema( $table2 )->foreignKey;
                if ( $foreignKey ) {
                    $pos = strpos($foreignKey['reference'], '.');
                    $referenceTable = substr($foreignKey['reference'], 0, $pos);
                }
                if ( $foreignKey && $referenceTable == $table1 ) {
                    $join .= ($join ? '' : $table1).' join '.(in_array($table2, $joinTable) ? $table1 : $table2).' on '.$table2.'.'.$foreignKey['key'].'='.$foreignKey['reference'];
                    $joinTable[] = $table1;
                    $joinTable[] = $table2;
                } else if ( $mainForeignKey && $mainReferenceTable == $table2 ) {
                    $join .= ($join ? '' : $table1).' join '.(in_array($table2, $joinTable) ? $table1 : $table2).' on '.$table1.'.'.$mainForeignKey['key'].'='.$mainForeignKey['reference'];
                    $joinTable[] = $table1;
                    $joinTable[] = $table2;
                }
            }
        }

        return $join;
    }

    static public function resolverOrder( $order, $tableName='' )
    {
        if ( empty($order) ) return '';

        if ( is_string($order) ) $order = explode(',', $order);
        foreach ( $order as $key => $val ) {
            $tb = '';
            is_numeric($key) && ($val = explode(' ', $val)) && ($key = $val[0] && $val = $val[1]);
            strpos($key, '.') && ($key = explode('.', $key)) && ($tb = $key[0] && $key = $key[1]);
            $key = $tb ? $tb . '.' . $key : $tableName . '.' . $key;
            $return[] = $key . ' ' . $val;
        }

        return implode(',', $return);
    }

    static public function resolverGroup( $group, $tableName='' )
    {
        if ( empty($group) ) return '';

        if ( is_string($group) ) $group = explode(',', $group);
        foreach ( $group as $key => $val ) {
            $tb = '';
            is_numeric($key) && ($key = $val) && ($val = '');
            strpos($key, '.') && ($key = explode('.', $key)) && ($tb = $key[0]) && ($key = $key[1]);
            $key = $tb ? $tb . '.' . $key : $tableName . '.' . $key;
            $by[] = $key;
            $val && $hv[] = $val;
        }

        return implode(',', $by) . ( $hv ? ' having '.implode(',', $hv) : '' );
    }

}
