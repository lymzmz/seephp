<?php

header('Content-type:text/html;charset=utf8');
define('DEBUG', false);
define('NEW_LINE', empty($_SERVER['HTTP_HOST']) && $_SERVER['argc'] ? PHP_EOL : '<br/>');
define('ROOT_DIR', realpath(dirname(__FILE__)));

require ROOT_DIR.'/engine/system.php';

see_engine_system::init();
see_engine_system::booting();

function DEBUG() {
    echo NEW_LINE;
    echo '<pre>';
    foreach ( func_get_args() as $val ) print_r($val);
    echo '</pre>';
}
