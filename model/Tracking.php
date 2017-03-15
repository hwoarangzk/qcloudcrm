<?php 
require_once(__DIR__.'/Yunsou.php'); 
use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;


// 追踪
class TrackingModel extends \YunsouModel{
	function __construct() {
		parent::__construct([
	 	"appId"=>"52940002", 
	 	"Region"=>"gz",
	 	'SecretID'=>'AKIDcEi3fI86MQNAlEHrxxpcFnHclIpD3fll',
	 	'SecretKey'=>'0zscSBoGdty5BARI7veyb4teEx3992oT'
	 	]);
	}

	/**
	 * 分页查询
	 * @return [type] [description]
	 */
	function searchpage($_id,$page,$searchmessage){

		// 按照当前id进行查询
		
		if (empty($searchmessage)){
	 		$key = $this->search("tuanduimao",['num'=>"[N:customerid:$_id:$_id]"],$page,'6');
	 	}else{

	 		$key = $this->search($searchmessage,['num'=>"[N:customerid:$_id:$_id]"],$page,'6');
	 	}

		/**
		 * page:每页显示个数
		 * num：当前页数
		 * all：数据个数
		 */
		//根据id调取分页信息
		$page_num = $this->page(['page'=>'6','all'=>$key['data']["eresult_num"]]);


	 	// 把查询的内容进行循环
	 	$message = array();
	 	foreach ($key["data"]["result_list"] as $num => $data) {

	 		$message[$num]=$this->objarray_to_array(json_decode($data["doc_meta"]));
	 	}	
	
		return array('data'=>$message,
	 			'page'=>$page_num['page_sum'],
	 			'page_num'=>$page,
	 			'_id'=>$_id,
	 			'page_count'=>count($page_num['page_sum'])
	 	);
	}
	



}


 ?>