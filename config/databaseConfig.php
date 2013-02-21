<?php

return array(

    'master' => array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
            'port' => 3306,
            'name' => 'new'
        ),
    'slave' => array(
            array(
                'host' => 'localhost',
                'username' => 'root',
                'password' => 'root',
                'port' => 3306,
                'name' => 'new'
            )
        ),
    'cache' => false,
    'cache_dir' => ROOT_DIR.'/cache/database'

);
