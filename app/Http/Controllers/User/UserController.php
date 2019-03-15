<?php
namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class UserController extends  Controller{
    protected $redis_hu_key='api:h:u:';  //个人信息hash
    /*
     * 登录
     */
    public function login(Request $request){
        //接收
        $user_name=$request->input('uid');
        $pwd=$request->input('p');
        //验证用户信息
        if(1){
            $uid=$request->input('uid');
            $str=time()+$uid+mt_rand(11111,99999);
            $token=substr(md5($str),10,20);
            //保存token到Reids
            $key=$this->redis_hu_key.$uid;
            Redis::hSet($key,'token',$token);
            Redis::expire($key,3600*24*7);     // 过期时间一周
            echo $token;
            $_SERVER['HTTP_TOKEN']=$token;
        }else{

        }
    }
    /*
     * 个人中心
     */
    public function Center(Request $request){
        $uid=$request->input('uid');
//        var_dump($_SERVER);die;
        if(!empty($_SERVER['HTTP_TOKEN'])){
            $response=[
                'errno' => 50000,
                'msg'   => 'Token Require!'
            ];
        }else{
            //验证token有效是否过期或伪造
            $key=$this->redis_hu_key.$uid;
            $token=Redis::hGet($key,'token');
            $_SERVER['HTTP_TOKEN']=$token;
//            var_dump($_SERVER);die;
            if($token==$_SERVER['HTTP_TOKEN']){
                $response=[
                    'errno' =>0,
                    'msg'   => 'ok',
                    'data'  =>[
                        'name'   => '联通',
                        'age'    => 12
                    ]
                ];
            }else{
                $response=[
                    'errno' =>50001,
                    'msg'   =>'Invalid Token!!'
                ];
            }
        }
        return $response;
    }
    /*
     * 防止刷次数
     */
    public function fangshua(){
        $request_url=$_SERVER['REQUEST_URI'];   //访问方法
        $hash=substr(md5($request_url),0,10);   //加密
//        var_dump($_SERVER);//打印，找到信息
        $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];   //客户地址ip
        $redis_key='str:'.$hash.':'.$REMOTE_ADDR;
        $number=Redis::incr($redis_key);    //若值不为空,incr命令会解释为十进制64位有符号整数
        Redis::expire($redis_key,60);       //时间限制  设置过期时间
        if($number>5){     //若刷新次数超过5次,则视为非法请求
            $response=[
                'errno' => 4003,
                'msg'   => 'Invalid Request!!',
            ];
            Redis::expire($redis_key,10);       //限制时间，并将存入Redis
            $fei_request_ip='s:Invalid:ip:';
            Redis::sAdd($fei_request_ip,$REMOTE_ADDR);  //将一个或多个元素加入集合中
        }else{
            $response=[
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => [
                    'name'  => '嘤嘤嘤',
                    'age'   => 123,
                ]
            ];
        }
        return $response;
    }
}
?>