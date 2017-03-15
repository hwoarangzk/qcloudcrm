<?php 


use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 素材数据处理
 */
class SourceDataController extends \Tuanduimao\Loader\Controller {


	function __construct() {
	}


	/**
	 *  保存素材数据
	 * @return [type] [description]
	 */
	function save() {
		// 实例化model
		$source =App::M('Source');
		// 获取id如果id存在修改
		// id不存在创建
		if(!empty($_POST['_id'])){
			try {
				$data = $source->__update($_POST['_id'],$_POST);

			 } catch (Exce $e) {
				echo $e->tojson;
				return;
			}
		}else{
			try{
			
				$data = $source->create($_POST);

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

		
		$source =App::M('source');

		// 获取id
		$id =!empty($_POST['id'])?$_POST['id']:"false";

		try {
		
			 $res = $source->__delete($id);

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
	 * 缓存页面载入
	 * @return [type] [description]
	 */
	function sethtml(){
		$_id = $_GET['_id'];
		$Source =App::M('source');
		// 如果id==空报错
		 if ( empty( $_id ) ) {
		 	throw new Excp('资源不存在','404', ['get'=>$_GET]);
		 }

		// 如果资源存在
		$html = $Source->html($_id);

		if( $html !== false ) {
			$data = ['data'=>$html];
		}else{
			// 查询这条数据的数值
			$data= $Source->getLine( "where _id=:id", [],['id'=>$_id ]);
			$data = $data['content'];
		}

		App::render($data,'desktop/source','portal.detial');

	}

}



 ?>