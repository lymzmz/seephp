<?php

return array(

    'master' => array(
            'host' => SAE_MYSQL_HOST_M,
            'username' => SAE_MYSQL_USER,
            'password' => SAE_MYSQL_PASS,
            'port' => SAE_MYSQL_PORT,
            'name' => SAE_MYSQL_DB
        ),
    'slave' => array(
            array(
                'host' => SAE_MYSQL_HOST_S,
                'username' => SAE_MYSQL_USER,
                'password' => SAE_MYSQL_PASS,
                'port' => SAE_MYSQL_PORT,
                'name' => SAE_MYSQL_DB
            )
        ),
    'cache' => false,
    'cache_dir' => ROOT_DIR.'/cache/database'

);
