<?php 

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;

// 有图
class Youtumodel {

	/**
	 *	appid
	 *	bucket
	 *	SecretID
	 *	SecretKey
	 *	validity
	 *	file
	 * { function_description }
	 */
	function sign($arr){
		
		// 生成10位随机数
		$num = $this->generateId(10);
		$s = [
		"a" =>$arr['appid'] ,
		"b" =>$arr['bucket'],
		"k" =>$arr['SecretID'],
		"e" => time()+86400,
		"t" => time(),
		"r" => $num,
		"u" => "0",
		"f" =>$arr['file'],
		];
		// 签名字符串
		$orignal = "a={$s['a']}&b={$s['b']}&k={$s['k']}&e={$s['e']}&t={$s['t']}&r={$s['r']}&u={$s['u']}&f={$s['f']}";
		$signTmp = hash_hmac( 'SHA1', $orignal, $arr['SecretKey'], true );
		$sign = base64_encode( $signTmp . $orignal );
		return $sign;
	}


	/**
	 * 图片上传到空间
	 * [update description]
	 * @return [type] [description]
	 */
	function update($url,$imageFilename=null){
		$key = $this->sign(['appid'=>'10071180','bucket'=>'youtu','SecretID'=>'AKIDsAORSK6UkpjR2RmsoZAdw65ucAdvr6uX','SecretKey'=>'jMrAJH4fACAxd8RqAhMPK3FOL7meNveI','file'=>$imageFilename]);
		$md5 = md5( $url );
		$resp = Utils::Req(
			"POST",
			"http://web.image.myqcloud.com/photos/v2/"."10071180/"."youtu/0/",
			[
				"type" => "media",
				"datatype"=>"json",
				"header" => [
					"Authorization:$key",
				],
				"data" =>[
	            "MagicContext" => "text",
			    "__files" => [
			            	[
			            		"mimetype"=>"image/jpeg", 
			            		"name"=>"FileContent", 
			            		"filename"=>$imageFilename,
			            		"data" => $url,
			            	]
			            ],
	                "Md5" => $md5
	        	]
	   		]

		);
		return $resp;
	}

	/**
	 * 拍照
	 * @return [type] [description]
	 */
	function ocr($url){
		$key = $this->sign(['appid'=>'10071180','bucket'=>'youtu','SecretID'=>'AKIDsAORSK6UkpjR2RmsoZAdw65ucAdvr6uX','SecretKey'=>'jMrAJH4fACAxd8RqAhMPK3FOL7meNveI']);
		//发送图片信息返回图片信息
		$resp = Utils::Req(
			"POST",
			"http://service.image.myqcloud.com/ocr/namecard",
			[
				"type" => "json",
				"datatype"=>"json",
				"header" => [
				"Authorization: $key",
				],
				"data" =>[
					"appid" => "10071180",
					"bucket" => "youtu",
					"ret_image"=>0,
					"url_list" => [
						$url
					]
				]
			]
		);

		return $resp;

	}
	
	/**
	 *随机数
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