<?php

class see_ctl_base_member extends see_app_controller {

    public function authGroup()
    {
        return array('member');
    }

    function logout()
    {
        if ( see_engine_kernel::auth()->logout() )
            $this->redirect( see_engine_request::index() );
        else
            $this->error('登出失败');
    }

    function lists()
    {
        $data = array(
            array(
                'goods_id' => 1,
                'name' => 'tomato',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
                'cate_name' => 'abcd',
                'thumb' => 'logo.gif',
            ),
            array(
                'goods_id' => 2,
                'name' => 'egg',
                'nums' => 20,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'goods_id' => 3,
                'name' => 'hulu',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            /*array(
                'name' => '黄瓜',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '茄子',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '苹果',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '鸭梨',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '橙子',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '橘子',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'name' => '芹菜',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),*/
        );
        $this->pagedata['lists'] = $data;
        $this->display('lists.html');
    }

}
