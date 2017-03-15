<?php
use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;


class SetupController extends \Tuanduimao\Loader\Controller {
	
	function __construct() {
	}



	function install() {
		$customer = App::M('Customer');
		$source = App::M('source');
		$task = App::M('task');
		$scaling = App::M('scaling');
		// 执行卸载
		try {
			$source->dropTable();
			$customer->dropTable();
			$task->dropTable();
			$scaling->dropTable();
		}catch( Excp $e) {

			echo $e->toJSON();
			return;

		}
		
		// 创建表
		$source = App::M('source');
		$customer = App::M('customer');
		$task = App::M('task');
		$scaling = App::M('scaling');
		try  {
			$source->__schema();
			$task->__schema();
			$customer->__schema();
			$scaling->__schema();
		}catch ( Excp $e ) {
			echo $e->toJSON();
			return;
		}


		echo json_encode('ok');
	}



	function upgrade(){
		echo json_encode('ok');	
	}

	function repair() {

		$cust = App::M('Customer');
		try  {
			$cust->__schema();
		}catch ( Excp $e ) {
			echo $e->toJSON();
			return;
		}

		echo json_encode('ok');		
	}

	// 卸载
	function uninstall() {
		$customer = App::M('Customer');
		$source = App::M('source');
		$task = App::M('task');
		$scaling = App::M('scaling');
		try  {
			$source->__clear();
			$customer->__clear();
			$task->__clear();
			$scaling->__clear();
		}catch ( Excp $e ) {
			echo $e->toJSON();
			return;
		}
		echo json_encode('ok');		
	}
}