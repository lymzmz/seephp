<?php

class see_ctl_base_default extends see_app_controller {

/* ------------------------------------- */
    /**
     * 登录后才可访问网站所有内容
     */
    function authGroup() {
        return array('member');
    }
/* ------------------------------------- */

    function login()
    {
        $data = see_engine_kernel::request()->post;
        if ( empty($data) ) {
            //$this->pagedata['error'] = see_engine_kernel::request()->cookie['error'];
            //see_engine_request::cookie('error', null);
            $this->display('login.html');
        } else {
            $result = see_engine_kernel::auth()->login( $data['username'], $data['password'] );
            if ( $result === true ) {

                $this->succ('登陆成功', 'base/default/lists');
            } else {
                //see_engine_request::cookie('error', '用户名或密码错误');
                $this->fail( '用户名或密码错误', see_engine_request::login() );
            }
        }
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
            'gua' => array(
            array(
                'goods_id' => 1,
                'name' => 'tomato',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            /*array(
                'goods_id' => 2,
                'name' => '鸡蛋',
                'nums' => 20,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
                'goods_id' => 3,
                'name' => '西葫芦',
                'nums' => 5,
                'buy_time' => strtotime('2015-1-5'),
                'end_time' => strtotime('2015-1-25'),
                'cate_id' => 1,
            ),
            array(
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
            ),
        );
        $this->pagedata['lists'] = $data;
        $this->display('lists.html');
    }

    function index( $id, $name )
    {
        $user = see_engine_kernel::user();
        $this->pagedata['username'] = $user->username.'#'.$name;
        $data = array(
                    'member_id' => 1,
                    'order_bn' => mt_rand(0,9).'aaaaaaaaaaaaaa',
                    'create_time' => time(),
                    'memo' => '这是备注'
            );

/*
            //C create
        $colle = kernel::singleApp('base')->collection('orders');
        //$colle->add( $data, false );
        $order_id = $colle->add( $colle->record( $data ), true );

        //kernel::singleApp('base')->model('orders')->insert( $data );
            //C end

            //U update
        $record = kernel::singleApp('base')->collection('orders')->record( $data );
        $record->order_id = $order_id;
        $record->memo .= '__做过一次修改';
        kernel::singleApp('base')->collection('orders')->up( $record );

        //kernel::app('base')->single()->model('orders')->update( array('memo' => '又一次修改'), array('order_id' => 1) );
            //U end

            //D delete
        $colle = kernel::singleApp('base')->collection('orders');
        $record = $colle->record( $colle->dumpByorder_id( 89, '*' ) );
        $colle->del( $record, false );

        //kernel::single()->app('base')->model('orders')->delete( array('order_id' => 1) );
            //D end
        
        $colle->performOperations();
*/
            //R read
        $colle = see_engine_kernel::single()->collection('orders');
//        $result = $colle->setBuilder( 'base_deliverys.bn,base_members.member_id,order_id,order_bn,memo', array('order_id|<'=>66), array('order_id'=>'desc'), null , 1, 5 )->listAll();

        //$result = kernel::single()->app('base')->model('orders')->findResult( array('memo', 'order_id'), array('order_id' => 1) );
            //R end


        echo '<pre>aa';print_r($result);


        $this->pagedata['result'] = array($data);
        $this->display( 'default.html' );
    }

}
