<?php 

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;


// 短信发送
class SmsModel {


	/**
	 * 初始化
	 * @param array $param [description]
	 */
	function __construct( $opt=[] ) {
		$this->AppID = isset($opt['AppID']) ? $opt['AppID'] : "1400017564";
		$this->AppKey = isset($opt['AppKey']) ? $opt['AppKey'] : "2b9f1e3ef8e81ebb5cf4f2b9d1433fe0";
	}


	function sign( $mobile ) {

		return md5( "{$this->AppKey}{$mobile}");

	}

	/**
	 * 发送短信文本
	 * @param  [type] $mobile  [description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	function send( $mobile, $content ){
		$sig=$this->sign($mobile);
		$resp = Utils::Req(
		     "POST", 
		     "https://yun.tim.qq.com/v3/tlssmssvr/sendsms",
		     [
		         "type" => "json",
		         "datatype"=>"json",
		         "header" => [
		         	"Content-Type: application/json"
		         ],
		         "query" => [
		         	"random"=> rand(100000,999999),
		         	"sdkappid" => $this->AppID 
		         ],
		         "data" =>[

		            "tel" => [
		                 "nationcode" => "86",
		                 "phone" => "$mobile"
		            ],
		            "type" => "0",
		            "sign" => "云课堂测试",
		           	"msg" => $content,
		            "sig" => $sig,
		            "extend" => "",
		            "ext" => ""
		         ]
		     ]
		 );
		$resp['tel'] = $mobile;
		return $resp;
	}
	


	/**
	 * 发送语音短信
	 * @param  [type] $mobile  [description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	function sendvoice( $mobile, $content ){
		$sig=$this->sign($mobile);
		$resp = Utils::Req(
			"POST",
			"https://yun.tim.qq.com/v3/tlsvoicesvr/sendvoiceprompt",
			[
				"type" => "json",
				"datatype"=>"json",
				"query" => [
					"sdkappid" => $this->AppID ,
					"random" => rand(1,999999999)
				],
				"data" =>[
					"tel" => [
					"nationcode" => "86",
					"phone" =>$mobile,
					],
					"prompttype" => "2",
					"promptfile"=> $content,
					"sig" => $sig,
					"ext" => ""
				]
			]
		);

		$resp['tel'] = $mobile;
		return $resp;
	}


	/**
	 * 发送邮件
	 * @param  [type] $arr [description]
	 * @return [type]      [description]
	 */
	function sendemail($arr){
			/**数据参数缺一不可
			 * user发送人
			 * content 发送内容
			 * guest 接收人
			 * host host地址
			 * passwd passwd发送人密码
			 * title 题目
			 */
			$message = Utils::MailMessage($arr['title'])
					  	/**
			 			 * 发件人账号=>发件人名
			 			 */
	                    ->setFrom([$arr['user']=>'测试程序'])
					    ->addPart($arr['content'],'text/html') // 正文 HTML
					   	->setTo([$arr['guest']=>"www"]);//发送
	                    /**
	                     * host地址
	                     * 发件人账号
	                     * 密码
	                     * ssl 设置(smtp.exmail.qq.com)
	                     */
			$mailer = Utils::Mailer(['host'=>$arr['host'],
									 'user'=>$arr['user'], 
									 'pass'=>$arr['passwd'],
									 'ssl'=>true]);
			$mailer->send($message);
	}

	/**
	 *生成id随机数
	 * @return [type] [description]
	 */
	function generateId( $length ) {
    	// 随机数字符集，可任意添加你需要的字符
    	$chars = '1234567890';
	    $num = '';
	    for ( $i = 0; $i < $length; $i++ ) 
	    {
	      
	        $num .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	    }

	    return date(time()).$num;
	}




}


 ?>