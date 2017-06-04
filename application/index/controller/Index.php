<?php
namespace app\index\controller;

use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;
use think\Config;
use think\Db;
use think\Request;
use PHP;

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
        $post = $_POST;
        $code = Request::instance()->param('code');
        $appid = Config::get('appid');
        $secret = Config::get('secret');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $res = file_get_contents($url);
        $res_arr = (array)json_decode($res);
        $openid =$res_arr['openid'];
        //查询数据库是否有此记录(异步，redis)
        $post['openid'] = $openid;
       $find = Db::table('user')->where('openid', $openid)->column('openid');
        if(empty($find)){
            unset($post['code']);
            Db::table('user')->insert($post);
        }else{
            //更新
        }
        return $res;
    }


    public function uploadFace()
    {
        $appid = 'wx4f4bc4dec97d474b';
         $group_id = 'test_group_id';
        $encryptedData = Request::instance()->param('encryptedData');
        $iv = Request::instance()->param('iv');

        $pc = new PHP\WXBizDataCrypt();
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        $openid = Request::instance()->param('openid');
        $nickName = Request::instance()->param('nickname');
        header('Content-type: application/json');
        $uploads_dir = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir);
        }

        if ($_FILES ["file"]["error"] == 0) {
            $tmp_name = $_FILES ["file"]["tmp_name"];
            $name = time() . $_FILES ["file"]["name"];
            if(move_uploaded_file($tmp_name, "$uploads_dir/$name")){
                //查看个体是否存在
                $youtu_group_id = Db::table('face')->where('person_id', $openid)->value('youtu_group_id');
                //存在，收费，删除现有
                if(!empty($youtu_group_id)){
                    $youtu_group_id = explode(',', $youtu_group_id);
                    $youtu_group_id = in_array($group_id, $youtu_group_id) ? $youtu_group_id : array_push($youtu_group_id,$group_id);
                    $re_del = YouTu::delperson($openid);
                    //删除成功
                    if($re_del['errorcode'] == 0){
                        //搜索topface
                        $res = YouTu::faceidentifyurl($this->base_url.$name, $group_id);
                        $candidates = $res['candidates'];
                        foreach($candidates as $k=>$v){
                            $candidates[$k]['tag'] = json_decode($v['tag']);
                        }
                        $res['candidates'] = $candidates;
                        $res['shareData'] = $data;
                        //新增优图个体
                        $newperson_re = YouTu::newpersonurl($this->base_url.$name, $openid, $youtu_group_id,$nickName, json_encode(['img'=>$this->base_url.$name, 'nickname' => $nickName]));
                        if($newperson_re['errorcode'] == 0){
                            //更新数据库face_id
                            Db::table('face')->where('person_id', $openid)->update(['face_id' => $newperson_re['face_id'], 'youtu_group_id' => implode(',', $youtu_group_id)]);
                            return json($res);
                        }else{
                            return json($newperson_re);
                        }

                    }else{
                        //删除失败
                        return json(['errorcode'=> -1,'errormsg' => '个体删除失败']);
                    }
                }else{
                    //不存在
                    //新增优图个体
                    $newperson_re = YouTu::newpersonurl($this->base_url.$name, $openid, [$group_id],$openid, $this->base_url.$name);
                    if($newperson_re['errorcode'] ==0){
                        //插入数据库
                        Db::table('face')->data(
                            [
                                'face_id' => $newperson_re['face_id'],
                                'person_id' => $openid,
                                'user_id' => $openid,
                                'nickName'=>$nickName,
                                'img_url' => $this->base_url.$name,
                                'we_group_id' => $group_id,
                                'youtu_group_id' => $group_id,
                                'appid' => '',
                            ]
                        )->insert();
                    }else{
                        return json(['errorcode'=> -6,'errormsg' => '人脸识别失败', 'info'=>$newperson_re]);
                    }

                }

            }else{
                return json(['errorcode'=> -2, 'errormsg'=>'文件上传失败']);
            }

        }else{
            return json(['errorcode'=> -3, 'errormsg'=>'文件上传失败']);
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
