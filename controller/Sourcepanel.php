<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 素材面板
 */
class SourcePanelController extends \Tuanduimao\Loader\Controller {


	function __construct() {
	}

	/**
	 * 素材入口
	 * @return [type] [description]
	 */
	function index(){

		// 接收每条记录的_id
		$_id = !empty($_GET['_id']) ?$_GET['_id'] : "";

		$type = !empty($_GET['type']) ?$_GET['type'] : "read";

		$Source = App::M('Source');
		
		// 查询这条数据的数值
		$data= $Source->getLine( "where _id=:id", [],['id'=>$_id ]);
		
		$data = ['_id'=>$_id,'title'=>$data['title'],'type'=>$type];
		App::render($data,'desktop/source','panel.index');
	}


	/**
	 *             
	 * 查看素材
	 * @return [type] [description]
	 */
	function read(){

		$_id = !empty($_GET['_id']) ?$_GET['_id'] : "";
		// 实例化
		$Source = App::M('Source');

		// 查询这条数据的数值
		$data= $Source->getLine( "where _id=:id", [],['id'=>$_id ] );

		$data  = ['data'=>$data];
		App::render($data,'desktop/source','panel.read');
	}


	/**
	 * 修改素材
	 * @return [type] [description]
	 */
	function modify(){

		$_id = !empty($_GET['_id']) ?$_GET['_id'] : "";
		// 实例化
		$Source = App::M('Source');

		// 查询这条数据的数值
		$data= $Source->getLine( "where _id=:id", [],['id'=>$_id ] );

		$data  = ['data'=>$data];
		
		App::render($data,'desktop/source','panel.modify');
	}

	
	/**
	 * 创建素材
	 * @return [type] [description]
	 */
	function create(){

		App::render($data,'desktop/source','panel.create');

	}


}
 ?>