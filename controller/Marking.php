<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户标注
 */
class MarkingController extends \Tuanduimao\Loader\Controller {
	
	function __construct() {
	}

	/**
	 * 弹出标注
	 * @return [type] [description]
	 */
	function index(){
		// 实例化Marking
		$Marking = App::M("Marking");
		// 获取当前id
		$_id = !empty($_GET['_id'])?$_GET['_id']:"";
		// 获取当前页号
		$page= isset($_GET['page']) ?$_GET['page'] : "1";
		// 对分页进行查询
		$data = $Marking ->searchpage($_id,$page);
		App::render($data,'desktop/Marking','index');
	}
	
	/**
	 * 标注存入
	 * @return [type] [description]
	 */
	function save(){
		// 实例化Marking
		$Marking = App::M("Marking");
		//（当前用户）
		$user =  $this->user;
		/**
		 * createat:创建时间
		 * count：标注内容
		 * customerid：标注id
		 * uname 用户名
		 * isdelete是否删除
		 * id:当前信息id
		 * key查询的key
		 */
		$contents = [
						[
							"createat" =>date("Y-m-d H:i:s",time()),
							"content"=>$_POST['content'],
							"customerid"=>$_POST['_id'],
							"uid"=>$user['userid'],
							"uname" =>$user['name'],
							"isdelete"=>"0",
							"id"=>$Marking->genStr(15),
							"key"=>"tencent"
						]
					];
		// 创建数据
		$message =$Marking->create($contents);
	
		echo json_encode($message);
		return;
	}

	/**
	 * 分页处理
	 * @return [type] [description]
	 */
	function page(){
		// 实例化
		$Marking = App::M("Marking");
		// 获取当前页号
		$page= isset($_POST['page']) ?$_POST['page'] : "1";
		// 获取当前id
		$_id = !empty($_POST['_id'])?$_POST['_id']:"";

		/**
		 * 搜索值
		 * 
		 */
		$searchmessage = !empty($_POST['searchmessage'])?$_POST['searchmessage']:"";
		$data = $Marking->searchpage($_id,$page,$searchmessage);
		App::render($data,'desktop/Marking','page');
	}

}
 ?>