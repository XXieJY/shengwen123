<?php

// +----------------------------------------------------------------------

// | 浩森PHP框架 [ IeasynetPHP ]

// +----------------------------------------------------------------------

// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]

// +----------------------------------------------------------------------

// | 官方网站：http://ieasynet.com

// +----------------------------------------------------------------------

// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// | 作者: 拼搏 <378184@qq.com>

// +----------------------------------------------------------------------



namespace app\cms\home;

use think\Db;

use util\Tree;
use \think\Request;

use EasyWeChat\Foundation\Application;  
use Doctrine\Common\Cache\RedisCache;
use EasyWeChat\Payment\Order;  

/**
 * 前台首页控制器
 * @package app\cms\admin
 */

class Pay extends Common

{

    /**
     * 首页
     * @author 拼搏 <378184@qq.com>
     * @return mixed
     */

    protected function options(){ //选项设置  

    	

        

            $config = [

              // ...

                'payment' => [

		            'merchant_id' => module_config('wechat.merchant'),

		            'key' => module_config('wechat.key'),

		            'cert_path' => ROOT_PATH.'wpay/apiclient_cert.pem', // XXX: 绝对路径！！！！

		            'key_path' => ROOT_PATH.'wpay/apiclient_key.pem',      // XXX: 绝对路径！！！！

		            'notify_url'         => url('cms/pay/paySuccess'), 

		            // 'device_info'     => '013467007045764',

		            // 'sub_app_id'      => '',

		            // 'sub_merchant_id' => '',

		            // ...

        		],

              // ..

            ];  

        $config2 = module_config('wechat');

        $config = array_merge($config, $config2);

        return $config;

    } 
    //补全支付openid
    public function payopenid($user=null,$u=null,$hosturl=null){
        $userchuancan=$user;

        $config = module_config('wechat');
        $cacheDriver = new RedisCache();
        // 创建 redis 实例
        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        $cacheDriver->setRedis($redis); 
        $config2['cache']=$cacheDriver;

        $app = new Application($config);
        $oauth = $app->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $a='location:http://'.$hosturl.'/index.php/cms/pay/index/user/'.$userchuancan.'/u/'.$u.'/payopenid/'.$user['original']['openid'];
        header($a);

    }
    public function payopenidget($userchuancan=null,$u=null,$hosturl=null){
      $config = module_config('wechat');
        $config2 = [
                // ...
                'oauth' => [
                'scopes'   => ['snsapi_base'],
                'callback' => 'http://'.module_config('agent.agent_rooturl').'/index.php/cms/pay/payopenid/user/'.$userchuancan.'/u/'.$u.'/hosturl/'.$hosturl,
                  ],
                ];
        $cacheDriver = new RedisCache();
        // 创建 redis 实例
        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        $cacheDriver->setRedis($redis);     
        $config['cache']=$cacheDriver;
        $config = array_merge($config, $config2);
        $app = new Application($config);
        $oauth = $app->oauth;
        $oauth->redirect()->send();
    }

    public function index($error=null,$cxid=null,$user=null,$u=null,$payopenid=null)

    {

		/*登陆验证方法*/
    if($error=='')
      {$error=0;}
    if($cxid=='')
      {$cxid=0;}
		session_start();

    
    if(!empty($user))
    {
    $_SESSION['wechat_user']=json_decode(base64_decode($user),true);
    $_SESSION['target_url']=base64_decode($u);
    }


    if(!empty($payopenid))
    {
      DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->update(['payopenid'=>$payopenid]);
    }

     if(empty($_SESSION['target_url']))
     {
     $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     }

    
     $openid=$_SESSION['wechat_user']['original']['openid'];
    
    $user=DB::table('ien_admin_user')->where('openid',$openid)->find();

    if($user['agentlogin']==1 && $user['payopenid']=="")
    {
      $userchuancan=base64_encode(json_encode($_SESSION['wechat_user']));
      $u=base64_encode($_SESSION['target_url']);
      $this->redirect('pay/payopenidget',['userchuancan'=>$userchuancan,'u'=>$u,'hosturl'=>$_SERVER['HTTP_HOST']]);  
    }

    if($_SERVER['HTTP_HOST']!=module_config('agent.agent_payurl'))
        {
          $userchuancan=base64_encode(json_encode($_SESSION['wechat_user']));
          $u=base64_encode($_SESSION['target_url']);
          $a='location:http://'. module_config('agent.agent_payurl').'/index.php/cms/pay/index/error/'.$error.'/cxid/'.$cxid.'/user/'.$userchuancan.'/u/'.$u;
          //$a='location:http://'.$_SERVER['HTTP_HOST'].'/index.php/cms/pay/index/error/'.$error.'/cxid/'.$cxid.'/user/'.$userchuancan.'/u/'.$u;
          header($a);
     }
     if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
    }


