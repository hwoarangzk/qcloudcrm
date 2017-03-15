<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户面板
 */
class CustomerPanelController extends \Tuanduimao\Loader\Controller {


	function __construct() {
	}

	/**
	 * 载入添加客户页面
	 * @return [type] [description]
	 */
	function create(){

		$type = !empty($_GET['type'])?$_GET['type'] : "read";
		$data = ['type'=>$type];
		App::render($data,'desktop/customer','panel.create');
	}


	/**
	 * 客户操作栏入口
	 * @return [type] [description]
	 */
	function index(){

		// 接收每条记录的_id
		$_id = $_GET['_id'];
		// 如果id为空不在执行
		if (empty($_id)){
			echo "缺少id";	
			die();
		}
		$data = ['_id'=>$_id];
		App::render($data,'desktop/customer','panel.index');
	}


	/**
	 * 客户阅读模式
	 * @return [type] [description]
	 */
	function read(){
		
		// 接收每条记录的_id
		$_id = $_GET['_id'];
		// 如果id为空不在执行
		if (empty($_id)){
			echo "缺少id";	
			die();
		}
		// 实例化
		$Customer = App::M('Customer');
		// 查询这条数据的数值
		$data= $Customer->getLine( "where _id=:id", [],['id'=>$_id ] );
		$data  = ['data'=>$data];
		App::render($data,'desktop/customer','panel.read');
	}

	/**
	 * 修改模式
	 * @return [type] [description]
	 */
	function  modify(){
		// 接收每条记录的_id
		$_id = $_GET['_id'];
		// 如果id为空不在执行
		if (empty($_id)){
			echo "缺少id";	
			die();
		}
		// 实例化
		$Customer = App::M('Customer');
		// 查询这条数据的数值
		$data= $Customer->getLine( "where _id=:id", [],['id'=>$_id ] );
		$data  = ['data'=>$data];
		App::render($data,'desktop/customer','panel.modify');
	}

}

 ?>