<?php
/**
 * Created by PhpStorm.
 * User: Rafi
 * Date: 2018/8/11
 * Time: 0:02
 */

namespace app\index\controller;


use think\Controller;
use rafi\Sms as SmsExtend;

class Sms extends Controller
{
	public function http_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	public function sendSms($phone,$code)
    {	
    	$content="【惠众钱站】您本次验证码为$code,十分钟内有效。";
    	$url='http://sms.37037.com/sms.aspx';
    	$data="action=send&userid=8147&account=wodetian&&password=50818291&mobile=$phone&content=$content&sendTime=&checkcontent=1";
        //$data="action=send&userid=8783&account=".urlencode('南瓜袋')."&password=123456&mobile=$phone&content=$content&sendTime=&checkcontent=1";
    	$this->http_request($url,$data);
    	return 'ok';
    }    
	
	
    public function send(){
        $mobile = input('mobile');
        $captcha = input('picVerify');
        $result = $this->validate(['mobile'=>$mobile],'User.mobile');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        if (!captcha_check($captcha, '', config('captcha'))){
            $this->error('图片验证码错误，请重新输入');
        }
        $verify_code = rand(1000,9999);
        cache('verify_'.$mobile,$verify_code,60*5);
        $data = [
            'accessKeyId' => config('access_key_id'),                 // your appid
            'accessKeySecret' => config('access_key_secret'),         // your app_secret
            'signName'    => config('sign_name'),                    // your 签名
            'templateCode' => config('template_code')         // your 模板编号
        ];
        /*$sms = new SmsExtend($data);
        $res = $sms->sendSms($mobile,$verify_code);
        if ($res->Code === 'OK' && $res->Message === 'OK'){
            $this->success('验证码已发送');
        }
        $this->error($res->Code);
		*/
		$this->sendSms($mobile,$verify_code);
		$this->success('验证码已发送');
    }

    public function forget(){
        $mobile = input('mobile');
        $captcha = input('picVerify');
        if (!captcha_check($captcha, '', config('captcha'))){
            $this->error('图片验证码错误，请重新输入');
        }
        $verify_code = rand(1000,9999);
        cache('verify_'.$mobile,$verify_code,60*5);
        $data = [
            'accessKeyId' => config('access_key_id'),                 // your appid
            'accessKeySecret' => config('access_key_secret'),         // your app_secret
            'signName'    => config('sign_name'),                    // your 签名
            'templateCode' => config('template_code')         // your 模板编号
        ];
		$this->sendSms($mobile,$verify_code);
		$this->success('验证码已发送');
        /*$sms = new SmsExtend($data);
        $res = $sms->sendSms($mobile,$verify_code);
        if ($res->Code === 'OK' && $res->Message === 'OK'){
            $this->success('验证码已发送');
        }
        $this->error($res->Code);*/
    }
}