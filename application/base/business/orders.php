<?php

class see_bsn_base_orders extends see_app_business {

    /**
     * 购买
     */
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
            'member_id' =>      $data['member_id'],
            'cate_id' =>        $data['cate_id'],
            'name' =>           $data['name'],
            'buy_time' =>       empty($data['buy_date']) ? time() : strtotime($data['buy_date']),
            'expire_time' =>    empty($data['expire_date']) ? 0 : strtotime($data['expire_date']),
            'notify' =>         $data['notify'] == 'true' ? 1 : 0,
            'price' =>          empty($data['price']) ? 0.00 : $data['price'],
            'thumbnail' =>      empty($data['thumb']) ? '' : $data['thumb'],
            'memo' =>           empty($data['memo']) ? '' : $data['memo'],
            'create_time' =>    time(),
        );
        $result = see_engine_kernel::model('orders')->insert($_data);

        return $result;
    }

    /**
     * 移除
     */
    public function remove( $data )
    {
         if ( empty($data['order_id']) || empty($data['member_id']) ) {
            $this->error('数据错误');

            return;
        }

        $_filter = array(
            'order_id' => $data['order_id'],
            'member_id' => $data['member_id']
        );
        $_data = array(
            'status' => 0
        );
        $result = see_engine_kernel::model('orders')->update($_data, $_filter);

        return $result;
    }

}
