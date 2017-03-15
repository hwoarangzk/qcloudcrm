<?php 
use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Wechat as Wechat;

/**
 * 客户追踪API
 */
class TrackingAPIController extends \Tuanduimao\Loader\Controller {



	function __construct() {
	}

	/**
	 * 弹出标注
	 * @return [type] [description]
	 */
	function index(){

		// 实例化Tracking
		$Tracking = App::M("Tracking");

		// 获取当前id
		$_id = !empty($_GET['_id'])?$_GET['_id']:"";
		// 获取当前页号
		$page= isset($_GET['page']) ?$_GET['page'] : "1";


		$data = $Tracking ->searchpage($_id,$page);
		App::render($data,'desktop/tracking','index');
	}


	/**
	 * 客户追踪API
	 * @return [type] [description]
	 */
	function testCreate() {
		$tuan = new Tuan;
		$resp = $tuan->call('/apps/crmpg/Tracking/create',[],[
			[
				"customerid"=>"10",
				"oid" => rand('1','100'),
				"oname" => "注册",
				"content" => "用户帐号注册成功",
				"key" => "tuanduimao",
				"isdelete"=>"1",
				"createat"=>date('Y-m-d'),
				"id"=>rand('1','100')
			],
	
		]);

		echo json_encode($resp);
	}

	/**
	 * 分页处理
	 * @return [type] [description]
	 */
	function page(){

		$Tracking = App::M("Tracking");
		// 获取当前页号
		$page= isset($_POST['page']) ?$_POST['page'] : "1";

		// 获取当前id
		$_id = !empty($_POST['_id'])?$_POST['_id']:"";

		/**
		 * 搜索值
		 * @var [type]
		 */
		$searchmessage = !empty($_POST['searchmessage'])?$_POST['searchmessage']:"";

		$data = $Tracking->searchpage('37',$page,$searchmessage);

		App::render($data,'desktop/tracking','page');
	}
}

 ?>