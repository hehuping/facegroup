<?php
namespace app\index\controller;

use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;
use think\Config;
use think\Db;
use think\Request;

class Index
{

    protected $base_url = 'https://www.yiyazhe.com/uploads/';

    public function __construct()
    {
        $appid = '10008634';
        $secretId = 'AKIDBE9sQsEbPxxyvgIsU32xmZCtVuDkn33v';
        $secretKey = '542QiuwmU6WkYIsHanroaGyX2SjokVEv';
        $userid = '595549109';
        $end_point = 'http://api.youtu.qq.com/';
        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, $end_point);
    }

    public function getsession()
    {
        $code = Request::instance()->param('code');
        $appid = Config::get('appid');
        $secret = Config::get('secret');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $res = file_get_contents($url);
        session('user', json_decode($res));
        return $res;
    }


    public function uploadFace()
    {
         $group_id = 'test_group_id';
        $person_id = 'test_person_id'.rand(1,10000);
        header('Content-type: application/json');
        $uploads_dir = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir);
        }

        if ($_FILES ["file"]["error"] == 0) {
            $tmp_name = $_FILES ["file"]["tmp_name"];
            $name = time() . $_FILES ["file"]["name"];
            if(move_uploaded_file($tmp_name, "$uploads_dir/$name")){
                //搜索topface
                $res = YouTu::faceidentifyurl($this->base_url.$name, $group_id);
                //新增优图个体
                $re = YouTu::newpersonurl($this->base_url.$name, $person_id, [$group_id],$person_id, $this->base_url.$name);
                //插入数据库
                Db::table('face')->data(
                    [
                        'face_id' => $re['face_id'],
                        'user_id' => $person_id,
                        'nickName'=>$person_id,
                        'img_url' => $this->base_url.$name,
                        'we_group_id' => $group_id,
                        'youtu_group_id' => $group_id,
                        'appid' => '',
                    ]
                )->insert();

                return json($res);

            }else{
                return json(['error'=> -1]);
            }

        }else{
            return json(['error'=> -2]);
        }

    }

    protected function addToPerson($filename, $groupid){
        //新增个体
       return  YouTu::newperson($this->base_url.$filename, 'test_id', ['tencent'],'test_name', '');
    }

    protected function Top5Face($path){
        YouTu::faceidentify($this->base_url.$path);
    }

    //获取组
    public function getGroup(){
        $group_id = YouTu::getgroupids();
        return json($group_id);
    }

    //获取人列表
    public function getPerson($id){
        $id = $id ? $id : $_GET['id'];
        return json(YouTu::getpersonids($id));
    }

    public function getFace($id){
        $id = $id ? $id : $_GET['id'];
        return json(YouTu::getfaceids($id));
    }

    public function delPerson(){
        $group_id = YouTu::getgroupids()['group_ids'];
        foreach($group_id as $v){
            $person = YouTu::getpersonids($v)['person_ids'];
            foreach($person as $v2){
                print_r(YouTu::delperson($v2));
            }
        }

    }
}
