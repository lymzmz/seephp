<?php

class see_app_plugin extends see_app_abstract {

    /**
     * 插件层报错
     *
     * @param string $msg 提示信息
     */
    public function error( $msg )
    {
        throw new Exception($msg);
    }

}
