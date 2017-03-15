<?php 
require __DIR__."/../vendor/autoload.php";
use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;
use SSHClient\ClientConfiguration\ClientConfiguration;
use SSHClient\ClientBuilder\ClientBuilder;

/**
 * @所有方法只考虑按需计费
 * @接口调用方式
 */
class ScalingModel extends Model {
	/**
	 * appid 项目id
	 * Region 地区
	 * SecretID
	 * SecretKey
	 * @param array $opt [description]
	 */
	function __construct( $opt=[] ) {
		parent::__construct();
		$this->table('scaling');
		$this->Region = isset($opt['Region']) ? $opt['Region'] : "gz";
		$this->SecretId = isset($opt['SecretId']) ? $opt['SecretId'] : "AKIDxoTxGQLIwPhnka5EOOCoFVZS9j8NKbw5";
		$this->SecretKey = isset($opt['SecretKey']) ? $opt['SecretKey'] : "5VfkeLuHSTh8XkmbEfu030lKhsreLxPg";
	}

	// 数据库创建
	function __schema() {
		$this->putColumn( 'condition', $this->type('string', ['length'=>40]) )
			->putColumn( 'ncident', $this->type('string',['length'=>200]) )
			->putColumn('fulltext', $this->type('string',['length'=>200]) )
			->putColumn('data', $this->type('string',['length'=>400]) )
			->putColumn('key', $this->type('string',['length'=>1000]) )
			;
	}



	/**
	 * 添加数据库
	 * @return [type] [description]
	 */
	function create($data){

		return parent::create($data);
	}


	/**
	 * 清除数据库
	 * @return [type] [description]
	 */
	function __clear() {

		$this->dropTable();	
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
		$params['Action'] = isset($params['Action']) ? $params['Action'] :'DescribeInstances';
		$params['Timestamp'] = isset($params['Timestamp']) ? $params['Timestamp'] : time();
		$params['Nonce'] = isset($params['Nonce']) ? $params['Nonce'] :  $this->generateNum(6);
		$params['SecretId'] = isset($params['SecretId']) ? $params['SecretId'] : $this->SecretId;
		$params['Region'] = isset($params['Region']) ? $params['Region'] : $this->Region;
		$option['method'] = isset($option['method']) ? $option['method'] : 'GET';
		$option['host'] = isset($option['host']) ? $option['host'] : 'cvm.api.qcloud.com';
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
	 * 添加云主机
	 */
	function addCvm(){
		// 生成随机数
		$num = $this->generateNum(6);
		// 创建请求参数
		/**
		 * cpu核心数
		 * none随机数
		 * Timestamp当前时间
		 * none随机数
		 * SecretId
		 * men内存
		 * imageId指定镜像
		 * storageType磁盘类型
		 * storageSize数据盘
		 * zoneId可用区ID
		 */
		$params=[
			'Timestamp' => time(),
			'Action' =>'RunInstancesHour',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'zoneId'=>'100002',
			'cpu'=>'1',
			'mem'=>'2',
			'imageType'=>'2',
			'storageSize'=>'20',
			'imageId'=>'img-aa9z7opt'
		];
		$signStr = $this->sign($params);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cvm.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);
		return $resp;
	}

	/**
	 * 退还云主机
	 * @param  [type] $inst [description]
	 * @return [type]       [description]
	 */
	function rmCvm( $inst ){
		// 查询服务器信息
		$data = $this->seek($inst);
		// 通过描述接口过滤出id
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'ReturnInstance',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'instanceId'=>$data['instanceSet']['0']['unInstanceId']
		];
		$signStr = $this->sign($params);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cvm.api.qcloud.com/v2/index.php",
			[	
				"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);
		return $resp;
	} 




	/**
	 * 创建数据库实例
	 */
	function addCDB($data){
		// 生成随机数
		$num = $this->generateNum(6);
		// 创建请求参数
		// cdbType:自定义规格CUSTOM
		// engineVersion:mysql版本(默认5.5)
		// goodsNum实例数量(默认1)
		// memory内存大小
		// volume硬盘大小
		// 内存大小硬盘大小设置必须要和文档一致
		$data = [
			'cdbType'=>$data['cdbType']?$data['cdbType']:"custom",
			'engineVersion'=>$data['engineVersion']?$data['engineVersion']:"5.5",
			'goodsNum'=>$data['goodsNum']?$data['goodsNum']:"1",
			'memory'=>$data['memory']?$data['memory']:"1000",
			'volume'=>$data['volume']?$data['volume']:"25"
		];
		$params=[
			'Timestamp' => time(),
			'Action' =>'CreateCdbHour',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'cdbType'=>$data['cdbType'],
			'engineVersion'=>$data['engineVersion'],
			'goodsNum'=>$data['goodsNum'],
			'memory'=>$data['memory'],
			'volume'=>$data['volume']
		];
		$option = ['host'=>'cdb.api.qcloud.com'];
		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cdb.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);

