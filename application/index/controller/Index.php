<?php
namespace app\index\controller;

use TencentYoutuyun\Conf;

class Index
{

    public function __construct()
    {
        $appid = '10008634';
        $secretId = 'AKIDBE9sQsEbPxxyvgIsU32xmZCtVuDkn33v';
        $secretKey = '542QiuwmU6WkYIsHanroaGyX2SjokVEv';
        $userid = '595549109';
        $end_point = 'http://api.youtu.qq.com/';
        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, $end_point);
    }

    public function index()
    {
        return 1;
    }

    public function addFace()
    {
        $uploads_dir = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir);
        }

        if ($_FILES ["file"]["error"] == 0) {
            $tmp_name = $_FILES ["file"]["tmp_name"];
            $name = time() . $_FILES ["file"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
        }
        return json(['filename' => $name, 'size'=> $_FILES ["file"]['size']]);
    }
}
