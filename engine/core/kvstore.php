<?php
/**
 * key/value数据库实例类(nosql)
 */

class see_engine_kvstore {

    /**
     * 实例化一个nosql数据库
     *
     * @param array $config 配置项
     *
     * @return object
     */
    static public function instance( $config_arr )
    {
        $class_name = 'see_kv_' . $config_arr['engine'];
        $kv = new $class_name( $config_arr );

        return $kv;
    }

}
