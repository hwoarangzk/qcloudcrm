<?php 
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Loader\App as App;
use \Tuanduimao\Wechat as Wechat;
use \Tuanduimao\Wxwork;



/**
 * 客户数据
 */
class MobCustomerDataController extends \Tuanduimao\Loader\Controller {

	function __construct() {
	}
	/**
	 * 保存客户数据
	 * @return [type] [description]
	 */
	function  save(){
		// 实例化model
		$customer =App::M('Customer');
		// 调用model方法
		$data= $customer->create($_POST);
		// 判断数据是否错误
		if( $data['result'] === false ) {
			// 如果错误返回数值
			try {
				$customer->create($_POST);

			 } catch (Exce $e) {
				echo $e->tojson;
				return;
			}

		}else{
			echo json_encode($data);
			return;
		}
	
	}

	
	/**
	 *
	 * 名片扫描处理
	 * @return [type] [description]
	 */
	function ocr () {
		// 获取上传图片id
		$media_id = $_POST['sid'];
		// 如果没哟获取到报错
		if ( empty($media_id) ) {
			throw new Excp('非法请求', 403, ['post'=>$_POST]);
		}
		
	
		// $we = new Wechat(['appid'=>'wxf427d2cb6ac66d2c',
		// 	'secret'=>'f393e0169885f0b7d7d07604a5205c']);

		$we = new Wxwork([
			'corpid' => 'WWe146299c731e6301',
			'secret' => 'VPFu2NpuNuQJx0MEOsC0pQolb-PtSmqnjqsptchjYbk'
		]);

		/**
		 * 实例化youtu（优图）
		 * appid 
		 * bucket
		 * SecretID
		 * SecretKey
		 * @var [type]
		 */
		$yt = App::M("Youtu", [
		"appid"=>"10071180",
		"bucket"=>"youtu",
		'SecretID'=>'AKIDsAORSK6UkpjR2RmsoZAdw65ucAdvr6uX',
		'SecretKey'=>'jMrAJH4fACAxd8RqAhMPK3FOL7meNveI']);

		// Wechat里面的方法
		// 获取图片文件
		$name = 'name_card_'.$media_id;
		$imageData = $we->getMedia($media_id);

		// 图片上传到空间
		$resp = $yt->update($imageData, $name);

		// 如果错误报错
		if ( $resp['code'] != 0 ) {
			throw new Excp('返回结果异常', 500, ['post'=>$_POST, 'upload'=>$resp]);
		}

		// 延迟2秒执行
		sleep(2);
		// 拍照上传获取信息
		$namecard = $yt->ocr( $resp['data']['download_url'] );
		// 如果错误报错
		if ( $resp['code'] != 0 ) {
			throw new Excp('返回结果异常', 500, ['post'=>$_POST, 'namecard'=>$resp] );
		}

		// 扫面信息存入session
		$_SESSION['namecard'] = $namecard['result_list']['0']['data'];

		echo json_encode($namecard);
	}

	/**
	 * 搜索结果（ajax传入）
	 * @return [type] [description]
	 */
	function find(){

		// 获取查询内容
		$keyword = $_POST['keyword'];
		// 获取查询页码
		$page = !empty($_POST['page'])?$_POST['page']:"1";

		// 实例化
		$customer = App::M('Customer');

		// 查询
		$data= $customer->query()
				->orderBy('name','asc')
				->where('fulltext', 'like','%'.$keyword.'%')
				->where('deleted_at', '=',NULL)
				->select()
				->paginate(5,['_id'],"",$page)
				->toArray();
		
		// 优化左右链接
		$customer->hackPageurlequ( $data );

		$data = ['data'=>$data['data'],'page'=>$page];
		$html = App::render($data,'mobile/customer','search.result',true);
		echo json_encode($message = ['html'=>$html,'page'=>$data["next_page_url"],'cur'=>$page]);
	}

}

?>