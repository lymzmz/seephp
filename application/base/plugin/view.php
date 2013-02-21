<?php

class see_plg_base_view extends see_app_plugin {

    public function template_langaa( $data )
    {
        echo $data;
    }

    public function template_abcdefg( $data )
    {
        print_r($data);
    }

}
