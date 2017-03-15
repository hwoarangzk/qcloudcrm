<?php 
use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan;
use \Tuanduimao\Loader\App as App;
use \afinogen89\getmail\protocol;


class ScalingController  extends \Tuanduimao\Loader\Controller {
	
	function __construct() {
	}

	function test2() {
		$tuan = new Tuan;
		$resp = $tuan->call('/user/get',[],[]);

		Utils::out( $resp );
	}

	// 添加场景
	function add(){
		$Scaling =App::M('Scaling');
		//condition 场景的应用
		//ncident 具体的方法
		//fulltext 具体关键词
		//data 查询的数据（重要缺少不可）
		//数据库中存入事件和方法
		//秘钥
		$key ='
		-----BEGIN RSA PRIVATE KEY-----
		MIICXAIBAAKBgQDxP/S7Df1/GO4K8IWsxeKNP890DIPhj17S43N1ylEB4H9ZUcZs
		lV59+gJSdxlz4gznECUYXxbTDXpQ9LQgCvkDiKhhqrEMqIB6A4Y455akbSQKd9Po
		m3t5kpJQ+miOckfRPLBXMJuvEEXpfErVN+WJD/Bw+lhIkoGVpdzjYnU92wIDAQAB
		AoGARfD8yp4ruAVKPfGtT4GvRLQTONnIAkTGgO1gM+4LvjePtB15IVHMq0koEzBk
		OKx4gSS1HHO08kseAwpujjugJU4IPcGqIFJ+zxKLhOnqoUO+3DwgStfw63KTIa7t
		Zgm3G6+gLjsJZfO2HaNBY3bKwaYLzofedbCPMaStcGDVA4ECQQD4jXlkxYPF/qmR
		uK0ORmQ0XoKkbcXUAYO1UHVTU7ehROU3e0a2RaMcwbIboSfd5JyKjnLeOy46drFn
		D3cldi8JAkEA+Hp3LSFLfYUidD9FfzUnIPLNauPVD1I/i5JtWzXBfG9kp2DbFCgd
		c7srt8USq9b85OslnnjBwRJMpCMMHQSawwJBAKPhS/gYjwDeH1n4ZQozeWBaLFNU
		GYrmkLvc1+7gFQRdE7EYNBB8K8cLI286O7n/QQPOVoiWhq1/kwq71Lg7i1kCQGCK
		fHldYU5AhvVxi9fz1+MWUzd/k81jIGtjFfgFN8rYINxjZls7hs3rX/4DpNB9ND7h
		GfmrY2RXbs2rDE7N9i8CQCiARDNaG9zC12+EW2frUEghjBglxumQEwbEkF8QXLDs
		h9HslOkfD50mjl10mllP67mVxXahVw91G3lQi4eTj/M=
		-----END RSA PRIVATE KEY-----';
		$arr = [
					['condition'=>'外网出带宽','ncident'=>'Broadjoin','fulltext'=>json_encode(['index'=>'外网出带宽','warning'=>'外网出带宽 > 1.8 Mbps']),'data'=>json_encode(['api'=>'cvm.api.qcloud.com','method'=>'DescribeInstances','parameter'=>'lanIps.0']),'key'=>$key],
					['condition'=>'CPU利用率告警','ncident'=>'setLB','fulltext'=>json_encode(['index'=>'CPU利用率','warning'=>'CPU利用率 > 80 %']),'data'=>json_encode(['api'=>'lb.api.qcloud.com','method'=>'DescribeLoadBalancers','parameter'=>'backendWanIps.0']),'key'=>$key],
					['condition'=>'磁盘使用量告警','ncident'=>'clearLog','fulltext'=>json_encode(['index'=>'磁盘利用率','warning'=>'磁盘利用率 > 80 %']),'key'=>$key],
					['condition'=>'MySQL实例连接数告警','ncident'=>'setConn','fulltext'=>json_encode(['index'=>'云数据库当前连接数','warning'=>'当前连接数 > 200 个']),'data'=>json_encode(['api'=>'cdb.api.qcloud.com','method'=>'DescribeCdbProductList','parameter'=>'lanIps.0']),'key'=>$key],
					['condition'=>'网页服务器宕机','ncident'=>'addCvm','fulltext'=>json_encode(['index'=>'服务器网页服务器宕机','warning'=>'服务器网页服务器宕机']),'key'=>$key],

			];
		$data = $Scaling->select();
		//检测是否为空
		if (empty($data['data'])) {
			foreach($arr as $num=>$message){
				$data = $Scaling->create($message);
				if (empty($data)) {
					echo json_encode($data);
				}
			}
		}
	}

	//删除场景
	function rm(){



	} 

	//当前场景列表
	function ls(){



	}

	//触发事件
	function trigger(){
		$Scaling =App::M('Scaling');

		$log = $Scaling->clearLog();

		// 实例化
		$Monitor =App::M('Monitor');
		// 传入账号密码
		$arr =  ['host'=>'imap.exmail.qq.com','passwd'=>'Woaini110','user'=>'maoshun@diancloud.com'];
		// 接收邮件
		$data  =  $Monitor->recive($arr);
	
		$data = "";
	    $Scaling =App::M('Scaling');
		$data = $Monitor->parseMail($data);
	
		if($data["methond"]=='Broadjoin'){
			$data = $data['id']["instanceSet"]['0'];
			//Broadjoin 增大流量,报错警告为流量到达临界值处理方法为加大流量(本方法只对按需要计费有效)
			$data = $Scaling->Broadjoin($data["unInstanceId"],$data["bandwidth"]);
			if ($data['codeDesc']!='Success'){
				echo $data['message'];
				return;
			}
			
		}else if($data['methond']=='setLB'){

			// 获取负债均衡信息
			// loadBalancerId负载均衡实例ID
			$id = $data['id']['loadBalancerSet']['0']['unLoadBalancerId'];
			$message = $Scaling->searchLB($id);
			// 云服务器的唯一ID backends.n.instanceId
			$unInstanceId = $Scaling->dataser($message['wanIpSet']);
			// 查询负载均衡查询到浮点数降低为原来的一半
			// ackends.n.weight 绑定负债均衡权重
			$backends = floor($message['weight']*2);
			// 执行方法设置负债均衡
			$data = $Scaling->setLB($id,$unInstanceId,$backends);

			if ($data['codeDesc']!='Success'){
				echo $data['message'];
				return;
			}
			
		}else if($data['methond']=='clearLog'){


			$Scaling->clearLog();

		}else{


			echo "1";

		}
		
	}


	function test(){
		$data = "	尊敬的3553056501用户，

		您的监控指标：云数据库当前连接数于2016-11-27 15:55:00 发生告警，您可以登录云监控控制台查看或取消接收告警。
		告警详情
		告警对象：cdb127136(10.66.186.242:3306) | appId: 1252758974
		监控指标：云数据库当前连接数
		所属项目：默认项目
		告警内容：当前连接数 > 200 个
		腾讯云监控

		本邮件为系统推送，请不要直接回复。如有任何问题，请联系我们获取帮助";
		  
		$Monitor =App::M('Monitor');

		$data = $Monitor->parseMail($data);



	
	}





}
?>