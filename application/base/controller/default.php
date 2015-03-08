<?php

class see_ctl_base_default extends see_app_controller {

    function authGroup() {
        return array('guest','member');
    }

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

                $this->succ('登陆成功', 'base/member/lists');
            } else {
                //see_engine_request::cookie('error', '用户名或密码错误');
                $this->fail( '用户名或密码错误', see_engine_request::login() );
            }
        }
    }

    function welcome()
    {
        $this->display( 'welcome.html' );
    }

    function navMenu(){
        $menu = array(
                'aaaaaaaaaa','bbbbbbbb','cccccccc'
            );
        echo json_encode($menu);
        return;
        $this->display('nav_menu.html');
    }

}
