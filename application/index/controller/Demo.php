<?php
namespace app\index\controller;



class Demo extends \think\Controller
{

    protected $base_url = 'https://www.yiyazhe.com/uploads/';

    public function index(){
        $this->assign([
            'name'  => 'ThinkPHP',
            'email' => 'thinkphp@qq.com'
        ]);
        // 模板输出
        return $this->fetch('index');
    }
}
