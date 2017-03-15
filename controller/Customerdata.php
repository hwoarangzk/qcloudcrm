<?php 	
use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户管理数据操作
 */
class CustomerDataController extends \Tuanduimao\Loader\Controller {


	function __construct() {
	}


	/**
	 * 客户存入
	 * @return [type] [description]
	 */
	function save(){
		// 实例化model
		$customer =App::M('Customer');	
		/**
		 * 如果id存在执行修改，如果id不存在执行添加
		 */
		if(!empty($_POST['_id'])){
			// 尝试执行修改，如果错误报错
			try{
				$data = $customer->__update($_POST['_id'],$_POST);
			} catch (Exce $e) {
				echo $e->tojson;
				return;
			}
		}else{
			// 执行添加方法如果错误报错
			try {
				$data = $customer->create($_POST);
			} catch (Exce $e) {
				echo $e->tojson;
				return;
			}
		}
		echo json_encode($data);
	}

	/**
	 * 客户删除
	 * @return [type] [description]
	 */
	function  delete(){
		// 实例化model
		$customer =App::M('Customer');
		// 获取id，如果id不存在报错
		$id=!empty($_POST['id'])?$_POST['id']:"false";

		// 执行移出方法
		$test = $customer->remove($id,'_id',true);
		// 对删除结果做出判断
		if ($test==true) {

			echo "true";
		}else{

			echo "false";
		}
	}


	/**
	 * 生成二维码
	 * @return [type] [description]
	 */
	function orcode(){
		// 接收原链接
		$str = $_POST['str'];
		// new方法
		$utils = new Utils();
		// 执行链接
		$url =  $utils->ShortUrl($str);
		// 报出生成二维码的链接
		echo $url;
	}
}

?>