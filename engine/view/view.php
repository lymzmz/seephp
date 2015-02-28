<?php
/**
 * 视图处理
 */

class see_view_view extends see_view_abstract implements see_view_interface {

    private $_config = null;
    private $_assign = array();
    private $_app = '';

    public function __construct( $config=null )
    {
        $this->_config = (object)$config;
    }

    public function setApp( $app_name_str )
    {
        $this->_app = $app_name_str;
    }

    public function setAssign( $assign_arr )
    {
        $this->_assign = $assign_arr;
    }

    public function display( $file_name_str )
    {
        list($src_file, $tpl_file) = $this->_get_path( $file_name_str );

        if ( !file_exists($src_file) ) {
            throw new Exception('template file not exists ['.$src_file.'].');

            return;
        }
        if ( !file_exists($tpl_file) || filemtime($src_file) > filemtime($tpl_file) ) {
            DEBUG && DEBUG('parse template: ', $src_file);

            $this->_parse_template( $file_name_str );
        }
        $this->_output( $tpl_file );
    }

    private function _output( $file_name_str )
    {
        DEBUG && DEBUG('display page to screen: ', $file_name_str);
        extract($this->_assign);

        require $file_name_str;
    }

    private function _get_path( $file_name )
    {
        $src_file = ROOT_DIR . '/application/' . $this->_app . '/view/' . $this->_config->template . '/' . $file_name;
        $tpl_file = ROOT_DIR . '/cache/template/' . $this->_app . '_' . $this->_config->template . '_' . str_replace('/', '_', $file_name) . '.php';

        return array($src_file, $tpl_file);
    }

    private function _parse_template( $file_name_str )
    {
        list($src_file, $tpl_file) = $this->_get_path( $file_name_str);
        $html = file_get_contents( $src_file );
        $pattern = '/\<\{(\w+)?\s*(.*?)\}\>/';
        $html = preg_replace_callback( $pattern, array( $this, '_parse_function' ), $html );
        file_put_contents( $tpl_file, $html, LOCK_EX );
    }

    /**
     * 解析流程控制结构或函数
     *
     * @param array $row_arr array(0=>the matchs string 1=>function name 2=>arguments)
     * <input type="text" value="<?php echo "aaa"; ?>"/>
     *
     * @return string
     */
    private function _parse_function( $row_arr )
    {
        if ( ($pos = strpos($row_arr[1], '_')) ) {
            $function = substr($row_arr[1], 0, $pos);
            $sign = substr($row_arr[1], $pos + 1);
        } else
            $function = $row_arr[1];
        switch ( $function ) {
            case '':
                $string = '<?php echo '.$this->_parse_var( $row_arr[2] ).'; ?>';
                break;
            case 'if':
                if ( false === ($arguments = $this->_parse_arguments( $row_arr[2], 1 )) ) {//带比较和逻辑运算符的
                    $arguments = $this->_parse_arguments( $row_arr[2], 3 );
                    $arguments = $arguments[0];
                }
                $string = '<?php if ('.$arguments.') { ?>';
                break;
            case 'elseif':
                if ( false === ($arguments = $this->_parse_arguments( $row_arr[2], 1 )) ) {
                    $arguments = $this->_parse_arguments( $row_arr[2], 3 );
                    $arguments = $arguments[0];
                }
                $string = '<?php else if ('.$arguments.') { ?>';
                break;
            case 'else':
                $string = '<?php } else { ?>';
                break;
            case 'fi':
                $string = '<?php } ?>';
                break;
            case 'for':
                $arguments = $this->_parse_arguments( $row_arr[2], 2 );//key=value形式的
                if ( empty($arguments['from']) )
                    throw new Exception('missing the key \'from\' for function \'for\' in file '.$file_name);
                if ( empty($arguments['item']) )
                    throw new Exception('missing the key \'item\' for function \'for\' in file'.$file_name);
                $string = '<?php foreach ( '.$arguments['from'].' as '.
                    (empty($arguments['key']) ? $arguments['item'] : $arguments['key'].'=>'.$arguments['item']).' ) { ?>';
                break;
            case 'done':
                $string = '<?php } ?>';
                break;
            case 'input':
                $arguments = $this->_parse_arguments( $row_arr[2], 2 );
                if ( empty($sign) )
                    throw new Exception('missing the key \'type\' for function \'input\' in file '.$file_name);
                $string = see_view_input::resolver( $sign, $arguments );
                break;
            case 'include':
                $arguments = $this->_parse_arguments( $row_arr[2], 3 );//单纯value形式
                $string = '<?php $this->display( '.$arguments[0].' ); ?>';
                break;
            case 'assign':
                $arguments = $this->_parse_arguments( $row_arr[2], 2 );
                $str = array();
                foreach ( $arguments as $key => $val )
                    $str[] = '$' . $this->_parse_var( $key ) . '=' . $this->_parse_var( $val ) . ';';
                $string = '<?php '.implode(' ', $str).' ?>';
                break;
            case 'img':
                $arguments = $this->_parse_arguments( $row_arr[2], 2 );
                $string = $this->_parse_plugin( $row_arr[1], $arguments, 2 );
                break;
            default:
                $arguments = $this->_parse_arguments( $row_arr[2], 3 );
                $string = $this->_parse_plugin( $row_arr[1], $arguments, 3 );
        }

        return $string;
    }

