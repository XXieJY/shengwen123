<?php
// +----------------------------------------------------------------------
// | 浩森PHP框架 [ IeasynetPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]
// +----------------------------------------------------------------------
// | 官方网站：http://ieasynet.com
// +----------------------------------------------------------------------
// | 5LiK6aW25biC55ub5paH56eR5oqA5pyJ6ZmQ5YWs5Y+4
// +----------------------------------------------------------------------
// | 作者: 拼搏 <378184@qq.com>
// +----------------------------------------------------------------------

namespace app\cms\home;
use think\Db;
use util\Tree;
use think\Session;
/**
 * 前台首页控制器
 * @package app\cms\admin
 */
class Index extends Common
{
    /**
     * 首页
     * @author 拼搏 <378184@qq.com>
     * @return mixed
     */
    public function index($agent=null,$t=null)
    {
		
		session_start();
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(is_weixin())
        {
		//添加点击数
		if($t!="")
		{
		DB::table('ien_agent')->where('id',$t)->setInc('click');
		}

        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }

        //通过地址更新推广ID
       preg_match("/\w([1-9]\d*)/",$_SERVER['HTTP_HOST'],$dlid);
      if(!empty($dlid))
      {
        $agentid=DB::table('ien_agent')->where('uid',$dlid[1])->order('create_time desc')->value('id');
        $user_sxid=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->find();
        if(!empty($agentid) && $dlid[1]!=$user_sxid['sxid'])
        {
            $datasx=['sxid'=>$dlid[1],'tgid'=>$agentid,'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($datasx);
        }
      }

        //更新读者推广ID
        $urla['1']="";   
        if(strpos($_SESSION['target_url'],"?"))
        {
            $url=explode("?",$_SESSION['target_url']);
            $urla=explode("=", $url[1]);
        }
        if(!empty($urla['1']))
        {
          $sxid=DB::table('ien_agent')->where('id',$urla['1'])->find();
          if(empty($sxid))
          {
            $sxid['uid']=0;
          }
        }
        else{$sxid['uid']=0;}
        if($sxid['uid']!=0 && $sxid['uid']!="" && $urla['1']!="")
        {
            $dataagent=['sxid'=>$sxid['uid'],'tgid'=>$urla['1'],'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($dataagent);
        }
        //更新读者推广ID结束
        //$this->oauth($agent,'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]); 
        //$this->getCode("wxfcc9e317d7e4279d","8af792a6ed7ada0e26bd30c212638f49");
		$banner_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '0', tj)")
                ->order('zhishu desc')
                ->select();
        $zhubian_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '1', tj)")
                ->order('zhishu desc')
                ->select();
        $girl_list_1 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '2', tj)")
                ->order('zhishu desc')
                ->select();
        $girl_list_2 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '3', tj)")
                ->order('zhishu desc')
                ->select();
        $boy_list_1 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '4', tj)")
                ->order('zhishu desc')
                ->select();
        $boy_list_2 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
				->where("FIND_IN_SET( '5', tj)")
                ->order('zhishu desc')
                ->select();
        $free_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where('free_stime','<= time',time())
                ->where('free_etime','>= time',time())
                ->order('zhishu desc')
                ->select();

        foreach ($banner_list as $key => $value) {
            $banner_list[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($girl_list_1 as $key => $value) {
            $girl_list_1[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($girl_list_2 as $key => $value) {
            $girl_list_2[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($boy_list_1 as $key => $value) {
            $boy_list_1[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($boy_list_2 as $key => $value) {
            $boy_list_2[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        
        preg_match("/\w([1-9]\d*)/",$_SERVER['HTTP_HOST'],$dlid);
        if(!empty($dlid['1']))
        {
            $dl=DB::table('ien_admin_user')->where('id',$dlid['1'])->find();
            if(!empty($dl['ewm']))
            {
                $ewm=$dl['ewm'];
            }
            else
            {
                $ewm=get_file_path(module_config('cms.support_wx'));
            }
        }
        else
            {
                $ewm=get_file_path(module_config('cms.support_wx'));
            }

        $this->assign('ewm', $ewm);       
        $this->assign('banner_list', $banner_list);        
        $this->assign('zhubian_list', $zhubian_list);        
        $this->assign('girl_list_1', $girl_list_1);        
        $this->assign('girl_list_2', $girl_list_2);
        $this->assign('boy_list_1', $boy_list_1);
        $this->assign('boy_list_2', $boy_list_2);
        $this->assign('free_list', $free_list); 
        $this->assign('userid',$_SESSION['wechat_user']['original']['openid']);

        return $this->fetch(); // 渲染模板
        }
        else{
            ///手机wap登录
            //添加点击数
        if($t!="")
        {
        DB::table('ien_agent')->where('id',$t)->setInc('click');
        }


        //通过地址更新推广ID
       preg_match("/\w([1-9]\d*)/",$_SERVER['HTTP_HOST'],$dlid);
      if(!empty($dlid))
      {
        $agentid=DB::table('ien_agent')->where('uid',$dlid[1])->order('create_time desc')->value('id');
        $user_sxid=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->find();
        if(!empty($agentid) && $dlid[1]!=$user_sxid['sxid'])
        {
            //session保存上线信息
            //$datasx=['sxid'=>$dlid[1],'tgid'=>$agentid,'gzopenid'=>""];
            $_SESSION['sxid']=$dlid[1];
            $_SESSION['tgid']=$agentid;
            $_SESSION['gzopenid']="";

        }
      }

        //更新读者推广ID
        $urla['1']="";   
        if(strpos($_SESSION['target_url'],"?"))
        {
            $url=explode("?",$_SESSION['target_url']);
            $urla=explode("=", $url[1]);
        }
        if(!empty($urla['1']))
        {
          $sxid=DB::table('ien_agent')->where('id',$urla['1'])->find();
          if(empty($sxid))
          {
            $sxid['uid']=0;
          }
        }
        else{$sxid['uid']=0;}
        if($sxid['uid']!=0 && $sxid['uid']!="" && $urla['1']!="")
        {
            ///session保存上线信息
            //$dataagent=['sxid'=>$sxid['uid'],'tgid'=>$urla['1'],'gzopenid'=>""];
            $_SESSION['sxid']=$sxid['uid'];
            $_SESSION['tgid']=$urla['1'];
            $_SESSION['gzopenid']="";
            
        }

        $banner_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '0', tj)")
                ->order('zhishu desc')
                ->select();
        $zhubian_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '1', tj)")
                ->order('zhishu desc')
                ->select();
        $girl_list_1 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '2', tj)")
                ->order('zhishu desc')
                ->select();
        $girl_list_2 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '3', tj)")
                ->order('zhishu desc')
                ->select();
        $boy_list_1 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '4', tj)")
                ->order('zhishu desc')
                ->select();
        $boy_list_2 = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where("FIND_IN_SET( '5', tj)")
                ->order('zhishu desc')
                ->select();
        $free_list = Db::table('ien_book','id,title,image,desc,zhishu,model')
                ->where('status=1')
                ->where('free_stime','<= time',time())
                ->where('free_etime','>= time',time())
                ->order('zhishu desc')
                ->select();

        foreach ($banner_list as $key => $value) {
            $banner_list[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($girl_list_1 as $key => $value) {
            $girl_list_1[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($girl_list_2 as $key => $value) {
            $girl_list_2[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($boy_list_1 as $key => $value) {
            $boy_list_1[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        foreach ($boy_list_2 as $key => $value) {
            $boy_list_2[$key]['desc']=mb_substr($value['desc'],0,45,'utf-8')."...";
        }
        
        preg_match("/\w([1-9]\d*)/",$_SERVER['HTTP_HOST'],$dlid);
        if(!empty($dlid['1']))
        {
            $dl=DB::table('ien_admin_user')->where('id',$dlid['1'])->find();
            if(!empty($dl['ewm']))
            {
                $ewm=$dl['ewm'];
            }
            else
            {
                $ewm=get_file_path(module_config('cms.support_wx'));
            }
        }
        else
            {
                $ewm=get_file_path(module_config('cms.support_wx'));
            }
        $userid=$_SESSION['wechat_user']['original']['openid']?$_SESSION['wechat_user']['original']['openid']:0;

        $this->assign('ewm', $ewm);       
        $this->assign('banner_list', $banner_list);        
        $this->assign('zhubian_list', $zhubian_list);        
        $this->assign('girl_list_1', $girl_list_1);        
        $this->assign('girl_list_2', $girl_list_2);
        $this->assign('boy_list_1', $boy_list_1);
        $this->assign('boy_list_2', $boy_list_2);
        $this->assign('free_list', $free_list); 
        $this->assign('userid',$userid);


        return $this->fetch(); // 渲染模板

        }

    }
     public function footer()
    {
    	return $this->fetch(); // 渲染模板
    }
     public function header()
    {
    	return $this->fetch(); // 渲染模板
    }
     public function booklibrary()
    {   
        $type=parse_attr(module_config('agent.agent_novel_type'));
       
        $i=0;
        foreach ($type as $key => $value) {
            $type1[$i]['id']=$key;
            $type1[$i]['title']=$value;
            $i++;
        }
        $this->assign('type', $type1);
    	return $this->fetch(); // 渲染模板
    }
    public function getuserinfo($uid=null)
    {
        if(empty($uid))
        {
            $data['headimgurl']='http://'.$_SERVER['HTTP_HOST']."/images/user.png";
            $data['nickname']="登录";
            return $data;
        }
        else{
            $userinfo=DB::table('ien_admin_user')->where('openid',$uid)->whereOr('username',$uid)->find();
            $data['headimgurl']=$userinfo['avatar'];
            $data['nickname']=$userinfo['nickname'];
            return $data;
        }
        

    }
    public function getreadlog($uid=null)
    {
        $bookinfo=DB::table('ien_read_log')->where('uid',$uid)->order('update_time desc')->find();
        $cinfo=DB::table('ien_chapter')->where('id',$bookinfo['zid'])->find();

        $data['title']=$cinfo['title'];
        $data['url']='http://'.$_SERVER['HTTP_HOST']."/index.php/cms/document/detail/id/".$cinfo['id'];
        return $data;

    }

}