		//$openid=$_SESSION['wechat_user']['original']['openid'];
    

		$this->assign('user', $user);
    $this->assign('shubi', module_config('agent.agent_pay_money'));
    $this->assign('err', $error);
    $this->assign('zffs', module_config('agent.agent_pay_fangshi'));
    	//print_r( cookie('wechat_user'));

    //获取商品信息
    $cuxiaotitle="";
    $cuxiaoshij="";
    if(!empty($cxid))
    {
      $cuxiao=DB::table('ien_cuxiaolist')->where('id',$cxid)->whereTime('endtime','>',time())->find();
      if(empty($cuxiao))
      {
        $this->redirect('pay/index');
      }
      $pro=DB::table('ien_cuxiao')->where('cxid',$cxid)->where('leixing',2)->order('orderby asc')->select();
      $this->assign('pro', $pro);
      $this->assign('cuxiaotitle', $cuxiao['name']);
      $cuxiaoshij="活动日期:".date("Y/m/d",$cuxiao['starttime'])."-".date("Y/m/d",$cuxiao['endtime']);
      $this->assign('cuxiaoshij', $cuxiaoshij);
    }
    else
    {
      $pro=DB::table('ien_cuxiao')->where('leixing',1)->order('orderby asc')->select();
      $this->assign('pro', $pro);
    }

