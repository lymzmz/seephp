<?php

define('DEBUG', true);
define('NEW_LINE', empty($_SERVER['HTTP_HOST']) && $_SERVER['argc'] ? PHP_EOL : '<br/>');
define('ROOT_DIR', realpath(dirname(__FILE__)));

require ROOT_DIR.'/engine/system.php';

see_engine_system::init();
see_engine_system::booting();

function DEBUG() {
    echo NEW_LINE;
    foreach ( func_get_args() as $val ) print_r($val);
}
