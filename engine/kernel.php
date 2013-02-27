<?php
/**
 * 核心接口类，客户端调用
 */

class see_engine_kernel {

    /**
     * @var bool $_is_single 是否单例
     * @access private
     */
    static private $_is_single = false;

    /**
     * @var string $_cur_app 当前APP
     * @access private
     */
    static private $_cur_app = '';

    /**
     * @var object $_instance kernel实例
     * @access private
     */
    static private $_instance = null;

    /**
     * @var object $_kvServer kv服务器实例
     * @access private
     */
    static private $_kvServer = null;

    /**
     * @var object $_dbServer db服务器实例
     * @access private
     */
    static private $_dbServer = null;

    /**
     * @var array $_models APP所有model对象数组
     * @access private
     */
    static private $_models = array();

    /**
     * @var array $_business APP所有业务对象数组
     * @access private
     */
    static private $_business = array();

    /**
     * @var array $_plugins APP所有插件对象数组
     * @access private
     */
    static private $_plugins = array();

    /**
     * @var object $_view 视图对象实例
     * @access private
     */
    static private $_view = null;

    /**
     * @var array $_controllers APP所有控制器对象数组
     * @access private
     */
    static private $_controllers = array();

    /**
     * @var array $_collections APP所有表数据集合对象数组
     * @access private
     */
    static private $_collections = array();

    /**
     * @var array $_dbSchemas APP所有数据表定义对象数组
     * @access private
     */
    static private $_dbSchemas = array();

    /**
     * @var array $_languages APP所有语言包数组
     * @access private
     */
    static private $_languages = array();

    static public function lang( $string, $package=null, $app=null )
    {
        empty($package) && ( $package = see_engine_config::load( 'application' )->view['language'] );
        empty($app) && ( $app = see_engine_config::app() );
        if ( !isset(self::$_languages[$app]) ) {
            self::$_languages[$app] = include ROOT_DIR.'/application/'.$app.'/lang/'.$package.'/language.lg';
        }
        if ( !isset(self::$_languages[$app][$string]) ) {
            /*foreach ( self::$_languages[$app] as $key => $val ) {
                $key = str_replace('%s', '(.*?)', $key);
                $val = str_replace('%', '(.*?)', $val);
                if ( preg_match( $key, $string, $match ) )
                    $string = preg_replace($val, 
            }*///todo 语言包模糊匹配
            return false;
        }

        return self::$_languages[$app][$string];
    }

    static public function user( $user_id=null, $group_id=null )
    {
        $user_id = empty($user_id) ? see_engine_request::mapper()->cookie['U'] : $user_id;

        return see_engine_user::instance( $user_id );
    }

    static public function auth()
    {
        static $_auth = null;
        if ( is_null($_auth) ) $_auth = new see_engine_user;

        return $_auth;
    }

    static public function config( $name )
    {
        if ( empty($name) ) return false;

        return see_engine_config::load( $name );
    }

    static public function request()
    {
        return see_engine_request::mapper();
    }

    static public function url( $url='' )
    {
        return see_engine_request::url( $url );
    }

