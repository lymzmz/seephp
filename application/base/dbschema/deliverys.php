<?php

return array(

    'columns' => array(
        'delivery_id' => array(
            'type' => 'mediumint(8) unsigned',
            'extra' => 'auto_increment'
        ),
        'order_id' => array(
            'fkey' => 'base_orders.order_id',
            'label' => see_engine_kernel::lang('订单号'),
        ),
        'bn' => array(
            'type' => 'varchar(30)'
        ),
        'reship_name' => array(
            'type' => 'varchar(20)'
        )
    ),
    'primaryKey' => 'delivery_id',
    'foreignKey' => array('key' => 'order_id', 'reference' => 'base_orders.order_id'),
    'index' => array(

    ),
    'engine' => 'innodb',
    'version' => 1.0

);
