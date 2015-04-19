<?php

class see_app_business extends see_app_abstract {

    /**
     * 业务层报错
     *
     * @param string $msg 提示信息
     */
    public function error( $msg )
    {
        throw new Exception($msg);
    }

}
