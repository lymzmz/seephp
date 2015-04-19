<?php

return array(
    'comment' => see_engine_kernel::lang('会员订单表'),
    'columns' => array(
        'order_id' => array(
            'type' => 'mediumint(8) unsigned',
            'extra' => 'auto_increment'
        ),
        'member_id' => array(
            'fkey' => 'base_members.member_id',
            'label' => see_engine_kernel::lang('会员用户名')
        ),
        'cate_id' => array(
            'fkey' => 'base_cates.cate_id',
            'label' => see_engine_kernel::lang('分类名')
        ),
        'name' => array(
            'type' => 'varchar(100)',
            'label' => see_engine_kernel::lang('名称')
        ),
        'buy_time' => array(
            'type' => 'int(10)',
            'alias' => 'date',
            'label' => see_engine_kernel::lang('购买日期')
        ),
        'expire_time' => array(
            'type' => 'int(10)',
            'alias' => 'date',
            'label' => see_engine_kernel::lang('下单时间')
        ),
        'notify' => array(
            'type' => 'tinyint(1)',
            'alias' => 'int',
            'label' => see_engine_kernel::lang('到期提醒')
        ),
        'price' => array(
            'type' => 'decimal(10,2)',
            'alias' => 'float',
            'label' => see_engine_kernel::lang('价格')
        ),
        'thumbnail' => array(
            'type' => 'varchar(100)',
            'label' => see_engine_kernel::lang('缩略图')
        ),
         'create_time' => array(
            'type' => 'int(10)',
            'alias' => 'date',
            'label' => see_engine_kernel::lang('创建时间')
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
