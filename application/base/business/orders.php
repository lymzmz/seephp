<?php

class see_bsn_base_orders extends see_app_business {

    public function buy( $data )
    {
        if ( empty($data['name']) ) {
            $this->error('名称不能为空');

            return;
        } else if ( empty($data['member_id']) ) {
            $this->error('数据错误');

            return;
        }

        $_data = array(
            'member_id' => $data['member_id'],
            'name' => $data['name'],
            'buy_time' => empty($data['buy_date']) ? 0 : strtotime($data['buy_date']),
            'expire_time' => empty($data['expire_date']) ? 0 : strtotime($data['expire_date']),
            'notify' => $data['notify'] == 'true' ? 1 : 0,
            'price' => empty($data['price']) ? 0.00 : $data['price'],
            'thumbnail' => empty($data['thumb']) ? '' : $data['thumb'],
            'memo' => empty($data['memo']) ? '' : $data['memo'],
            'create_time' => time(),
        );
        $result = see_engine_kernel::model('orders')->insert($_data);

        return $result;
    }

}
