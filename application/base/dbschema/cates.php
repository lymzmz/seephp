<?php

return array(
    'comment' => see_engine_kernel::lang('商品分类表'),
    'columns' => array(
        'cate_id' => array(
            'type' => 'mediumint(8) unsigned',
            'extra' => 'auto_increment'
        ),
        'member_id' => array(
            'fkey' => 'base_members.member_id',
            'label' => see_engine_kernel::lang('会员用户名')
        ),
        'cate_name' => array(
            'type' => 'varchar(100)',
            'label' => see_engine_kernel::lang('名称')
        ),
        'is_order' => array(
            'type' => 'tinyint(1)',
            'label' => see_engine_kernel::lang('排序'),
        ),
        'create_time' => array(
            'type' => 'int(10)',
            'alias' => 'date',
            'label' => see_engine_kernel::lang('创建时间')
        ),
    ),
    'primaryKey' => array('cate_id'),
    'foreignKey' => array('key' => 'member_id', 'reference' => 'base_members.member_id'),
    'engine' => 'myisam',
    'version' => 1.0
);

/**

create table base_cates (
    cate_id mediumint(8) unsigned not null auto_increment,
    member_id mediumint(8) unsigned not null default 0 comment '会员ID',
    cate_name varchar(100) not null comment '名称',
    is_order tinyint(1) not null default 0 comment '排序',
    create_time int(10) unsigned not null default 0 comment '创建时间',
    primary key (cate_id)
) engine = myisam default charset = utf8;

*/
