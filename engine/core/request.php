<?php

class see_engine_request {

    static private $_info = null;

    private function __construct(){}

    static public function host( $full=true )
    {
        if ( $full === true ) {
            $host = substr($_SERVER['SERVER_PROTOCOL'], 0, 5) == 'HTTPS' ? 'https://' : 'http://';
            $host .= $_SERVER['SERVER_NAME'];
            $host .= ( $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ) ? '' : ':'.$_SERVER['SERVER_PORT'];
            $host .= $_SERVER['SCRIPT_NAME'];
        } else
            $host = $_SERVER['SERVER_NAME'];

        return $host;
    }

    static public function mapper()
    {
        if ( empty(self::$_info) ) {
            self::_mapper();
        }

        return empty(self::$_info) ? false : self::$_info;
    }

    static public function resolverRequest( $request, $is_default=true )
    {
        if ( !is_array($request) ) {
            $url_sep = $is_default === false ? see_engine_config::load( 'system' )->urlSep : '/';
            if ( substr($request, 0, 1) == $url_sep ) $request = substr($request, 1);
            $uri = explode($url_sep, $request);
            $info['sys'][] = array_shift($uri);//app
            $info['sys'][] = array_shift($uri);//ctl
            $info['sys'][] = array_shift($uri);//act
            foreach ( $uri as $key => $val ) {
                if ( $key % 2 )
                    $p_v = $val;
                else
                    $p_k = $val;
                if ( isset($p_v) ) {
                    $info['get'][$p_k] = $p_v;
                    unset($p_v);
                }
            }
        } else {
            $info['sys'][] = $request['app'];
            $info['sys'][] = $request['ctl'];
            $info['sys'][] = $request['act'];
            $info['get'] = $request;
        }

        return $info;
    }

    static private function _mapper()
    {
        $request = count($_GET) ? $_GET : $_SERVER['PATH_INFO'];
        if ( empty($request) ) {
            $request = see_engine_config::load( 'application' )->defaultEntry;
            self::$_info = self::resolverRequest( implode('/', $request), true );
        } else {
            self::$_info = self::resolverRequest( $request, false );
        }

        self::$_info['post'] = $_POST;
        self::$_info['cookie'] = $_COOKIE;
        self::$_info['guide'][] = 'see_ctl_' . self::$_info['sys'][0] . '_' . self::$_info['sys'][1];//class name
        self::$_info['guide'][] = self::$_info['sys'][2];//method name
        unset($_GET, $_POST, $_COOKIE);
        self::$_info = (object)self::$_info;
    }

}