    static public function view( $config=null )
    {
        $class_name = 'see_app_view';
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_view) ) {
                self::$_view = new $class_name( see_engine_config::load( 'application' )->view );
            }

            return self::$_view;
        } else {
            
            return new $class_name( $config ? $config : see_engine_config::load( 'application' )->view );
        }
    }

    static public function business( $class_name, $params=null )
    {
        $app = self::_application();
        $class_name = 'see_bsn_' . $app . '_' . $class_name;
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_business[$class_name]) ) {
                if ( !class_exists($class_name) ) return null;

                self::$_business[$class_name] = new $class_name( $params );
            }

            return self::$_business[$class_name];
        } else {
            if ( !class_exists($class_name) ) return null;

            return new $class_name( $params );
        }
    }

    static public function model( $class_name, $params=null )
    {
        $app = self::_application();
        $class_name = 'see_mdl_' . $app . '_' . $class_name;
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_models[$class_name]) ) {
                if ( !class_exists($class_name) ) {
                    self::$_models[$class_name] = new see_app_model( $params, $class_name );
                } else {
                    self::$_models[$class_name] = new $class_name( $params );
                }
            }

            return self::$_models[$class_name];
        } else {
            if ( !class_exists($class_name) ) {
                $model = new see_app_model( $params, $class_name );
            } else {
                $model = new $class_name( $params );
            }

            return $model;
        }
    }

    static public function plugin( $class_name, $params=null )
    {
        $app = self::_application();
        $class_name = 'see_plg_' . $app . '_' . $class_name;
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_plugins[$class_name]) ) {
                if ( !class_exists($class_name) ) return null;

                self::$_plugins[$class_name] = new $class_name( $params );
            }

            return self::$_plugins[$class_name];
        } else {
            if ( !class_exists($class_name) ) return null;

            return new $class_name( $params );
        }
    }

    static private function controller( $class_name, $params=null )
    {
        $app = self::_application();
        $class_name = 'see_ctl_' . $app . '_' . $class_name;
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_controllers[$class_name]) ) {
                if ( !class_exists($class_name) ) return null;

                self::$_controllers[$class_name] = new $class_name( $params );
            }

            return self::$_controllers[$class_name];
        } else {
            if ( !class_exists($class_name) ) return null;

            return new $class_name( $params );
        }
    }

    static public function database( $config=null )
    {
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_dbServer) ) {
                self::$_dbServer = see_engine_database::db();
            }

            return self::$_dbServer;
        } else {
            
            return see_engine_database::db( $config );
        }
    }

    static public function collection( $table_name )
    {
        $app = self::_application();
        $table_name = $app . '_' . $table_name;
        if ( self::$_is_single ) {
            self::$_is_single = false;
            if ( !isset(self::$_collections[$table_name]) ) {
                self::$_collections[$table_name] = see_engine_database::collection( $table_name );
            }

            return self::$_collections[$table_name];
        } else {
            
            return see_engine_database::collection( $table_name );
        }
    }

    static public function dbSchema( $table_name )
    {
        $app = self::_application();
        $table_name = $app . '_' . $table_name;
        if ( !isset(self::$_dbSchemas[$table_name]) ) {
            self::$_dbSchemas[$table_name] = see_engine_database::schema( $table_name );
        }

        return self::$_dbSchemas[$table_name];
    }

    static public function dbRecord( $table_name,  $record=array() )
    {
        $app = self::_application();

        return self::singleApp( $app )->collection( $table_name )->record( $record );
    }

    static public function fetch( $key, &$value )
    {
        if ( !isset(self::$_kvServer) ) {
            self::$_kvServer = see_engine_kvstore::instance( see_engine_config::load( 'application' )->kvServer );
        }
        DEBUG && DEBUG('app kvstore get: ', $key);

        return self::$_kvServer->fetch( md5($key), $value );
    }

    static public function store( $key, $value )
    {
        if ( !isset(self::$_kvServer) ) {
            self::$_kvServer = see_engine_kvstore::instance( see_engine_config::load( 'application' )->kvServer );
        }
        DEBUG && DEBUG('app kvstore save: ', $key);

        return self::$_kvServer->store( md5($key), $value );
    }

    static public function single()
    {
        self::$_is_single = true;
        if ( !isset(self::$_instance) ) {
            $class_name = __CLASS__;
            self::$_instance = new $class_name;
        }

        return self::$_instance;
    }

    static public function app( $app_str )
    {
        self::$_cur_app = $app;
        if ( !isset(self::$_instance) ) {
            $class_name = __CLASS__;
            self::$_instance = new $class_name;
        }

        return self::$_instance;
    }

    static public function singleApp( $app_str )
    {
        self::$_is_single = true;
        self::$_cur_app = $app_str;
        if ( !isset(self::$_instance) ) {
            $class_name = __CLASS__;
            self::$_instance = new $class_name;
        }

        return self::$_instance;
    }

    static private function _application()
    {
        if ( empty(self::$_cur_app) ) {
            $app = self::$_cur_app = see_engine_config::app();
        } else {
            $app = self::$_cur_app;
            self::$_cur_app = see_engine_config::app();
        }

        return $app;
    }

}