		return $resp;
	} 




	/**
	 * 创建数据库实例
	 */ 
	function setConn($host){
		// 生成随机数
		$num = $this->generateNum(6);
		// 创建请求参数
		/**
		 * cdbType:自定义规格CUSTOM
		 * engineVersion:mysql版本
		 * goodsNum实例数量
		 * memory内存大小
		 * volume硬盘大小
		 * memory和volume必须根据手动创建模块填写
		 */
		 $params=[
		 	'Timestamp' => time(),
		 	'Action' =>'UpgradeCdb',
		 	'Nonce' =>$num,
		 	'Region' =>'gz',
		 	'SecretId' =>$this->SecretId,
		 	'cdbInstanceId'=>'cdb-fy91q3vg',
		 	'memory'=>'2000',
		 	'volume'=>'35'
		 ];
		 $option = ['host'=>'cdb.api.qcloud.com'];
		 $signStr = $this->sign($params,$option);
		 //提交请求
		 $resp = Utils::Req(
		 	"GET",
		 	"https://cdb.api.qcloud.com/v2/index.php",
		 	[	
		 		//"debug"=>true,
		 		"datatype"=>"json",
		 		"query" => array_merge( $params,["Signature"=>$signStr])
	 		]
		 );

		return $resp;
	} 

	/**
	 * 查询所有的mysql主机
	 *  
	 */
	function sertotal($ip){
		// 查询全部的mysql云主机
		$data = $this->search([
					'method'=>'DescribeCdbInstances',
					'api'=>'cdb.api.qcloud.com'
			   ]);
		// 检测缓存是否存在
		$mem = new Mem;
		// 如果缓存为空存入当前值
		$mem->set($ip,$ip,'3600');
		// 如果不为空
		$res = $this->testcache($ip);
	}


	/**
	 * 检测缓存（检测有没有mysql警告的缓存）
	 * 不同的情况给予对应的处理方式
	 * @return [type] [description]
	 */
	function testcache($id){
		// 查询全部的mysql云主机
		$data = $this->search([
				'method'=>'DescribeCdbInstances',
				'api'=>'cdb.api.qcloud.com'
		   ]);
		// 定义空数组
		$mem = new Mem;
		// 警告的id数组
		$arrmall  = array();
		// 全部的id数组
		$keyid = array();
 		// 全部的mysql主机化为数组(是否发出警告)
		foreach ($data["cdbInstanceSet"] as $key => $value){
			// 把全部mysql实例的id放进数组
			$keyid[] =  $value['cdbInstanceName'];
			$testmes = $mem->get( $value['cdbInstanceName'] );
			if ($testmes!=false) {
				$testmes=true;
			}
			$testmes=true;
			$arrmall[$value['cdbInstanceName']]=$testmes;
		}


		// 根据数组判断是创建还是更改
		$res =  in_array(false,array_values($arrnall));
		if($res==false) {
			// 执行创建
			//$this->addCDB();
			// 因为所有的mysql实例全部预警（会留下缓存）,没有缓存的便是新创件的实例
			$arr = ['cdb125713','cdb127136','cdb128000','cdb129813'];
			// 找到不同
			$diffkey= implode('',array_diff($keyid,$arr));
			// 初始化
			$this->sqlinitial(['cdbInstanceId'=>$diffkey]);

		}else{
		

		}
	
	}
	
	/**
	 * mysql初始化
	 * @return [type] [description]
	 */
	function sqlinitial($data){
		//cdbInstanceId 实例id
		//charset 字符集
		//port 端口
		//lowerCaseTableNames 大写写敏感
		//password密码 默认（tuanduimao123）
		$data = [
			'cdbInstanceId'=>$data['cdbInstanceId']?$data['cdbInstanceId']:"",
			'charset'=>$data['charset']?$data['charset']:"utf8",
			'port'=>$data['port']?$data['port']:"3306",
			'lowerCaseTableNames'=>$data['lowerCaseTableNames']?$data['lowerCaseTableNames']:"0",
			'password'=>$data['password']?$data['password']:"tuanduimao123",
		];
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'CdbMysqlInit',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'cdbInstanceId'=>$data['cdbInstanceId'],
			'charset'=>$data['charset'],
			'port'=>$data['port'],
			'lowerCaseTableNames'=>$data['lowerCaseTableNames'],
			'password'=>$data['password']
		];
		// 签名host地址
		$option  = ['host'=>'cdb.api.qcloud.com'];
 		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cdb.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);
		
		// 调用查询类查看初始化状态(获取初始化状态id)
		$test = $this->search(['method'=>'GetCdbInitInfo','parameter'=>'jobId','api'=>'GetCdbInitInfo'],$resp['jobId']);

		echo "<pre>";
		var_dump($test);


	}

























	/**
	 * 查询(可以查询出id)
	 * @return [type] [description]
	 */
	function search($data,$id=null){
		// method查询方法
		// parameter查询参数name（查询现在的参数为ip）	
		// api查询接口的api
		$data =[
					'method'=>$data['method']?$data['method']:"",
					'parameter'=>$data['parameter']?$data['parameter']:"",
					'api'=>$data['api']?$data['api']:""
			   ];
		// 生成随机数
		$num = $this->generateNum(6);

		//必传参数
		$key =[
			'Timestamp' => time(),
			'Action' =>$data['method'],
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId
		];
		//判断选传的参数是否存在
		//存在合并数组
		//
		//
	
		if (!empty($data['parameter'])&&!empty($data['id'])) {
			// 选传参数
			$select = [
				$data['parameter']=>$id
			];	
			// 合并数组
			$params = array_merge($key,$select);
			
		}else{
			// 不存在附值
			$params = $key;
		}

		echo "<pre>";
		var_dump($params);
		echo "</pre>";
		// die();
		// // 签名host地址
		// $option  = ['host'=>$data['api']];
		// $signStr = $this->sign($params,$option);
		// //提交请求
		// $resp = Utils::Req(
		// 	"GET",
		// 	"https://".$data['api']."/v2/index.php",
		// 	[	
		// 		//"debug"=>true,
		// 		"datatype"=>"json",
		// 		"query" => array_merge( $params, ["Signature"=>$signStr])
		// 	]
		// );

		// return $resp;
	}















































	/**
	 * 调整宽带
	 */
	function Broadjoin($id,$data){
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'UpdateInstanceBandwidthHour',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'instanceId'=>$id,
			'bandwidth'=>$data+2
		];
		// 签名host地址
		$option  = ['host'=>'cvm.api.qcloud.com'];
 		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cvm.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);

		return $resp;
	}

	/**
	 *  调整负载均衡权重
	 * @param [type] $inst [description]
	 */
	function setLB($loadBalancerId,$unInstanceId,$id){
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'ModifyLoadBalancerBackends',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'loadBalancerId'=>$loadBalancerId,
			'backends.1.instanceId'=>$unInstanceId,
			'backends.1.weight'=>$id
		];
		// 签名host地址
		$option  = ['host'=>'lb.api.qcloud.com'];
 		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://lb.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);

		return $resp;

	}

	/**
	 * 清除日志
	 * @param  [type] $inst [description]
	 * @return [type]       [description]
	 */
	function clearLog( $inst ){
	    // 定义删除路径
		$arr =['/logs/php','/logs/nginx'];
		foreach ($arr as $key => $url) {
			$urlname = scandir($url);
			foreach ($urlname as $num => $filetime) {
				$this->filetime($url."/".$filetime);
			}
		}
		
	}
	
	/**
	 *随机数
	 * @return [type] [description]
	 */
	function generateNum( $length ) {
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
	 * 获取负载均衡绑定的后端服务器列表(获取权重)
	 */
	function searchLB($id){
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'DescribeLoadBalancerBackends',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'loadBalancerId'=>$id
		];
		// 签名host地址
		$option  = ['host'=>'lb.api.qcloud.com'];
 		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://lb.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);
		// 负债均衡权重
		// 外网ip
		return array('weight' =>$resp['backendSet']['0']['weight'],
				'wanIpSet'=>$resp['backendSet']['0']['wanIpSet']['0']
			);
	}

	/**
	 * 查询服务器id
	 * @return [type] [description]
	 */
	function dataser($id){
		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
			'Timestamp' => time(),
			'Action' =>'DescribeInstances',
			'Nonce' =>$num,
			'Region' =>'gz',
			'SecretId' =>$this->SecretId,
			'lanIps.0'=>$id
		];
		// 签名host地址
		$option  = ['host'=>'cvm.api.qcloud.com'];
 		$signStr = $this->sign($params,$option);
		//提交请求
		$resp = Utils::Req(
			"GET",
			"https://cvm.api.qcloud.com/v2/index.php",
			[	
				//"debug"=>true,
				"datatype"=>"json",
				"query" => array_merge( $params, ["Signature"=>$signStr])
			]
		);

		return $resp['instanceSet']['0']['unInstanceId'];
	}

	/**
	 * 获取文件的创建时间
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function filetime($data){
		//判断文件类型
		if (substr(strrchr($data, '.'), 1)=='log'){
			//判断创建时间大于6个月前的时间执行删除
			if (date("Y-m-d H:i:s",filectime($data))>=date("Y-m-d H:i:s", time()-2592000*6)){
				unlink($data);
			}
		}
	}






	/**
	 * 查询数据库宕机
	 * @return [type] [description]
	 */
	function sermysql(){

		// 生成随机数
		$num = $this->generateNum(6);
		$params=[
		 	'Timestamp' => time(),
		 	'Action' =>'DescribeCdbInstances',
		 	'Nonce' =>$num,
		 	'Region' =>'gz',
		 	'SecretId' =>$this->SecretId,
		 ];
		 $option = ['host'=>'cdb.api.qcloud.com'];
		 $signStr = $this->sign($params,$option);
		 //提交请求
		 $resp = Utils::Req(
		 	"GET",
		 	"https://cdb.api.qcloud.com/v2/index.php",
		 	[	
		 		//"debug"=>true,
		 		"datatype"=>"json",
		 		"query" => array_merge( $params,["Signature"=>$signStr])
	 		]
		);
	}

	/**
	 * 发送邮件
	 * @return [type] [description]
	 */
	function mail(){





	}


}
?>