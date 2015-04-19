<?php

class see_bsn_base_cates extends see_app_business {

    /**
     * 购买
     */
    public function add( $data )
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
            'cate_name' => $data['name'],
            'is_order' => 0,
            'create_time' => time(),
        );
        $result = see_engine_kernel::model('cates')->insert($_data);

        return $result;
    }

    /**
     * 移除
     */
    public function remove( $data )
    {
         if ( empty($data['cate_id']) || empty($data['member_id']) ) {
            $this->error('数据错误');

            return;
        }

        $_filter = array(
            'cate_id' => $data['cate_id'],
            'member_id' => $data['member_id']
        );
        $result = see_engine_kernel::model('cates')->delete($_filter);

        return $result;
    }

}
