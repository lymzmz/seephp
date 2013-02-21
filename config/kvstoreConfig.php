<?php

return array(

    'system' => array(
        'engine' => 'secache',
        'file' => ROOT_DIR.'/cache/system.db'
    ),
    'application' => array(
        'engine' => 'secache',
        'file' => ROOT_DIR.'/cache/application.db'
    )

);
