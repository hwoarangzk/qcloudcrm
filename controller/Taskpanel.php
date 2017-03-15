<?php 

use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;


/**
 * 任务面板
 */
class TaskPanelController extends \Tuanduimao\Loader\Controller {


	function __construct() {
	}

	// 详情页index入口
	function index(){
		// 获取id值
		$id=!empty($_GET["_id"])?$_GET["_id"]:"";

		$type=!empty($_GET["type"])?$_GET["type"]:"read";

		$Task = App::M('Task');
		$data= $Task->getLine( "where _id=:id", [],['id'=>$id ]);

		$data =['id'=>$id,'data'=>$data,'type'=>$type];

		App::render($data,'desktop/task','panel.index');
	}



	/**
	 * 查看任务
	 * @return [type] [description]
	 */
	function read() {

		//获取id
		$id=!empty($_GET["id"])?$_GET["id"]:"";
		//实例化
		$Task = App::M('Task');
		//查询
		$data= $Task->getLine( "where _id=:id", [],['id'=>$id ]);

		$data =['data'=>$data];

		App::render($data,'desktop/task','panel.read');
	}

	
	/**
	 * 修改任务
	 * @return [type] [description]
	 */
	function modify(){

		//获取id
		$id=!empty($_GET["id"])?$_GET["id"]:"";
		//实例化
		$Task = App::M('Task');
		//查询
		$data= $Task->getLine( "where _id=:id", [],['id'=>$id ]);
		
		$data =['data'=>$data,
		        'date'=>date("Y-m-d",strtotime($data['push_at'])),
		        'time'=>date("H:i:s",strtotime($data['push_at']))
		       ];
		
		
		App::render($data,'desktop/task','panel.modify');
	}

	
	/**
	 * 创建任务
	 * @return [type] [description]
	 */
	function create(){
		App::render($data,'desktop/task','panel.create');
	}

	/**
	 * 右侧弹出框
	 * @return [type] [description]
	 */
	function select(){
		
		// 实例化
		$Source = App::M('Source');
		//获取type
		$type=!empty($_POST["type"])?$_POST["type"]:"";

		// 查询
		$data= $Source->query()
				->orderBy("created_at", "desc")
				->where('deleted_at', '=',NULL)
				->select()
				->paginate(10,['_id'],'1')
				->toArray();
		// 右侧弹出框页码
		$num = array();		
		for ($i='1'; $i<=$data["last_page"] ; $i++) { 
			$num[$i] = $i;
		}


		$data = ['data'=>$data['data'],'pageall'=>$num,'page'=>'1','last_page'=>$data["last_page"],'type'=>$type];
		App::render($data,'desktop/task','panel.select');
	}

	/**
	 * 右侧弹出框
	 * @return [type] [description]
	 */
	function selectpage(){

		$page = !empty($_POST['page'])?$_POST['page']:"1";

		// 实例化
		$Task = App::M('Task');

		// 查询
		$data= $Source->query()
				->orderBy("created_at", "desc")
				->where('deleted_at', '=',NULL)
				->select()
				->paginate(10,['_id'],"",$page)
				->toArray();


		// 分页
		$num = array();		
		for ($i='1'; $i<=$data["last_page"] ; $i++) { 

			$num[$i] = $i;
		}

		$data = ['data'=>$data['data'],'pageall'=>$num,'page'=>$page,'last_page'=>$data["last_page"]];

		App::render($data,'desktop/task','panel.selected');
	}



	/**
	 * 通过id获取内容（ajax传入）
	 */
	function chooseid(){

		$_id = !empty($_POST['_id'])?$_POST['_id']:"1";
		$Task = App::M('Source');
		// 查询这条数据的数值
		$data= $Task->getLine( "where _id=:id", [],['id'=>$_id ] );
		echo json_encode($data);
	}

}

 ?>