<?php

return array(
    'comment' => kernel::lang('会员订单表'),
    'columns' => array(
        'order_id' => array(
            'type' => 'mediumint(8) unsigned',
            'extra' => 'auto_increment'
        ),
        'member_id' => array(
            'fkey' => 'base_members.member_id',
            'label' => see_engine_kernel::lang('会员用户名')
        ),
        'order_bn' => array(
            'type' => 'varchar(30)',
            'label' => see_engine_kernel::lang('订单号')
        ),
        'create_time' => array(
            'type' => 'int(10)',
            'alias' => 'date',
            'label' => see_engine_kernel::lang('下单时间')
        ),
        'memo' => array(
            'type' => 'varchar(200)',
            'isnull' => true,
            'label' => see_engine_kernel::lang('备注')
        )
    ),
    'primaryKey' => array('order_id'),
    'foreignKey' => array('key' => 'member_id', 'reference' => 'base_members.member_id'),
    'index' => array(
        'idx_memberorder' => array(
            'columns' => array('member_id', 'order_bn'),
            'type' => 'index'
        ),
        'idx_order' => array(
            'columns' => array('order_bn'),
            'type' => 'unique'
        ),
        'idx_time' => array(
            'columns' => array('create_time'),
            'type' => 'index'
        )
    ),
    'engine' => 'myisam',
    'version' => 1.0
);
