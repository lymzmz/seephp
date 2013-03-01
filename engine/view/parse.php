<?php
/**
 * 视图插件类
 */

class see_view_parse {

    /**
     * 语言包
     *
     * @param array $params
     *
     * @return string
     */
    static public function parse_lang( $params )
    {
        $html = '<?php echo see_engine_kernel::lang('.$params['data'].'); ?>';

        return $html;
    }

    /**
     * URL组织
     *
     * @param array $params
     *
     * @return string
     */
    static public function parse_url( $params )
    {
        $html = '<?php echo see_engine_kernel::url('.$params['data'].'); ?>';

        return $html;
    }

    static public function parse_img( $params )
    {
        $host = see_engine_config::load( 'application' )->fileServer;
        $host = $host ? $host : see_engine_request::host( false );
        $file = $host.'/statics/images/<?php echo ' . $params['data'] . '; ?>';
        $html = '<img src="' . $file . '"';
        unset($params['data']);
        foreach ( $params as $key => $val ) $html .= ' ' . $key . '="<?php echo '.$val.'; ?>"';
        $html .= '/>';

        return $html;
    }

}
