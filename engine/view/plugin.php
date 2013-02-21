<?php
/**
 * 视图插件类
 */

class see_view_plugin {

    /**
     * 语言包
     *
     * @param array $params
     *
     * @return string
     */
    static public function template_lang( $params )
    {
        $view = see_engine_config::load( 'application' )->view;
        $return = see_engine_kernel::lang( $params[0], $view['language'], see_engine_request::app() );
        if ( false === $return ) {
            $return = see_engine_kernel::lang( $params[0], $view['language'], see_engine_config::app() );
        }

        return $return ? $return : $params[0];
    }

    /**
     * URL组织
     *
     * @param array $params
     *
     * @return string
     */
    static public function template_url( $params )
    {
        return see_engine_kernel::url( $params[0] );
    }

}
