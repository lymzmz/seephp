<?php
/**
 * 当前请求类
 */

class see_engine_request {

    static private $_info = null;

    private function __construct(){}

    /**
     * 登陆页地址
     *
     * @return string
     */
    static public function login()
    {
        return self::url( see_engine_config::load( 'application' )->loginEntry );
    }

    /**
     * 默认页地址
     *
     * @return string
     */
    static public function index()
    {
        return self::url( see_engine_config::load( 'application' )->defaultEntry );
    }

    /**
     * 当前请求的APP名称
     *
     * @return string
     */
    static public function app()
    {
        return self::mapper()->sys[0];
    }

    /**
     * 当前请求的域名（完整域名或只是域名）
     *
     * @param bool $full 是否取完整信息
     *
     * @return string
     */
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

    /**
     * 当前请求的参数，返回参数数组对象
     *
     * sys=array(0=>app,1=>ctl,2=>act)系统级参数
     * get=$_GETget提交的参数  post=$_POSTpost提交的参数  cookie=$_COOKIEcookie包含的参数
     * guide=array(0=>controller,1=>action)导航参数
     *
     * @return object
     */
    static public function mapper()
    {
        if ( empty(self::$_info) ) {
            self::_mapper();
        }

        return empty(self::$_info) ? false : self::$_info;
    }

    /**
     * 具体请求解析，sys 系统级参数，get url参数
     *
     * @param array|string array(app=>app,ctl=>ctl,act=act,other=>other) or base/default/index/id/1/name/mick
     * @param bool $is_default 是否是框架默认的分隔字符"/"，不是的话则取系统配置的值
     *
     * @return array
     */
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

    /**
     * array(
     *      sys => array(app, ctl, act),
     *      get => array(id=>1, name=>mick)
     *      post => array(id=>1, name=>mick)
     *      guide => array(ctl, act)
     *  )
     *
     * @access private
     */
    static private function _mapper()
    {
        $request = count($_GET) ? $_GET : $_SERVER['PATH_INFO'];
        if ( empty($request) ) {
            $request = see_engine_config::load( 'application' )->defaultEntry;
            self::$_info = self::resolverRequest( implode('/', $request), true );
        } else {
            self::$_info = self::resolverRequest( $request, false );
        }

        self::$_info['cookie'] = $_COOKIE;
        self::$_info['post'] = $_POST;
        self::$_info['guide'][] = 'see_ctl_' . self::$_info['sys'][0] . '_' . self::$_info['sys'][1];//class name
        self::$_info['guide'][] = self::$_info['sys'][2];//method name
        unset($_GET, $_POST, $_COOKIE);
        self::$_info = (object)self::$_info;
    }

    /**
     * 组织URL方法
     *
     * @param mixed string|array base/default/index | array(base,default,index)
     *
     * @return string
     */
    static public function url( $url='' )
    {
        $host = self::host( true );
        $url = $url ? ( is_array($url) ? implode('/', $url) : $url ) : '';
        $request = $url ? (object)self::resolverRequest( $url ) : self::mapper();
        if ( see_engine_config::load( 'system' )->url == 'get' ) {
            $url = $host.'?app='.$request->sys[0].'&ctl='.$request->sys[1].'&act='.$request->sys[2];
            if ( is_array($request->get) && count($request->get) )
                foreach ( $request->get as $key => $val )
                    $url .= '&'.$key.'='.$val;
        } else {
            $sep = see_engine_config::load( 'system' )->urlSep;
            $url = $host . $sep . str_replace('/', $sep, $url);
        }

        return $url;
    }

}
