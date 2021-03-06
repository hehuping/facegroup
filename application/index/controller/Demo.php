<?php
namespace app\index\controller;



class Demo extends \think\Controller
{

    protected $appid = 'wxb096e505f9556191';
    protected $secret = 'd4624c36b6795d1d99dcf0547af5443d';

    protected $base_url = 'https://www.yiyazhe.com/uploads/';

    public function index(){
        $this->assign([
            'name'  => 'ThinkPHP',
            'email' => 'thinkphp@qq.com'
        ]);
        // 模板输出
        return $this->fetch('index');
    }

    public function getSgin(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->secret}";
        $json = file_get_contents($url);
        $token = (array)json_decode($json);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$token['access_token']}&type=jsapi";
        $json = file_get_contents($url);

        $ticket = (array)json_decode($json);
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $noncestr=str_shuffle('Wm3WZYTPz0wzccnW');
        $jsapi_ticket=$ticket['ticket'];
        $timestamp=time();
        $w_url='https://www.yiyazhe.com/index/demo/getSgin';

        $jsapi_ticket='jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$w_url;
        $str = sha1($jsapi_ticket);


        echo $jsapi_ticket;



        $this->assign([
            'noncestr'  =>$noncestr,
            'time' => $timestamp,
            'str' => $str,
        ]);
        // 模板输出
        return $this->fetch('index2');
    }
}
