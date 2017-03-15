<?php 

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;

/**
 * 
 */

class Cmqmodel {

	/**
	 * appid 项目id
	 * Region 地区
	 * SecretID
	 * SecretKey
	 * @param array $opt [description]
	 */
	function __construct( $opt=[] ) {
		$this->Region = isset($opt['Region']) ? $opt['Region'] : "gz";
		$this->SecretId = isset($opt['SecretId']) ? $opt['SecretId'] : "AKIDxoTxGQLIwPhnka5EOOCoFVZS9j8NKbw5";
		$this->SecretKey = isset($opt['SecretKey']) ? $opt['SecretKey'] : "5VfkeLuHSTh8XkmbEfu030lKhsreLxPg";

	}

	/**
	 * 
	 * $params签名的参数
	 * $option服务器参数，详细可以查看文档
	 * @param  [type] $option [description]
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	function sign($params,$option){
		$params['Action'] = isset($params['Action']) ? $params['Action'] :'CreateQueue';
		$params['Timestamp'] = isset($params['Timestamp']) ? $params['Timestamp'] : time();
		$params['Nonce'] = isset($params['Nonce']) ? $params['Nonce'] :  $this->generateNum(6);
		$params['SecretId'] = isset($params['SecretId']) ? $params['SecretId'] : $this->SecretId;
		$params['Region'] = isset($params['Region']) ? $params['Region'] : $this->Region;
		$option['method'] = isset($option['method']) ? $option['method'] : 'GET';
		$option['host'] = isset($option['host']) ? $option['host'] : 'cmq-queue-gz.api.qcloud.com';
		$option['path'] = isset($option['path']) ? $option['path'] : '/v2/index.php';
		
		ksort( $params );
		$params_list = [];
		foreach( $params as $k=>$v ) {
			if (strpos($k, '_')){
     			$k = str_replace('_', '.', $k);
			}
			array_push( $params_list, "$k=$v");
		}

		$srcStr = implode( "&", $params_list);
		$orignal = "{$option['method']}{$option['host']}{$option['path']}?$srcStr";

		$params['Signature'] = urlencode(base64_encode(hash_hmac('sha1',$orignal,$this->SecretKey,true)));

		ksort( $params );
		return  $params['Signature'];
	}
	

	

	



	/**
	 * 创建
	 * @param  [type] $contents [description]
	 * @return [type]           [description]
	 */
	function create( $name, $option){
		// 生成随机数
		$num = $this->generateNum(6);
		// 创建请求参数
		$params=[
			'Timestamp' => time(),
			'queueName' =>$name,
			'Action' => 'CreateQueue',
			'Nonce' =>$num,
			'Region' => 'gz',
			'SecretId' =>$this->SecretId,
		];

		$signStr = $this->sign($params);

		
		//提交请求
		$resp = Utils::Req(
			"GET",
			 "https://cmq-queue-gz.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr] )
			]
		);

		return $resp;
	}

	/**
	 * 发送信息
	 * @param  [type] $queue   [队列名称]
	 * @param  [type] $message [信息]
	 * @return [type]          [description]
	 */
	function sendMessage($queue,$message){
		// 生成随机数
		$num = $this->generateNum(6);
		// 创建请求参数
		$params=[
			'Timestamp' => time(),
			'Action' => 'SendMessage',
			'Nonce' =>$num,
			'Region' => 'gz',
			'SecretId' =>$this->SecretId,
			'queueName'=>$queue,
			'msgBody'=>$message
		];
		// ['queueName'=>$queue,'msgBody'=>$message]
		$signStr = $this->sign($params);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cmq-queue-gz.api.qcloud.com/v2/index.php",
			[	
				"datatype"=>"json",
				"query" => array_merge($params,["Signature"=>$signStr])
			]
		);
		// 返回值中加入queuename
		return $resp;
	}

	/**
	 * 消费信息
	 * @param  [type] $queue [队列名字]
	 * @return [type]        [description]
	 */
	function receiveMessage( $queue ) {
		$num = $this->generateNum(6);
		// 创建请求参数
		$params=[
			'Timestamp' => time(),
			'Action' => 'ReceiveMessage',
			'Nonce' =>$num,
			'Region' => 'gz',
			'SecretId' =>$this->SecretId,
			'queueName'=>$queue
		];
		// ['queueName'=>$queue,'msgBody'=>$message]
		$signStr = $this->sign($params);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cmq-queue-gz.api.qcloud.com/v2/index.php",
			[	
				"datatype"=>"json",
				"query" => array_merge($params,["Signature"=>$signStr])
			]
		);

		// 返回值中加入queuename
		return $resp;
	}

	/**
	 * 删除信息
	 * @param  [type] $queue         [队列名字]
	 * @param  [type] $receiptHandle [消息id]
	 * @return [type]                [description]
	 */
	function removeMessage($queue,$receiptHandle ){
		// 318219979604694
		// gouqeq
		$num = $this->generateNum(6);
		// 创建请求参数
		$params=[
			'Timestamp' => time(),
			'Action' => 'DeleteMessage',
			'Nonce' =>$num,
			'Region' => 'gz',
			'SecretId' =>$this->SecretId,
			'queueName'=>$queue,
			'receiptHandle'=>$receiptHandle
		];
		$signStr = $this->sign($params);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cmq-queue-gz.api.qcloud.com/v2/index.php",
			[	
				"datatype"=>"json",
				"query" => array_merge($params,["Signature"=>$signStr])
			]
		);

		// 返回值中加入queuename
		return $resp;
	}

	/**
	 *生成num随机数
	 * @return [type] [description]
	 */
	function generateNum( $length ) 
	{
	   	// 随机数字符集，可任意添加你需要的字符
    	$chars = '1234567890';
	    $num = '';
	    for ( $i = 0; $i < $length; $i++ ) 
	    {
	      
	        $num .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	    }

	    return $num;
	}

	/**
	 * 对象转数组
	 * @param  [type] $obj [description]
	 * @return [type]      [description]
	 */
	function objarray_to_array($obj) {  
	     $ret = array();  
	     foreach ($obj as $key => $value) {  
	 	    if (gettype($value) == "array" || gettype($value) == "object"){  
	 	            $ret[$key] =  objarray_to_array($value);  
	 	    }else{  
	 	        $ret[$key] = $value;  
	 	    }  
	     }  
	     return $ret;  
	} 

}

 ?>