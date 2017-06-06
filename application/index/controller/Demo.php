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

        print_r($json);


       /* $this->assign([
            'json'  => 'ThinkPHP',
            'email' => 'thinkphp@qq.com'
        ]);
        // 模板输出
        return $this->fetch('index');*/
    }
}