    /**
     * 参数解析
     *
     * @param string $arguments_str
     * @param interger $type_int 1|2|3
     *
     * @return array
     */
    private function _parse_arguments( $arguments_str, $type_int )
    {
        switch ( $type_int ) {
            case 1: /* (key===value and key<>value) */
                $arguments_str = preg_replace('/\s+(and)\s+/', ' && ', $arguments_str);
                $arguments_str = preg_replace('/\s+(or)\s+/', ' || ', $arguments_str);
                $arguments_str = preg_replace_callback('/(\$[^\)\s\=\<\>]+)/', array($this, '_parse_var'), $arguments_str);
                $args = $arguments_str;
                break;
            case 2: /* (key=value key=value key=value) */
                $pattern = '/(\w+)=(["\']?)(.*?)\2\s/';
                preg_match_all($pattern, $arguments_str.' ', $matchs, PREG_SET_ORDER);
                if ( count($matchs) )
                    foreach ( $matchs as $val )
                        $args[$val[1]] = $this->_parse_var( $val[2].$val[3].$val[2] );
                break;
            case 3: /* (value value value) */
                $pattern = '/(["\']?)(.*?)\1\s/';
                preg_match_all($pattern, $arguments_str.' ', $matchs, PREG_SET_ORDER);
                if ( count($matchs) )
                    foreach ( $matchs as $val )
                        $args[] = $this->_parse_var( $val[1].$val[2].$val[1] );
                break;
            default: $args = null;
        }

        return empty($args) ? false : $args;
    }

    /**
     * 变量解析
     *
     * @param string $var_str "xxx" $xxx.aaa.bbb.0.ccc xxx
     *
     * @return mixed
     */
    private function _parse_var( $var_str )
    {
        $var = '';
        is_array($var_str) && ($var_str = $var_str[1]);
        if ( substr($var_str, 0, 1) == '$' ) {
            foreach ( explode('.', $var_str) as $key => $val )
                $var .= ( $key == 0 ? $val : '[' . ( substr($val, 0 ,1) == '$' || is_numeric($val) ? $val : '\''.$val.'\'' ) . ']' );
        } else {
            //todo 可以解析更多的变量类型
            $var = $var_str;
        }

        return $var;
    }

    protected function _parse_plugin( $method, $params, $type )
    {
        /* 标准方法 */
        if ( function_exists($method) ) {
            $html = '<?php echo '.$method.'('.implode(', ', $params).'); ?>';

            return $html;
        }

        /* 自定义方法 */
        $class_name = array(
                see_engine_kernel::singleApp( $this->_app )->plugin( 'view' ), /* 当前访问app */
                see_engine_kernel::singleApp( see_engine_config::app() )->plugin( 'view' ), /* 基础服务app */
                'see_view_parse' /* 底层解析 */
            );
        $method = 'parse_'.$method;
        foreach ( $class_name as $class ) {
            if ( !method_exists( $class, $method ) ) continue;

            return $type == 2 ? call_user_func( array($class, $method), $params ) : call_user_func_array( array($class, $method), $params );
        }

        return 'unknow function \''.$method.'\'';
    }

}
