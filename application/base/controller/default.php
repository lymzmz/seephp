<?php

class see_ctl_base_default extends see_app_controller {
function testbbb(){$this->fail('hijklmn');return;
    $result = see_engine_kernel::model('members')->findOne('*', array('username' => 'rick'));
    $this->fail('abcdefg');
}
    function authGroup() {
        return array('guest','member');
    }

    /**
     * 登录
     */
    function login()
    {
        $data = see_engine_kernel::request()->post;

        try {
            $result = see_engine_kernel::auth()->login( $data['username'], $data['passwd'] );
            if ( $result === true ) {
                $this->succ('登录成功');
            } else {
                $this->fail('用户名或密码错误');
            }
        } catch ( Exception $event ) {
            $this->fail($event->getMessage());
        }
    }

    /**
     * 注册
     */
    function register()
    {
        $data = see_engine_kernel::request()->post;

        $data['password'] = $data['passwd'];

        try {
            $result = see_engine_kernel::business('members')->register( $data );
            if ( $result === false ) {
                $this->fail('注册失败，请稍后重试');
            } else {
                $this->succ('注册成功');
            }
        } catch ( Exception $event ) {
            $this->fail($event->getMessage());
        }
    }

    /**
     * 添加新品
     */
    function addGoods()
    {
        $data = see_engine_kernel::request()->post;

        $data['member_id'] = see_engine_kernel::user()->member_id;

        try {
            $result = see_engine_kernel::business('orders')->buy( $data );
            if ( $result === false ) {
                $this->fail('添加失败，请稍后重试');
            } else {
                $this->succ('添加成功');
            }
        } catch ( Exception $event ) {
            $this->fail($event->getMessage());
        }
    }

    /**
     * 新品列表
     */
    function goodsList()
    {
        $data = see_engine_kernel::request()->post;
        $limit = $data['page_size'];
        $offset = ($data['page'] - 1) * $limit;

        $filter = array(
            'member_id' => see_engine_kernel::user()->member_id,
            'order_id|>' => $data['cursor'],
        );
        $order = 'create_time desc';
        $records = see_engine_kernel::model('orders')->findList('*', $filter, $order, '', $offset, $limit);
        if ( empty($records) ) {
            $this->fail('已是最新');

            return;
        }

        foreach ( $records as $key => $val ) {
            $buy_date = date('Y-m-d', $val['buy_time']);
            $expire_date = date('Y-m-d', $val['expire_time']);
            $notify = $val['notify'] == 1 ? '是' : '否';
            $thumbnail = $val['thumbnail'];
            $price = $val['price'];
            $name = $val['name'];
            $info[$key]['html'] = <<<EOT
                    <li class="mui-table-view-cell mui-media">
                        <div class="mui-slider-left mui-disabled">
                            <button class="mui-btn mui-btn-red">+</button>
                        </div>
                        <div class="mui-slider-right mui-disabled">
                            <button class="mui-btn mui-btn-red">-</button>
                        </div>
                        <a href="javascript:;" class="mui-slider-handle">
                            <img class="mui-media-object mui-pull-left" src="$thumbnail"  />
                            <div class="mui-media-body">
                                <label>$name</label>
                                <p class="mui-ellipsis">购买日期：$buy_date</p>
                                <p class="mui-ellipsis">有效期：$expire_date</p>
                                <p class="mui-ellipsis">到期提醒：$notify</p>
                                <p class="mui-ellipsis">价格：$price 元</p>
                            </div>
                        </a>
                    </li>
EOT;
            $info[$key]['order_id'] = $val['order_id'];
        }

        $this->succ($info);
    }

}
