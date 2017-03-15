<?php 


use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户追踪
 */

class TrackingController extends \Tuanduimao\Loader\Controller {

	function __construct() {
	}


	/**
	 * 客户追踪
	 * @return [type] [description]
	 */
	function index(){
		// 实例化Marking
		$Tracking = App::M("Tracking");
		
		// 获取当前id
		$_id = !empty($_GET['_id'])?$_GET['_id']:"";
		// 获取当前页号
		$page= isset($_GET['page']) ?$_GET['page'] : "1";

		$data = $Tracking ->searchpage($_id,$page);	

		App::render($data,'desktop/tracking','index');
	}

	// 创建接口
	function create() {
		$Tracking = App::M('Tracking');
		$resp = $Tracking->create($this->data);
		echo json_encode( $resp);
	}


	/**
	 * 分页处理
	 * @return [type] [description]
	 */
	function page(){

		$Marking = App::M("Tracking");
		// 获取当前页号
		$page= isset($_POST['page']) ?$_POST['page'] : "1";

		// 获取当前id
		$_id = !empty($_POST['_id'])?$_POST['_id']:"";

		//搜索值
		
		$searchmessage = !empty($_POST['searchmessage'])?$_POST['searchmessage']:"";

		$data = $Marking->searchpage($_id,$page,$searchmessage);

		App::render($data,'desktop/Marking','page');
	}





}



 ?>