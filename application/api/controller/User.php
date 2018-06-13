<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/13 0013
 * Time: 上午 10:07
 */
namespace app\api\controller;

use app\api\model\Member;
use think\Db;
use think\Request;

class User extends Base{

    public function _initialize()
    {
        parent::_initialize();
    }

    //注册
    public function register(Request $request)
    {
        if ($request->isPost()){
            $this->returnJson('403','错误访问');
        }
        $post = $this->request->param();

        $member_mod = new Member();
        $res = $member_mod->addData($post);
        $this->returnJson($res);
    }

    //登录
    public function login(Request $request)
    {
        if ($request->isPost()){
            $this->returnJson('403','错误访问');
        }

        $post = $this->request->param();
        $dispose = ['username'=>'账号','password'=>'密码'];
        $data = $this->dataDispose($post,true,$dispose);

        $userInfo = Db::name('member')->where('phone',$data['username'])->find();
        if (!$userInfo) $this->returnJson('500','账号不存在');
        if (md5($data['password']) != $userInfo['password']) $this->returnJson('500','密码错误');

        $user_id = $this->authcode($userInfo['uid'], 'ENCODE', AUTH_KEY);
        $this->returnJson('200','登录成功',$user_id);

    }

}