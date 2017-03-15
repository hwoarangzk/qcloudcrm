<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 客户检索
 */
class MobCustomerSearchController extends \Tuanduimao\Loader\Controller {

	function __construct() {
	}

	/**
	 *  首页（九宫格）
	 * @return [type] [description]
	 */
	function index(){

		$data = ['title'=>'客户管理'];
		App::render($data,'mobile/customer','search.index');
	}

	/**
	 * 客户检索
	 * @return [type] [description]
	 */
	function find(){
		
		$data = ['title'=>'客户检索'];
		App::render($data,'mobile/customer','search.find');
	}

}



 ?>