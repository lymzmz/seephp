<?php

return array(
    'comment' => see_engine_kernel::lang('会员表'),
    'columns' => array(
        'member_id' => array(
            'type' => 'mediumint (8) unsigned',
            'extra' => 'auto_increment',
        ),
        'username' => array(
            'type' => 'varchar(50)',
            'label' => see_engine_kernel::lang('用户名'),
        ),
        'password' => array(
            'type' => 'varchar(32)'
        ),
        'create_time' => array(
            'type' => 'int(10)',
            'label' => see_engine_kernel::lang('创建时间'),
            'alias' => 'date'
        )
    ),
    'primaryKey' => array('member_id'),
    'foreignKey' => null,
    'index' => array(
        'idx_userpass' => array(
            'columns' => array('username', 'password'),
            'type' => 'index'
        ),
        'idx_user' => array(
            'columns' => array('username'),
            'type' => 'unique'
        )
    ),
    'engine' => 'innodb',
    'version' => 1.0
);
