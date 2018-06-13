<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/13 0013
 * Time: 上午 10:31
 */
namespace app\api\model;

use think\Model;

class Member extends Model{
    protected $rule = [
        'phone' => 'require',
        'password'  => 'require',
    ];
    protected $message = [
        'phone.require' => '手机号不能为空',
        'password.require' => '密码不能为空',
    ];

    public function addData($data)
    {
        if ($uid = $this->validate($this->rule,$this->message)->insertGetId($data)){
            if ($uid){
                return $res = ['code'=>'500','msg'=>'注册成功'];
            }else{
                return $res = ['code'=>'200','msg'=>'注册失败'];
            }
        }else{
           return $res = ['code'=>'500','msg'=>$this->validate()->getError()];
        }
    }
}