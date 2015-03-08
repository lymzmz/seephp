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
    static public function parse_lang( $langeuage )
    {
        $html = '<?php echo see_engine_kernel::lang('.$langeuage.'); ?>';

        return $html;
    }

    /**
     * URL组织
     *
     * @param array $params
     *
     * @return string
     */
    static public function parse_url( $uri )
    {
        if ( $uri == 'statics' ) {
            $html = '<?php echo see_engine_request::host(false); ?>/statics';
        } else {
            $html = '<?php echo see_engine_kernel::url('.$uri.'); ?>';
        }
        return $html;
    }

    static public function parse_img( $params )
    {
        $host = see_engine_config::load( 'application' )->staticsServer;
        $host = $host ? $host : see_engine_request::host( false );
        $file = $host.'/statics/images/<?php echo ' . $params['src'] . '; ?>';
        $html = '<img src="' . $file . '"';
        unset($params['src']);
        foreach ( $params as $key => $val ) $html .= ' ' . $key . '="<?php echo '.$val.'; ?>"';
        $html .= '/>';

        return $html;
    }

}
