<?php 


use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;


/**
 * 客户追踪
 */
class MobTrackingController extends \Tuanduimao\Loader\Controller {

	function __construct() {

	}



	/**
	 * 客户追踪入口
	 * @return [type] [description]
	 */
	function index(){
		App::render($data,'mobile/tracking','index');
	}



	/**
	 * 分页处理
	 * @return [type] [description]
	 */
	function page(){

		$Tracking = App::M("Tracking");
		//获取当前页号
		$page= isset($_POST['page']) ?$_POST['page'] : "1";

		//获取当前id
		$_id = !empty($_POST['_id'])?$_POST['_id']:"";

		/**
		 * 搜索值
		 * @var [type]
		 */
		$searchmessage = !empty($_POST['searchmessage'])?$_POST['searchmessage']:"";

		// 分页处理
		$data = $Tracking->searchpage($_id,$page,$searchmessage);

		$html = App::render($data,'mobile/tracking','page',true);
		echo json_encode($message =['html'=>$html,'cur'=>$page]);
	}



}






 ?>