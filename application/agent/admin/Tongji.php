<?php
/**
 * User: pinbo
 * Date: 2017/4/8
 * Time: 上午9:50
 */
//后台访问控制器
namespace app\agent\admin;  
  
use think\console\Command;  
use think\console\Input;  
use think\console\Output;  
use think\Db;
  
class Tongji extends Command 
{  
    protected function configure()  
    {  
        $this->setName('tongji')->setDescription('Command tongji');  
    }  
  
    protected function execute(Input $input, Output $output)  
    {  
    	$output->writeln("正在更新订单统计请等待:");
    	$this->allpay();
        $output->writeln("订单统计更新成功");  
        $output->writeln("正在更新用户统计请等待:");
        $this->alluser();
        $output->writeln("用户统计更新成功");  
        $output->writeln("正在更新平台统计请等待:");
        $this->alllog();
        $output->writeln("平台统计更新成功");  
        $output->writeln("正在更新推广信息请等待:");
        $this->updatebookid();
        $output->writeln("推广信息更新成功");  
        $output->writeln("正在更新小说统计请等待:");
        $this->booklog();
        $output->writeln("小说统计更新成功");  
    }  
    public function allpay(){
    	ignore_user_abort(true); // 后台运行
		set_time_limit(0); // 取消脚本运行时间的超时上限


		$user=DB::table('ien_admin_user')->where('role','<>','3')->select();
		foreach($user as $key=>$value)
		{
			$paylog=DB::query("select * from ien_pay_log where did=".$value['id']." and paytype <> 0 group by FROM_UNIXTIME(addtime,'%Y%m%d')");
			if(!empty($paylog))
			{
			foreach ($paylog as $k => $valuea) {
					/*start*/
					$datey=date('Y',$valuea['addtime']);
					$datem=date('m',$valuea['addtime']);
					$dated=date('d',$valuea['addtime']);
					$beginToday=mktime(0,0,0,$datem,$dated,$datey);
					$endToday=mktime(0,0,0,$datem,$dated+1,$datey)-1;
		
					
					//当天全部充值
			$data['money']=(int)DB::table("ien_pay_log")->where('status',1)->where('isout','NEQ','1')->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->sum('money');
			//当天普通充值全部
			$data['ptmoney']=(int)DB::table("ien_pay_log")->where('status',1)->where('isout','NEQ','1')->where('paytype',2)->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->sum('money');
			//当天VIP充值全部
			$data['vipmoney']=(int)DB::table("ien_pay_log")->where('status',1)->where('isout','NEQ','1')->where('paytype',1)->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->sum('money');
			//普通充值笔数
			$data['ptnum']=DB::table("ien_pay_log")->where('paytype',2)->where('isout','NEQ','1')->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('id');
			//VIP充值笔数
			$data['vipnum']=DB::table("ien_pay_log")->where('paytype',1)->where('isout','NEQ','1')->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('id');
			//普通未充值笔数
			$data['ptnot']=DB::table("ien_pay_log")->where('status',0)->where('isout','NEQ','1')->where('paytype',2)->whereTime('addtime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('id');
			//VIP未充值笔数
			$data['vipnot']=DB::table("ien_pay_log")->where('status',0)->where('isout','NEQ','1')->where('paytype',1)->whereTime('addtime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('id');
			//普通充值人数
			$data['ptuser']=DB::table("ien_pay_log")->where('status',1)->where('isout','NEQ','1')->distinct(true)->field('uid')->where('paytype',2)->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('uid');
			//vip充值人数
			$data['vipuser']=DB::table("ien_pay_log")->where('status',1)->where('isout','NEQ','1')->distinct(true)->field('uid')->where('paytype',2)->whereTime('paytime', 'between',[$beginToday,$endToday])->where('did',$value['id'])->count('uid');

			$monthlog=DB::table("ien_day_money")->whereTime('addtime', 'between',[$beginToday,$endToday])->where('uid',$value['id'])->find();
					
					if(empty($monthlog))
					{	
						$data['uid']=$value['id'];
						$data['addtime']=$beginToday;
						Db::table('ien_day_money')->insert($data);
					}
					else
					{
						$data['uid']=$value['id'];
						$data['addtime']=$beginToday;
						Db::table('ien_day_money')->where('id',$monthlog['id'])->update($data);
					}

					/*end*/
					
				}
				//log
				file_put_contents("paylog.txt","代理商ID:".$value['id']."更新完成\r\n",FILE_APPEND);
				sleep(1);

			}
		}




    }


    public function alluser($id=null){
		
		ignore_user_abort(true); // 后台运行
		set_time_limit(0); // 取消脚本运行时间的超时上限
		

		$user=DB::table('ien_admin_user')->where('role','<>','3')->select();
		foreach($user as $key=>$value)
		{
			$userlog=DB::query("select * from ien_admin_user where sxid=".$value['id']." and isout = 0 group by FROM_UNIXTIME(create_time,'%Y%m%d')");
			
			if(!empty($userlog))
			{
			foreach ($userlog as $k => $valuea) {
					/*start*/
					$datey=date('Y',$valuea['create_time']);
					$datem=date('m',$valuea['create_time']);
					$dated=date('d',$valuea['create_time']);
					$beginToday=mktime(0,0,0,$datem,$dated,$datey);
					$endToday=mktime(0,0,0,$datem,$dated+1,$datey)-1;
		
		//当日新增人数合计
		$data['adduser']=0;
		//男性
		$data['nan']=0;
		//女性
		$data['nv']=0;
		//未知
		$data['weizhi']=0;
		//消费
		$data['payuser']=0;
		//关注
		$data['isgz']=0;
		//获取当前代理推广男性用户信息
		$man=DB::table('ien_admin_user')
		->where('sxid',$value['id'])
		->whereTime('create_time', 'between',[$beginToday,$endToday])
		->where('ien_admin_user.sex=1')
		->where('isout','0')
		->count('id');
		$data['nan']+=$man;
		//获取当前代理推广女性用户信息
		$user_women=DB::table('ien_admin_user')
		->where('sxid',$value['id'])
		->whereTime('ien_admin_user.create_time','between',[$beginToday,$endToday])
		->where('ien_admin_user.sex=2')
		->where('isout','0')
		->count('id');
		$data['nv']+=$user_women;	
		//获取当前代理推广未知用户信息
		$user_weizhi=DB::table('ien_admin_user')
		->where('sxid',$value['id'])
		->whereTime('ien_admin_user.create_time', 'between',[$beginToday,$endToday])
		->where('ien_admin_user.sex=0')
		->where('isout','0')
		->count('id');
		$data['weizhi']+=$user_weizhi;
		//获取当前代理推广全部用户信息
		$user_num=DB::table('ien_admin_user')
		->where('sxid',$value['id'])
		->whereTime('ien_admin_user.create_time', 'between',[$beginToday,$endToday])
		->where('isout','0')
		->count('id');
		$data['adduser']+=$user_num;
		//获取当前代理推广关注用户信息
		$user_isgz=DB::table('ien_admin_user')
		->where('sxid',$value['id'])
		->whereTime('ien_admin_user.create_time', 'between',[$beginToday,$endToday])
		->where('ien_admin_user.isguanzhu=1')
		->where('isout','0')
		->count('id');
		$data['isgz']+=$user_isgz;
		//获取当前代理推广付费用户信息
		$user_xiaofei=DB::table('ien_pay_log')
		->where('did',$value['id'])
		->where('status','1')
        ->where('isout','NEQ','1')
		->field('uid')
		->whereTime('paytime', 'between',[$beginToday,$endToday])
		->count('DISTINCT uid');
		$data['payuser']+=$user_xiaofei;

			$userlog=DB::table("ien_day_user")->whereTime('addtime', 'between',[$beginToday,$endToday])->where('uid',$value['id'])->find();

			if(empty($userlog))
			{	
				$data['uid']=$value['id'];
				$data['addtime']=$beginToday;
				Db::table('ien_day_user')->insert($data);
			}
			else
			{

				$data['uid']=$value['id'];
				$data['addtime']=$beginToday;
				Db::table('ien_day_user')->where('id',$userlog['id'])->update($data);
			}
					/*end*/
					
				}

				@ini_set('implicit_flush',1);
				ob_implicit_flush(1);
				@ob_end_clean();

				//log
				file_put_contents("userlog.txt","代理商ID:".$value['id']."更新完成\r\n",FILE_APPEND);
				sleep(1);

			}
		}




	}

	public function alllog(){
			ignore_user_abort(true); // 后台运行
			set_time_limit(0); // 取消脚本运行时间的超时上限

			$userlog=DB::query("select * from ien_pay_log where paytype<>0 group by FROM_UNIXTIME(addtime,'%Y%m%d'),FROM_UNIXTIME(paytime,'%Y%m%d')");
			
			if(!empty($userlog))
			{
			foreach ($userlog as $k => $valuea) {
					/*start*/
					$datey=date('Y',$valuea['addtime']);
					$datem=date('m',$valuea['addtime']);
					$dated=date('d',$valuea['addtime']);
					$beginToday=mktime(0,0,0,$datem,$dated,$datey);
					$endToday=mktime(0,0,0,$datem,$dated+1,$datey)-1;

			$data['yd_money']=(int)DB::table('ien_pay_log')->where('status',1)->where('did','NEQ','0')->where('isout','NEQ','1')->whereTime('paytime', 'between', [$beginToday, $endToday])->sum('money');
			$data['yd_sum']=DB::table('ien_pay_log')->where('status',1)->where('did','NEQ','0')->where('isout','NEQ','1')->whereTime('addtime', 'between', [$beginToday, $endToday])->count('id');
			$data['zj_money']=(int)DB::table('ien_pay_log')->where('status',1)->where('did','0')->whereTime('paytime', 'between', [$beginToday, $endToday])->sum('money');
			$data['zj_sum']=DB::table('ien_pay_log')->where('status',1)->where('did','0')->whereTime('addtime', 'between', [$beginToday, $endToday])->count('id');
			$data['yd_user']=DB::table('ien_admin_user')->where('sxid','NEQ','0')->whereTime('create_time', 'between', [$beginToday, $endToday])->count('id');
			$data['yd_guanzhu']=DB::table('ien_admin_user')->where('sxid','NEQ','0')->where('isguanzhu','1')->whereTime('create_time', 'between', [$beginToday, $endToday])->count('id');
			$data['zj_user']=DB::table('ien_admin_user')->where('sxid','0')->whereTime('create_time', 'between', [$beginToday, $endToday])->count('id');
			$data['zj_guanzhu']=DB::table('ien_admin_user')->where('sxid','0')->where('isguanzhu','1')->whereTime('create_time', 'between', [$beginToday, $endToday])->count('id');
			$data['kl_log']=(int)DB::table('ien_pay_log')->where('status',1)->where('did','NEQ','0')->where('isout','1')->whereTime('paytime', 'between', [$beginToday, $endToday])->sum('money');
		
		

			$userlog=DB::table("ien_all_log")->whereTime('addtime', 'between',[$beginToday,$endToday])->find();

			if(empty($userlog))
			{	
				$data['addtime']=$beginToday;
				Db::table('ien_all_log')->insert($data);
			}
			else
			{
				$data['addtime']=$beginToday;
				Db::table('ien_all_log')->where('id',$userlog['id'])->update($data);
			}
					/*end*/
					
				}


			}

	}

	public function updatebookid(){
		ignore_user_abort(true); // 后台运行
		set_time_limit(0); // 取消脚本运行时间的超时上限
		$userlog=DB::query("select * from ien_agent where ljlx <> 3");
			
		if(!empty($userlog))
		{
			foreach ($userlog as $k => $valuea) {
				$bookid=DB::table('ien_chapter')->where('id',$valuea['zid'])->column('bid');
				if(!empty($bookid))
				{
					try{
						DB::table('ien_agent')->where('id',$valuea['id'])->update(['bid'=>$bookid['0']]);
					}
					catch(\Exception $e){
					}

				}

			}
		}


	}


	public function booklog(){
			ignore_user_abort(true); // 后台运行
			set_time_limit(0); // 取消脚本运行时间的超时上限

			$userlog=DB::query("select * from ien_pay_log where paytype<>0 and status=1 group by FROM_UNIXTIME(paytime,'%Y%m%d')");
			
			
			if(!empty($userlog))
			{
			foreach ($userlog as $k => $valuea) {
					/*start*/
					$datey=date('Y',$valuea['addtime']);
					$datem=date('m',$valuea['addtime']);
					$dated=date('d',$valuea['addtime']);
					$beginToday=mktime(0,0,0,$datem,$dated,$datey);
					$endToday=mktime(0,0,0,$datem,$dated+1,$datey)-1;

					$book=DB::view('ien_book','id,title','ien_book.id=ien_agent.bid')
					->view('ien_agent','id,bid')
					->view('ien_pay_log','id,tgid,paytime','ien_agent.id=ien_pay_log.tgid')
					->whereTime('ien_pay_log.paytime','between',[$beginToday,$endToday])
					->select();
			
					foreach ($book as $k => $valueb) {
								$data['bid']=$valueb['bid'];
								$data['money']=(int)DB::view('ien_agent','id,bid')
								->view('ien_pay_log','id,money','ien_pay_log.tgid=ien_agent.id')
								->whereTime('ien_pay_log.paytime','between',[$beginToday,$endToday])
								->where('ien_pay_log.status','1')
								->where('ien_pay_log.isout','NEQ','1')
								->where('ien_pay_log.paytype','NEQ','0')
								->where('ien_agent.bid',$valueb['bid'])
								->sum('ien_pay_log.money');
								$data['count']=(int)DB::view('ien_agent','id,bid')
								->view('ien_pay_log','id','ien_pay_log.tgid=ien_agent.id')
								->whereTime('ien_pay_log.paytime','between',[$beginToday,$endToday])
								->where('ien_pay_log.status','1')
								->where('ien_pay_log.isout','NEQ','1')
								->where('ien_pay_log.paytype','NEQ','0')
								->where('ien_agent.bid',$valueb['bid'])
								->count('ien_pay_log.id');
								$data['title']=$valueb['title'];
								
								
								$userlog=DB::table("ien_book_log")->whereTime('addtime', 'between',[$beginToday,$endToday])->where('bid',$valueb['bid'])->find();

								if(empty($userlog))
								{	
									$data['addtime']=$beginToday;
									Db::table('ien_book_log')->insert($data);
								}
								else
								{
									$data['addtime']=$beginToday;
									Db::table('ien_book_log')->where('id',$userlog['id'])->update($data);
								}
										/*end*/
														}
					
				}


			}


	}


   


}