<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;


class TaskDataController extends \Tuanduimao\Loader\Controller {

	function __construct() {
	}


	function save(){
		// 参看是否存在时间
		// 存在时间转换
		$time = $_POST['data']." ".$_POST['time'];
		$_POST['push_at'] = $time;
		// 实例化model
		$task =App::M('Task');
		// post获取id
		$_id = $_POST['_id'];
		// 判断id是否存在
		// 如果存在update
		// 不存在create
		if (!empty($_id)) {
			try {
				$data = $task->__update($_id,$_POST);
			} catch (Exce $e) {
				echo $e->tojson;
				return;
			}
		}else{
			try {
				$data = $task->create($_POST);
			} catch (Exce $e) {
				echo $e->tojson;
				return;
			}
		}
		echo json_encode($data);
	}


	/**
	 *  删除素材数据
	 * @return [type] [description]
	 */
	function dalete() {

		
		$Task =App::M('Task');

		// 获取id
		$id =!empty($_POST['id'])?$_POST['id']:"false";

		try {
		
			 $res = $Task->__delete($id);

		 } catch (Exce $e) {

			echo $e->tojson;
			return;
			
		}
		
		if ($res=="true") {

			echo "true";
			
		}else{

			echo "false";
		}
		

	}	



	/**
	 * 计算百分比
	 * @return [type] [description]
	 */
	function  percent(){
		// 获取id
		$_id =!empty($_POST['id'])?$_POST['id']:"";

		$Task = App::M('Task');
		
		// 查询这条数据的数值
		$data= $Task->getLine( "where _id=:id", [],['id'=>$_id ] );
		$percent = round($data['pushedcnt']/$data['rscnt'],2)*100;

		if ($percent=NAN){
			$percent= "0";
		}

		// 算出百分比
		echo $percent."%";

	}

}



 ?>