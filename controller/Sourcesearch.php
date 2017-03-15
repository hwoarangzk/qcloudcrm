<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;

/**
 * 素材查询
 */
class SourceSearchController extends \Tuanduimao\Loader\Controller {

	function __construct() {
	}

	function test(){
		$Customer = App::M('Source');
		$Customer->testcreate();

	}
	
	/**
	 * 素材首页
	 * @return [type] [description]
	 */
	function index() {

		// 获取模式参数(1：管理模式 2：阅读模式)
		$mode = !empty($_GET['mode'])?$_GET['mode']:"r";
		// 获取模糊查询内容
		$keyword = !empty($_GET['keyword'])?$_GET['keyword']:""; 
		// 获取当前页
		$page = !empty($_GET['page'])?$_GET['page']:"1";
		// 获取当前的排列方式默认为1（按时间的上升顺序排列）
		$order = !empty($_GET['order'])?$_GET['order']:"ct";
		$orderMap = [
			"ct" => ["created_at", "desc"], //sss
			"ct_asc" => ["created_at", "asc"],
			"name" => ["title", "desc"],
			"name_asc" => ["title","asc"]
		];
		// 排序方式
		$order_st = $orderMap[$order];
		// 实例化
		$Customer = App::M('Source');
		// 查询
		$data= $Customer->query()
				->orderBy($order_st['0'], $order_st['1'])
				->where('title', 'like','%'.$keyword.'%')
				->where('deleted_at', '=',NULL)
				->select()
				->paginate(5,['_id'],"&keyword=".$keyword."&page",$page)
				->toArray();

		// 分页
		$num = array();		
		for ($i='1'; $i<=$data["last_page"] ; $i++) { 
			$num[$i] = $i;
		}

		// 优化左右链接
		$Customer->hackPageurl( $data );

		/**
		 * data：列表页数据
		 * page：页码
		 * cur:  当前页码
		 * pre:  上一页
		 * next： 下一页
		 * keyword : 关键词
		 * $order : 排列
		 * $mode : 模式
		 */

		$data =['data'=>$data['data'],
				'page'=>$num,
				'cur'=>$data["current_page"],
				'pre'=>$data["prev_page_url"],
				'next'=>$data["next_page_url"],
				'total'=>$data['total'],
				'keyword'=>$keyword,
				'order'=>$order,
				'mode'=>$mode];
		
		// 阅读管理模式切换
		if ($mode =='r'){

			App::render($data,'desktop/source','search.index');

		}else{

			App::render($data,'desktop/source','search.admin');
		}

		return [
			'js' => [
		 			"js/plugins/select2/select2.full.min.js",
		 			"js/plugins/jquery-validation/jquery.validate.min.js",
		 			"js/plugins/dropzonejs/dropzone.min.js",
		 			"js/plugins/cropper/cropper.min.js",
		 			'js/plugins/masked-inputs/jquery.maskedinput.min.js',
		 			'js/plugins/jquery-tags-input/jquery.tagsinput.min.js',
			 		"js/plugins/dropzonejs/dropzone.min.js",
			 		"js/plugins/cropper/cropper.min.js",
		    		'js/plugins/jquery-ui/jquery-ui.min.js',
	        		'js/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js',
				],
			'css'=>[
	 			"js/plugins/select2/select2.min.css",
	 			"js/plugins/select2/select2-bootstrap.min.css"
	 		],
			'crumb' => [
	                 "素材管理" => APP::R('SourceSearch','index'),
	                 "推送素材" =>'',
	        ]
		];

	}


}
?>