    return $this->fetch(); // 渲染模板

    



    }
    public function savellpayid($id=null)
    {
      session_start();
      if($id=='')
      {
        header("status: 400 Bad Request");
        return false;
      }
      else{
      $_SESSION['payprodcutid']=$id;
      return true;
      }
    }
    //连连支付
    public function llpay($id=null,$error=null,$proid=null)
    {

      session_start();

      if(empty($_SESSION['payprodcutid']))
      {
        //header('location:http://'. module_config('agent.agent_payurl').'/index.php/cms/pay/index/');
        $this->redirect('pay/index');
      }
      else{
      $id=$_SESSION['payprodcutid'];
      }
      //if($id==''){die("参数错误！");}

      $orderid='BOOK'.time().rand(10000,99999);

      //添加代理ID和渠道ID
      $tgid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('tgid');
      if(!empty($tgid))
      {
        $ddid=DB::table('ien_agent')->where('id',$tgid)->value('uid');
        if(!empty($ddid)){
          $did=$ddid;
          $sjid=DB::table('ien_admin_user')->where('id',$did)->value('did');
          if(!empty($sjid)){
            $qid=$sjid;
          }
          else{
            $qid=0;
          }
        }
        else{
        $did=0;
        $qid=0;
          }
      }
      else{
        $did=0;
        $qid=0;
      }
     
      $userinfo=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
      if($userinfo['agentlogin']=1 && !empty($userinfo['payopenid']))
      {
        $uid=$userinfo['payopenid'];
      }
      else
      {
        $uid=$_SESSION['wechat_user']['original']['openid'];
      }


      $data = ['uid' =>$_SESSION['wechat_user']['original']['openid'],

           'type' => '2',

           'status' => '0',

           'addtime' => time(),

           'paytime' => '0',

           'payid' => $orderid,

           'did' => $did,

           'qid' => $qid,

           'tgid' =>$tgid,

           ];
      
       //判断超期用户订单
      $isout=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('isout');
      if($isout==1)
      {
        $data['isout']=1;
      }
      //判断比例黑单
      $bili=module_config('agent.agent_klbili');
      $nokou=explode(',',module_config('agent.agent_nokou'));
      if($bili>0)
      {
        $sxid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('sxid');
        if(!in_array($sxid,$nokou)  || empty($nokou['0']))
        {
          $kl=mt_rand(1,$bili);
          if($kl<=2)
          {
            $data['isout']=1;
          }

        }
      }

      

    //设置订单金额类型商品ID
    $infodata=DB::table('ien_cuxiao')->where('id',$id)->find();
    $data['money'] =$infodata['money'];
    if($infodata['type']==1)
    {
    $data['paytype'] =2;
    }
    else
    {
    $data['paytype'] =1;
    }
    $data['cxid']=$id;


   // Db::table('ien_pay_log')->insert($data); 
    require_once ("./llpay/llpay.config.php");
    require_once ("./llpay/lib/llpay_apipost_submit.class.php");

    /**************************请求参数**************************/

      //必填

      //商家订单号
      $no_order = $orderid;

      //商户号
      $oid_partner = "201708210000817312";

      //订单金额
      $money_order = $data['money'];
      //商家订单时间
      $dt_order = date("YmdHis",time());
      //商家通知地址
      $notify_url = 'http://'.module_config('agent.agent_payurl').'/index.php/cms/pay/llpaycess/';
      //支付方式
      $pay_type = 12;
      //风控参数
      $risk_item = "pass";
      //订单查询接口地址
      $llpay_gateway_new = 'https://o2o.lianlianpay.com/offline_api/createOrder_init.htm';
      //需http://格式的完整路径，不能加?id=123这类自定义参数

      /************************************************************/

      //构造要请求的参数数组，无需改动
      $parameter = array (
        "oid_partner" => trim($llpay_config['oid_partner']),
        "sign_type" => trim($llpay_config['sign_type']),
        "no_order" => $no_order,
        "dt_order" => $dt_order,
        "money_order" => $money_order,
        "notify_url" => $notify_url,
        "pay_type" => $pay_type,
        "risk_item" => $risk_item,
        "openid"=>$uid,
        "appid"=>module_config('wechat.app_id'),
        //"openid"=>"oZN9Q1NSkKFBCwnvCEKrN0jHHQ9Y",
        //"appid"=>"wxa2a882ec0bee0f3f",
        
      );

      //20170616143530

      //建立请求
      $llpaySubmit = new \LLpaySubmit($llpay_config);
      $html_text = $llpaySubmit->buildRequestJSON($parameter,$llpay_gateway_new);

      $dataa=json_decode($html_text);
      $datadime=json_decode($dataa->dimension_url);

      $json='"appId" : "'.$datadime->appId.'", 
              "timeStamp":"'.$datadime->timeStamp.'", 
              "nonceStr" : "'.$datadime->nonceStr.'", 
              "package" : "'.$datadime->package.'",
              "signType" : "MD5",
              "paySign" : "'.$datadime->paySign.'",
              ';
      //如果有返回,并且返回状态是未支付,添加订单信息
      if($dataa->pay_status="1")
      {
        Db::table('ien_pay_log')->insert($data);  
      }

      $this->assign('json', $json);
      if(empty($_SESSION['target_url']))
      {$url="/index.php/cms/pay/index/";}
      else
        {$url=$_SESSION['target_url'];}
      $this->assign('url', $url);
      
      return $this->fetch(); // 渲染模板


    }


    public function llpaycess()
    {


require_once ("./llpay/llpay.config.php");
require_once ("./llpay/lib/llpay_notify.class.php");

//计算得出通知验证结果
$llpayNotify = new \LLpayNotify($llpay_config);
$llpayNotify->verifyNotify();
if ($llpayNotify->result) { //验证成功
  //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
  $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
  $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
  $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
  $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
 // $ddd=$no_order."/////".$oid_paybill."/////".$result_pay."/////".$money_order;
  if($result_pay == "SUCCESS"){
    //请在这里加上商户的业务逻辑程序代(更新订单状态、入账业务)
    //——请根据您的业务逻辑来编写程序——
    //payAfter($llpayNotify->notifyResp);

     // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单  

       $order = Db::table('ien_pay_log')->where('payid',$no_order)->find();

       if($order['status']==1)
       {
        die;
       }
       //通过订单获取商品信息
       $infodata=DB::table('ien_cuxiao')->where('id',$order['cxid'])->find();
       $score=$infodata['score'];
       $money=$infodata['money'];
       $typeday=$infodata['type'];
       $day=$infodata['day'];


                // 不是已经支付状态则修改为已经支付状态  

              Db::table('ien_pay_log')->where( 'payid' , $no_order )->update(['status' => '1','paytime' => time()]);

              Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->setInc('score', $score);
              //增加VIP天数
              if($typeday==2){
                $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                if($uinfo['isvip']=0 || $uinfo['vipetime']<time())
                {
                  $datatimer=time()+$day*86400;
                Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipstime' => time(),'vipetime'=>$datatimer]);

                }
                else
                {
                   $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                   $datatimer=$uinfo['vipetime']+$day*86400;

                  Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipetime'=>$datatimer]);
                }
              }



        //判断是否黑单
        $paylog=Db::table('ien_pay_log')->where( 'payid' , $no_order )->find();
        if($paylog['isout']!=1)
        {
        //充值成功给代理商增加余额
        $dl=DB::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
        if($dl['tgid'])
        {
          $tg=DB::table('ien_agent')->where('id',$dl['tgid'])->find();
          if($tg['uid'])
          {
            $dls=DB::table('ien_admin_user')->where('id',$tg['uid'])->find();
            if($dls['fcbl'])
            {
              $fy=$dls['fcbl'];
            }
            else
            {
              $fy=0.6;
            }
            $moneyjs=$money * $fy;
            Db::table('ien_admin_user')->where( 'id' , $tg['uid'] )->setInc('money', $moneyjs);
            //渠道商如果大于代理商比例,增加差价利润
            if($dls['did']!="" || $dls['did']!=0)
            {
              $qds=DB::table('ien_admin_user')->where('id',$dls['did'])->value('fcbl');
              if($qds!="" && $qds>$fy)
              {
                $cha=$qds-$fy;
                $moneqds=$money * $cha;
                Db::table('ien_admin_user')->where( 'id' , $dls['did'] )->setInc('money', $moneqds);
              }

            }
          }
        
        
        }
      }


  }
  //file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
  die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
  //file_put_contents("log.txt", "异步通知 验证失败\n", FILE_APPEND);
  //验证失败
  die("{'ret_code':'9999','ret_msg':'验签失败'}");
  //调试用，写文本函数记录程序运行情况是否正常
  //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
}
      

           

         

    }


    public function pay($id=null,$error=null){


	   session_start();


   		if($id==''){die("参数错误！");}

   		$orderid='BOOK'.time().rand(10000,99999);

   		//添加代理ID和渠道ID
   		$tgid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('tgid');
   		if(!empty($tgid))
   		{
   			$ddid=DB::table('ien_agent')->where('id',$tgid)->value('uid');
   			if(!empty($ddid)){
   				$did=$ddid;
   				$sjid=DB::table('ien_admin_user')->where('id',$did)->value('did');
   				if(!empty($sjid)){
   					$qid=$sjid;
   				}
   				else{
   					$qid=0;
   				}
   			}
   			else{
   			$did=0;
   			$qid=0;
   				}
   		}
   		else{
   			$did=0;
   			$qid=0;
   		}
     
      $userinfo=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
      if($userinfo['agentlogin']=1 && !empty($userinfo['payopenid']))
      {
        $uid=$userinfo['payopenid'];
      }
      else
      {
        $uid=$_SESSION['wechat_user']['original']['openid'];
      }


   		$data = ['uid' =>$_SESSION['wechat_user']['original']['openid'],

   				 'type' => '1',

   				 'status' => '0',

   				 'addtime' => time(),

   				 'paytime' => '0',

   				 'payid' => $orderid,

   				 'did' => $did,

   				 'qid' => $qid,

           'tgid' =>$tgid,

   				 ];

   		 //判断超期用户订单
      $isout=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('isout');
      if($isout==1)
      {
        $data['isout']=1;
      }
      //判断比例黑单
      $bili=module_config('agent.agent_klbili');
      $nokou=explode(',',module_config('agent.agent_nokou'));
      if($bili>0)
      {
        $sxid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('sxid');
        if(!in_array($sxid,$nokou)  || empty($nokou['0']))
        {
          $kl=mt_rand(1,$bili);
          if($kl<=2)
          {
            $data['isout']=1;
          }

        }
      }


   	//设置订单金额类型商品ID
    $infodata=DB::table('ien_cuxiao')->where('id',$id)->find();
    $data['money'] =$infodata['money'];
    if($infodata['type']==1)
    {
    $data['paytype'] =2;
    }
    else
    {
    $data['paytype'] =1;
    }
    $data['cxid']=$id;

		

		$product = [  

		    'body'             => '书币充值 - '.$data["money"].' 元',  

		    'trade_type'       => 'JSAPI',  

		    'out_trade_no'     => $orderid,  

		    'total_fee'        => $data['money']*100,  

		  	'notify_url'         => 'http://'.module_config('agent.agent_payurl').'/index.php/cms/pay/paysuccess', 

		    'openid'           => $uid,  

		    'attach'           => $id,  

		];

		
    
		$order = new Order($product);  

		$app = new Application($this->options());  

		$payment = $app->payment;  


		$result = $payment->prepare($order);  

		$prepayId = null;  
   

		if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){  
    
		    $prepayId = $result->prepay_id;

		    Db::table('ien_pay_log')->insert($data);  

		} else {  

		    var_dump($result);  

		    die("出错了。");  

		}  

		$json = $payment->configForPayment($prepayId);  

		  // 这个是jssdk里页面上需要用到的js参数信息。 

		//print_r($json);

		$this->assign('json', $json);
    

    $this->assign('ordsn', $order->ordsn); 
    if(empty($_SESSION['target_url']))
      {$url="/index.php/cms/pay/index/";}
    else
      {$url=$_SESSION['target_url'];}
    $this->assign('url', $url);

		return $this->fetch(); // 渲染模板

    



    }  

     public function paysuccess(){  

        $options = $this->options();  

        $app = new Application($options);  

        $response = $app->payment->handleNotify(function($notify, $successful){  

            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单  

            $order = Db::table('ien_pay_log')->where('payid',$notify->out_trade_no)->find();

            //通过订单获取商品信息
           $infodata=DB::table('ien_cuxiao')->where('id',$order['cxid'])->find();
           $score=$infodata['score'];
           $money=$infodata['money'];
           $typeday=$infodata['type'];
           $day=$infodata['day'];

            if (count($order) == 0) { // 如果订单不存在  

                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了  

            }  

            // 如果订单存在  

            // 检查订单是否已经更新过支付状态  

            if ($order['paytime']) { // 假设订单字段“支付时间”不为空代表已经支付  

                return true; // 已经支付成功了就不再更新了  

            }  

            // 用户是否支付成功  

            if ($successful) {  

                // 不是已经支付状态则修改为已经支付状态  

                Db::table('ien_pay_log')->where( 'payid' , $notify->out_trade_no )->update(['status' => '1','paytime' => time()]);

             	Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->setInc('score', $score);

             	//增加VIP天数
              if($typeday==2){
                $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                if($uinfo['isvip']=0 || $uinfo['vipetime']<time())
                {
                  $datatimer=time()+$day*86400;
                Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipstime' => time(),'vipetime'=>$datatimer]);

                }
                else
                {
                   $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                   $datatimer=$uinfo['vipetime']+$day*86400;

                  Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipetime'=>$datatimer]);
                }
              }
				//判断是否黑单
        $paylog=Db::table('ien_pay_log')->where( 'payid' , $notify->out_trade_no )->find();
        if($paylog['isout']!=1)
        {
        //充值成功给代理商增加余额
        $dl=DB::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
        if($dl['tgid'])
        {
          $tg=DB::table('ien_agent')->where('id',$dl['tgid'])->find();
          if($tg['uid'])
          {
            $dls=DB::table('ien_admin_user')->where('id',$tg['uid'])->find();
            if($dls['fcbl'])
            {
              $fy=$dls['fcbl'];
            }
            else
            {
              $fy=0.6;
            }
            $moneyjs=$money * $fy;
            Db::table('ien_admin_user')->where( 'id' , $tg['uid'] )->setInc('money', $moneyjs);
            //渠道商如果大于代理商比例,增加差价利润
            if($dls['did']!="" || $dls['did']!=0)
            {
              $qds=DB::table('ien_admin_user')->where('id',$dls['did'])->value('fcbl');
              if($qds!="" && $qds>$fy)
              {
                $cha=$qds-$fy;
                $moneqds=$money * $cha;
                Db::table('ien_admin_user')->where( 'id' , $dls['did'] )->setInc('money', $moneqds);
              }

            }
          }
        
        
        }
      }

            } else { // 用户支付失败  

                Db::table('ien_pay_log')->where( 'payid' , $notify->out_trade_no )->update(['status' => '0','paytime' => time()]);

            }  


   

            return true; // 返回处理完成  

        });  

    }  







        //威富通支付
    public function wftpay($id=null,$error=null,$proid=null)
    {

      session_start();


      if($id==''){die("参数错误！");}

      $orderid='BOOK'.time().rand(10000,99999);

      //添加代理ID和渠道ID
      $tgid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('tgid');
      if(!empty($tgid))
      {
        $ddid=DB::table('ien_agent')->where('id',$tgid)->value('uid');
        if(!empty($ddid)){
          $did=$ddid;
          $sjid=DB::table('ien_admin_user')->where('id',$did)->value('did');
          if(!empty($sjid)){
            $qid=$sjid;
          }
          else{
            $qid=0;
          }
        }
        else{
        $did=0;
        $qid=0;
          }
      }
      else{
        $did=0;
        $qid=0;
      }
     
      $userinfo=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
      if($userinfo['agentlogin']=1 && !empty($userinfo['payopenid']))
      {
        $uid=$userinfo['payopenid'];
      }
      else
      {
        $uid=$_SESSION['wechat_user']['original']['openid'];
      }


      $data = ['uid' =>$_SESSION['wechat_user']['original']['openid'],

           'type' => '1',

           'status' => '0',

           'addtime' => time(),

           'paytime' => '0',

           'payid' => $orderid,

           'did' => $did,

           'qid' => $qid,

           'tgid' =>$tgid,

           ];

       //判断超期用户订单
      $isout=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('isout');
      if($isout==1)
      {
        $data['isout']=1;
      }
      //判断比例黑单
      $bili=module_config('agent.agent_klbili');
      $nokou=explode(',',module_config('agent.agent_nokou'));
      if($bili>0)
      {
        $sxid=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->value('sxid');
        if(!in_array($sxid,$nokou)  || empty($nokou['0']))
        {
          $kl=mt_rand(1,$bili);
          if($kl<=2)
          {
            $data['isout']=1;
          }

        }
      }


    //设置订单金额类型商品ID
    $infodata=DB::table('ien_cuxiao')->where('id',$id)->find();
    $data['money'] =$infodata['money'];
    if($infodata['type']==1)
    {
    $data['paytype'] =2;
    }
    else
    {
    $data['paytype'] =1;
    }
    $data['cxid']=$id;

    

    /**************************请求参数**************************/

     $orderinfo=array(
            //商品名称
            'body'=>$infodata['name'],
            //ip地址
            'mch_create_ip'=>'127.0.0.1',
            'method'=>'submitOrderInfo',
            //订单号
            'out_trade_no'=>$orderid,
            //openid
            //'sub_openid'=>'',
            'sub_appid'=>module_config('wechat.appid'),
            'sub_openid'=>$_SESSION['wechat_user']['original']['openid'],
            //金额
            'total_fee'=>$data['money']*100,
           // 'total_fee'=>'1',
          'notify_url'=>'http://'.module_config('agent.agent_payurl').'/index.php/cms/pay/wftpaysuccess/',
          'callback_url'=>$tzurl,
            );

      /************************************************************/
      
      
        $options = array(
        CURLOPT_RETURNTRANSFER =>true,
        CURLOPT_HEADER =>false,
        CURLOPT_POST =>true,
        CURLOPT_POSTFIELDS => $orderinfo,
        );

    
        $ch = curl_init("http://".module_config('agent.agent_payurl')."/wftpay/request.php");
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
        $a=json_decode($result);
     //var_dump($options);
      // var_dump($a);
    //  die();
        Db::table('ien_pay_log')->insert($data); 
    $this->assign('data', $a->pay_info);
      $this->assign('url', $tzurl);

    return $this->fetch('wftpay'); // 渲染模板
    


    }


    public function wftpaysuccess(){
      require_once ("./llpay/llpay.config.php");
      require('./wftpay/Utils.class.php');
      require('./wftpay/config/config.php');
      require('./wftpay/class/RequestHandler.class.php');
      require('./wftpay/class/ClientResponseHandler.class.php');
      require('./wftpay/class/PayHttpClient.class.php');

        $this->resHandler = new \ClientResponseHandler();
        $this->reqHandler = new \RequestHandler();
        $this->pay = new \PayHttpClient();
        $this->cfg = new \Config();

        $this->reqHandler->setGateUrl($this->cfg->C('url'));
        $this->reqHandler->setKey($this->cfg->C('key'));

        $xml = file_get_contents('php://input');
        $this->resHandler->setContent($xml);
    //var_dump($this->resHandler->setContent($xml));
        $this->resHandler->setKey($this->cfg->C('key'));
        if($this->resHandler->isTenpaySign()){
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
        //echo $this->resHandler->getParameter('status');
        //此处可以在添加相关处理业务，校验通知参数中的商户订单号out_trade_no和金额total_fee是否和商户业务系统的单号和金额是否一致，一致后方可更新数据库表中的记录。 
        //更改订单状态

                $no_order=$this->resHandler->getParameter('out_trade_no');
                $order = Db::table('ien_pay_log')->where('payid',$no_order)->find();

       if($order['status']==1)
       {
        die;
       }
       //通过订单获取商品信息
       $infodata=DB::table('ien_cuxiao')->where('id',$order['cxid'])->find();
       $score=$infodata['score'];
       $money=$infodata['money'];
       $typeday=$infodata['type'];
       $day=$infodata['day'];


                // 不是已经支付状态则修改为已经支付状态  

              Db::table('ien_pay_log')->where( 'payid' , $no_order )->update(['status' => '1','paytime' => time()]);

              Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->setInc('score', $score);
              //增加VIP天数
              if($typeday==2){
                $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                if($uinfo['isvip']=0 || $uinfo['vipetime']<time())
                {
                  $datatimer=time()+$day*86400;
                Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipstime' => time(),'vipetime'=>$datatimer]);

                }
                else
                {
                   $uinfo=Db::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
                   $datatimer=$uinfo['vipetime']+$day*86400;

                  Db::table('ien_admin_user')

                ->where( 'openid' , $order['uid'])

                ->update(['isvip' => '1','vipetime'=>$datatimer]);
                }
              }



        //判断是否黑单
        $paylog=Db::table('ien_pay_log')->where( 'payid' , $no_order )->find();
        if($paylog['isout']!=1)
        {
        //充值成功给代理商增加余额
        $dl=DB::table('ien_admin_user')->where( 'openid' , $order['uid'] )->find();
        if($dl['tgid'])
        {
          $tg=DB::table('ien_agent')->where('id',$dl['tgid'])->find();
          if($tg['uid'])
          {
            $dls=DB::table('ien_admin_user')->where('id',$tg['uid'])->find();
            if($dls['fcbl'])
            {
              $fy=$dls['fcbl'];
            }
            else
            {
              $fy=0.6;
            }
            $moneyjs=$money * $fy;
            Db::table('ien_admin_user')->where( 'id' , $tg['uid'] )->setInc('money', $moneyjs);
            //渠道商如果大于代理商比例,增加差价利润
            if($dls['did']!="" || $dls['did']!=0)
            {
              $qds=DB::table('ien_admin_user')->where('id',$dls['did'])->value('fcbl');
              if($qds!="" && $qds>$fy)
              {
                $cha=$qds-$fy;
                $moneqds=$money * $cha;
                Db::table('ien_admin_user')->where( 'id' , $dls['did'] )->setInc('money', $moneqds);
              }

            }
          }
        
        
        }
      }





                \Utils::dataRecodes('接口回调收到通知参数',$this->resHandler->getAllParameters());

                echo 'success';
                exit();
            }else{
                echo 'failure';
                exit();
            }
        }else{
            echo 'failure';
        }
    }

   

}