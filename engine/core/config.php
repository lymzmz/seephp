<?php
/**
 * 系统配置类
 */

class see_engine_config {

    static private $_data = array();

    /**
     * 载入系统应用程序配置
     *
     * @param string $name 配置名称 application|system|database|kvstore
     *
     * @return object
     */
    static public function load( $name )
    {
        if ( empty($name) ) return null;

        if ( !isset(self::$_data[$name]) ) {
            self::$_data[$name] = require ROOT_DIR.'/config/'.$name.'Config.php';
            self::$_data[$name] = (object)self::$_data[$name];
            DEBUG && DEBUG($name, ' config: ', self::$_data[$name]);
        }

        return self::$_data[$name];
    }

    /**
     * 获取默认应用配置项值
     *
     * @return string
     */
    static public function app()
    {
        return self::load( 'application' )->defaultApp;
    }

}
