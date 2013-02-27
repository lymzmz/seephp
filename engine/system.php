<?php

final class see_engine_system {

    static private $_config = null;
    static private $_request = null;
    static private $_kvServer = null;

    private function __construct( $config )
    {
        //forbid to instance
    }

    static public function init()
    {
        set_error_handler( array(__CLASS__, '_showError') );
        set_exception_handler( array(__CLASS__, '_showException') );
        spl_autoload_register( array(__CLASS__, '_autoload') );
        self::$_config = see_engine_config::load( 'system' );
        self::$_kvServer = see_engine_kvstore::instance( see_engine_config::load( 'system')->kvServer );
        DEBUG && ob_start();
    }

    static public function booting()
    {
        if ( false === see_engine_request::mapper() ) {
            trigger_error('the guide path error.', E_USER_ERROR);

            exit( E_ERROR );
        }
        list($class_name, $method) = see_engine_request::mapper()->guide;
        if ( !class_exists($class_name) || !in_array($method, get_class_methods($class_name)) ) {
            trigger_error('the class ['.$class_name.'] is not exists or the method ['.$method.'] is not exists.', E_USER_ERROR);

            exit( E_ERROR );
        }

        self::launch( see_engine_request::mapper() );
    }

    static public function launch( $request )
    {
        list($class_name, $method) = $request->guide;
        $controller = new $class_name;

        if ( see_engine_config::load( 'application' )->auth === true ) {
            if ( false !== ($groupIds = call_user_func( array($controller, 'authGroup') )) ) {
                $user = see_engine_user::instance( $request->cookie['U'] );
                if ( !in_array('guest', $groupIds) && $user->verifyAccount( $request->cookie['T'] ) === false ) {
                    call_user_func( array($controller, 'redirect'), see_engine_request::login() );

                    exit();
                }
                if ( $user->verifyGroup( $groupIds ) === false ) {
                    call_user_func( array($controller, 'error'), 'your group can\'t access this page' );

                    exit();
                }
            } else {
                call_user_func( array($controller, 'redirect'), see_engine_request::index() );

                exit();
            }
        }

        if ( self::$_config->cache === false
            || call_user_func( array($controller, 'enableCache') ) === false
            || !empty($request->post) ) {//todo 缓存开关可以具体到某个action

            DEBUG && DEBUG('page: ', 'cache is closed so realtime to get result.'.NEW_LINE.NEW_LINE);

            call_user_func_array( array($controller, $method), $request->get );
        } else if ( false !== self::$_kvServer->fetch( self::_page_cache_global_key( $request ), $contents ) ) {
            DEBUG && DEBUG('page: ', 'cache open and get result from cache.'.NEW_LINE.NEW_LINE);

            echo $contents;
        } else {
            DEBUG && DEBUG('page: ', 'cache open but no cache or cache has expired, so realtime to get result.'.NEW_LINE.NEW_LINE);

            ob_start();
            call_user_func_array( array($controller, $method), $request->get );
            $contents = ob_get_clean();
            !DEBUG && self::$_kvServer->store( self::_page_cache_global_key( $request ), $contents );
            echo $contents;
        }
    }

    static public function parseClassName( $class_name )
    {
        $class = explode('_', $class_name);
        if ( array_shift($class) !== 'see' ) {
            trigger_error('please use the name of \'see_\' to set the first name with class \''.$class_name.'\'', E_USER_ERROR);

            exit(E_ERROR);
        }
        $sign = array_shift($class);
        $app = array_shift($class);
        count($class) && ($file = implode('/', $class));

        return array('sign' => $sign, 'app' => $app, 'file' => $file);
    }

    static private function _autoload( $class_name )
    {
        $class = self::parseClassName( $class_name );

        $engine = array('see_engine_kernel', 'see_engine_system');
        $sign = $class['sign'] == 'engine' ? ( in_array($class_name, $engine) ? 'engine' : 'core' ) : $class['sign'];
        $app = $class['app'];
        $file = empty($class['file']) ? '' : $class['file'];
        switch ( $sign ) {
            case 'ctl': $file = ROOT_DIR.'/application/'.$app.'/controller/'.$file.'.php';break;
            case 'mdl': $file = ROOT_DIR.'/application/'.$app.'/model/'.$file.'.php';break;
            case 'bsn': $file = ROOT_DIR.'/application/'.$app.'/business/'.$file.'.php';break;
            case 'plg': $file = ROOT_DIR.'/application/'.$app.'/plugin/'.$file.'.php';break;
            case 'core': $file = ROOT_DIR.'/engine/core/'.$app.'.php';break;
            case 'engine': $file = ROOT_DIR.'/engine/'.$app.'.php';break;
            default:
                $file = $sign . '/' . $app . ( $file ? '/'.$file : '' );
                $file = ROOT_DIR.'/engine/'.$file.'.php';
                break;
        }
        if ( !file_exists($file) ) {
            //DEBUG && debug_print_backtrace();

            trigger_error('the class file ['.$file.'] is not exists', E_USER_WARNING);
        } else {

            require $file;
        }
    }

    static private function _page_cache_global_key( $request_obj )
    {
        $ident = serialize($request_obj->guide).serialize($request_obj->get);

        return md5( $ident );
    }

    static public function _showError( $errno, $errstr, $errfile, $errline )
    {
        switch ( $errno ) {
            case E_ERROR:
            case E_USER_ERROR:
                throw new Exception($errstr.' in '.$errfile.' on line:'.$errline.NEW_LINE );break;
            default: //do something
        }
    }

    static public function _showException( $except )
    {
        echo NEW_LINE, NEW_LINE, 'Exception:', $except->getMessage(), ' in ', $except->getFile(), '(', $except->getLine(), ')', NEW_LINE;

        exit( E_ERROR );
    }

}
