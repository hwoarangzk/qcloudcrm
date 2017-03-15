<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户标注
 */
class MobMarkingController extends \Tuanduimao\Loader\Controller {

	function __construct() {

	}

	/**
	 * 标记页入口
	 * @return [type] [description]
	 */
	function index(){
		
		App::render($data,'mobile/marking','index');
	}

	/**
	 * 标注存入
	 * @return [type] [description]
	 */
	function save(){
		
		// 实例化Marking
		$Marking = App::M("Marking");
		// 用户
		$user =  $this->user;

		/**
		 * createat:创建时间
		 * content：标注内容
		 * customerid：标注id
		 * uid用户id
		 * uname用户名字
		 * isdelete是否删除
		 * id数据id
		 * key查询(根据这个查询)
		 * @var [type]
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

		$message =$Marking->create($contents);
		echo json_encode($message);
		return;
	}

	/**
	 * 分页处理（分页标注）
	 * @return [type] [description]
	 */
	function page(){

		$Marking = App::M("Marking");
		//获取当前页号
		$page= isset($_POST['page']) ?$_POST['page'] : "1";
		//获取当前id
		$_id = !empty($_POST['_id'])?$_POST['_id']:"";
		/**
		 * 搜索值
		 * @var [type]
		 */
		$searchmessage = !empty($_POST['searchmessage'])?$_POST['searchmessage']:"";

		// 分页查询
		$data = $Marking->searchpage($_id,$page,$searchmessage);
		$html = App::render($data,'mobile/Marking','page',true);

		echo json_encode($message =['html'=>$html,'cur'=>$page]);
	}

}



 ?>