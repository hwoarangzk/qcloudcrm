<?php 

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;

// 云搜
class YunsouModel {

		/**
		 * appid 项目id
		 * Region 地区
		 * SecretID
		 * SecretKey
		 * @param array $opt [description]
		 */
		function __construct( $opt=[] ) {
			$this->appId = isset($opt['appId']) ? $opt['appId'] : "14950002";
			$this->Region = isset($opt['Region']) ? $opt['Region'] : "gz";
			$this->SecretId = isset($opt['SecretId']) ? $opt['SecretId'] :
			"AKIDcEi3fI86MQNAlEHrxxpcFnHclIpD3fll";
			$this->SecretKey = isset($opt['SecretKey']) ? $opt['SecretKey']
			: "0zscSBoGdty5BARI7veyb4teEx3992oT";

		}

		/**
		 * 
		 * method 请求方式
		 * Timestamp 时间戳
		 * Nonce  随机数
		 * SecretId
		 * @param  [type] $option [description]
		 * @param  [type] $params [description]
		 * @return [type]         [description]
		 */
		function sign( $option, & $params ) {

			$option['method'] = isset($option['method']) ? $option['method'] :
			'GET';
			$params['Timestamp'] = isset($params['Timestamp']) ? $params['Times
			tamp'] : time();
			$params['Nonce'] = isset($params['Nonce']) ? $params['Nonce'] : $this->genStr(6);
			$params['SecretId'] = isset($params['SecretId']) ? $params['SecretId'] : $this->SecretId;
			$params['Region'] = isset($params['Region']) ? $params['Region'] : $this->Region;
			$params['appId'] = isset($params['appId']) ? $opt['appId'] : $this->appId;
			
			ksort( $params );

			$params_list = [];
			foreach( $params as $k=>$v ) {
				if (strpos($k, '_'))
	            {
	     
	                $k = str_replace('_', '.', $k);
				}
				array_push( $params_list, "$k=$v");
			}

			$srcStr = implode( "&", $params_list);
			$orignal = "{$option['method']}{$option['host']}{$option['path']}?$srcStr";

			$params['Signature'] = urlencode(base64_encode(hash_hmac('sha1', $orignal, $this->SecretKey,true)));

			ksort( $params );

			return  $params['Signature'];
		}


		/**
		 * 创建
		 * @param  [type] $contents [description]
		 * @return [type]           [description]
		 */
		function create( $contents ){
			
			$api = "https://yunsou.api.qcloud.com/v2/index.php";
			$option = [
				'method' => 'GET',
				'host' => 'yunsou.api.qcloud.com',
				'path' => '/v2/index.php'
			];

			$query = ["Action"=>"DataManipulation", "op_type"=>"add"];

			foreach( $contents as $idx=>$content ){
				foreach ($content as $ck => $cv) {
					$query["contents.{$idx}.{$ck}"] = $cv;
				}
			}

			$signStr = $this->sign( $option, $query);	

			
			$resp = Utils::Req(
				$option["method"],
				$api,
				[
					"datatype"=>"json",
					"query"=>$query,
				]
			);


			return $resp;
		}



		/**
		 * 生成随机数
		 * @param  [type] $length [description]
		 * @return [type]         [description]
		 */
		function genStr($length) {
	    	// 随机数字符集，可任意添加你需要的字符
	    	$chars = '123456789';
		    $num = '';
		    for ( $i = 0; $i < $length; $i++ ) 
		    {
		      
		        $num .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		    }

		    return $num;
		}
	/**
	 * keyword关键词
	 * 
	 * @param  [type]  $keyword [description]
	 * @param  array   $filter  [description]
	 * @param  integer $page    [description]
	 * @param  integer $perpage [description]
	 * @return [type]           [description]
	 */
	function search( $keyword, $filter=[], $page, $perpage ) {
		$api = "https://yunsou.api.qcloud.com/v2/index.php";
			$option = [
			'method' => 'GET',
			'host' => 'yunsou.api.qcloud.com',
			'path' => '/v2/index.php'
			];
			$query = [
				"Action"=>"DataSearch",
				"query_encode"=>"utf8",
				"search_query"=> $keyword,
				"page_id" => intval( $page ) - 1,
				"num_per_page"=>intval( $perpage ),
				"num_filter" => !empty($filter['num']) ? $filter['num'] : "",
				"cl_filter" => !empty($filter['cl']) ? $filter['cl'] : "",
				"extra" => !empty($filter['extra']) ? $filter['extra'] : "",
			];
			$this->sign( $option, $query );
			$resp = Utils::Req(
				$option["method"],
				$api,
				[
				"datatype"=>"json",
				"query"=>$query
				]
			);
			return $resp;
	}


	/**
	 * 字符串转数组
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

	/**
	 * 分页
	 * $page : 每页显示数
	 * 
	 * $all : 总共的数据个数
	 * 
 	 * @return [type] [description]
	 */
	 function page($arr){

	 	$page= isset($arr['page']) ? $arr['page'] : "10";
	  	$all= isset($arr['all']) ? $arr['all'] : "0";
	  	$page_num  = array();  


	  	$pag_sum=ceil($all/$page);
	  	for ($i=1; $i <=$pag_sum ; $i++) { 

	  			$page_num[$i] = $i;
	  	}

	 	return  array('page_sum'=>$page_num);
	 } 


	
}


 ?>