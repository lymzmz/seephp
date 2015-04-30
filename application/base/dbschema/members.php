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

/**

create table base_members (
    member_id mediumint(8) unsigned not null auto_increment,
    username varchar(50) not null comment '用户名',
    password varchar(32) not null comment '密码',
    create_time int(10) unsigned not null default 0 comment '创建时间',
    primary key (member_id),
    index idx_userpass (username, password),
    index idx_user (username)
) engine = myisam default charset = utf8;

*/
