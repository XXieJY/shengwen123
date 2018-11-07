<?php
// +----------------------------------------------------------------------
// | 浩森PHP框架 [ IeasynetPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]
// +----------------------------------------------------------------------
// | 官方网站：http://ieasynet.com
// +----------------------------------------------------------------------
// | b363c3ddb38a38e6c4fd7653fdc3cf00
// +----------------------------------------------------------------------
// | 作者: 拼搏 <378184@qq.com>
// +----------------------------------------------------------------------

namespace app\cms\home;

use app\cms\model\Column as ColumnModel;
use think\Db;
use util\Tree;

/**
 * 前台栏目文档列表控制器
 * @package app\cms\admin
 */
class Column extends Common
{
    /**
     * 栏目文章列表
     * @param null $id 栏目id
     * @author 拼搏 <378184@qq.com>
     * @return mixed
     */
    public function index($lid=null,$tstype=null,$gxid=null)
    { 
        
        $book= Db::table('ien_cms_column')->where('id', $id)->value('name');
        $this->assign('book', $book);
        $this->assign('id', $id);
		    $this->assign('tstype', $tstype);
       
        return $this->fetch("list_book");

    }
    public function bookfree($lid=null,$tstype=null,$gxid=null)
    { 
        
        $book= Db::table('ien_cms_column')->where('id', $id)->value('name');
        $this->assign('book', $book);
        $this->assign('id', $id);
        $this->assign('tstype', $tstype);
       
        return $this->fetch("list_bookfree");

    }
	
	
	//小说ajax
    public function doajax($start=null,$limit=null,$status=null,$gender=null,$category=null)
    {
        if(empty($start))
        {
          $start=0;
        }  
        if(empty($limit))
        {
          $limit=20;
        }  
        switch ($status) {
          case 'ongoing':
            $map['xstype']=0;
            break;
          case 'completed':
            $map['xstype']=1;
            break;
          case 'free':
            $map['isfree']=1;
            $map['free_stime']=['<=',time()];
            $map['free_etime']=['>=',time()];
            break;
          
          default:
            break;
        }
        if(!empty($category))
        {
          $map['tstype']=$category;
        }
        switch ($gender) {
          case 'female':
            $map['cid']=3;
            break;
          case 'male':
            $map['cid']=2;
            break;

          default:
            
            break;
        }
        $map['status']=1;
        $booklist=DB::table('ien_book')->where($map)->limit($start,$limit)->order('zhishu desc,update_time desc')->select();
        $count=DB::table('ien_book')->where($map)->order('zhishu desc,update_time desc')->count();
        $data_list['data']=[];
        foreach ($booklist as $key => $value) {
          $data_list['data'][$key]['article_count']=837;
          $data_list['data'][$key]['avatar']=get_file_path($value['image']);
          $data_list['data'][$key]['first_article_id']=0;
          if($value['isfree']=1 && $value['free_stime']<=time() && $value['free_etime']>=time())
          {
          $data_list['data'][$key]['free_time_end']=$value['free_etime'];
          $data_list['data'][$key]['free_time_start']=$value['free_stime'];
          $data_list['data'][$key]['is_time_limited_free']=true;
          }
          else
          {
          $data_list['data'][$key]['free_time_end']=0;
          $data_list['data'][$key]['free_time_start']=0;
          $data_list['data'][$key]['is_time_limited_free']=false;
          }
          $data_list['data'][$key]['gender']=$gender;
          $data_list['data'][$key]['id']=$value['id'];
          $data_list['data'][$key]['status']=$status;
          $data_list['data'][$key]['summary']=$value['desc'];
          $data_list['data'][$key]['tip_welth_sum']=0;
          $data_list['data'][$key]['title']=$value['title'];
          $data_list['data'][$key]['words']=$value['zishu'];
         
        }
        $data_list['total']=$count;

        $data_list=json_decode(json_encode($data_list),true);

        return  $data_list;
    }

    //小说ajax
    public function doajaxfree($start=null,$limit=null,$status=null,$gender=null,$category=null)
    {
        if(empty($start))
        {
          $start=0;
        }  
        if(empty($limit))
        {
          $limit=20;
        }  
        
        $map['isfree']=1;
        $map['free_stime']=['<=',time()];
        $map['free_etime']=['>=',time()];
        
        
        $map['status']=1;
        $booklist=DB::table('ien_book')->where($map)->limit($start,$limit)->order('zhishu desc,update_time desc')->select();
        $count=DB::table('ien_book')->where($map)->order('zhishu desc,update_time desc')->count();
        $data_list['data']=[];
        foreach ($booklist as $key => $value) {
          $data_list['data'][$key]['article_count']=837;
          $data_list['data'][$key]['avatar']=get_file_path($value['image']);
          $data_list['data'][$key]['first_article_id']=0;
          if($value['isfree']=1 && $value['free_stime']<=time() && $value['free_etime']>=time())
          {
          $data_list['data'][$key]['free_time_end']=$value['free_etime'];
          $data_list['data'][$key]['free_time_start']=$value['free_stime'];
          $data_list['data'][$key]['is_time_limited_free']=true;
          }
          else
          {
          $data_list['data'][$key]['free_time_end']=0;
          $data_list['data'][$key]['free_time_start']=0;
          $data_list['data'][$key]['is_time_limited_free']=false;
          }
          $data_list['data'][$key]['gender']=$gender;
          $data_list['data'][$key]['id']=$value['id'];
          $data_list['data'][$key]['status']=$status;
          $data_list['data'][$key]['summary']=$value['desc'];
          $data_list['data'][$key]['tip_welth_sum']=0;
          $data_list['data'][$key]['title']=$value['title'];
          $data_list['data'][$key]['words']=$value['zishu'];
         
        }
        $data_list['total']=$count;

        $data_list=json_decode(json_encode($data_list),true);

        return  $data_list;
    }

    public function doajaxi(){
      $data['data']=[];
      return json_decode(json_encode($data),true);
    }
	
	
	 public function indexidx($bid=null)
    { 
        if ($bid === null) $this->error('缺少参数');
       
        $book= Db::table('ien_book')->where('id', $bid)->find();

        if($book['isfree']=1 && $book['free_stime']<=time() && $book['free_etime']>=time())
        {
          $free='true';
        }
        else
        {
          $free='false';
        }
        session_start();
        $user=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->value('isguanzhu');
        if($user==1)
        {
          $isguanzhu='true';
        }
        else
        {
          $isguanzhu='false';
        }
        //查询小说强制关注章节
        preg_match("/[1-9]\d*/",$_SERVER['HTTP_HOST'],$dlid);
        if(!empty($dlid['0']))
        {
            $dl=DB::table('ien_admin_user')->where('id',$dlid['0'])->find();
            if(!empty($dl['guanzhu']) && $dl['guanzhu']!=0)
            {
                $chapteridx=$dl['guanzhu'];
            }
            else
            {
                if(!empty($book['gzzj']) && $book['gzzj']!=0)
                {
                   $chapteridx=$book['gzzj'];
                }
                else
                {
                    $chapteridx=module_config('agent.agent_guanzhu');
                }

            }

            //关注模式
            $gzmoshi=DB::table('ien_wechat_uconfig')->where('uid',$dlid['0'])->value('gzmoshi');
            if(!empty($gzmoshi) && $gzmoshi!=0)
            {
              $moshi='true';
            }
            else
            {
              $moshi='false';
            }

        }
        else
        {
          $sxid=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->value('sxid');
          $dl=DB::table('ien_admin_user')->where('id',$sxid)->find();
            if(!empty($dl['guanzhu']) && $dl['guanzhu']!=0)
            {
                $chapteridx=$dl['guanzhu'];
            }
            else
            {
                if(!empty($book['gzzj']) && $book['gzzj']!=0)
                {
                   $chapteridx=$book['gzzj'];
                }
                else
                {
                    $chapteridx=module_config('agent.agent_guanzhu');
                }

            }

            //关注模式
            $gzmoshi=DB::table('ien_wechat_uconfig')->where('uid',$sxid)->value('gzmoshi');
            if(!empty($gzmoshi) && $gzmoshi!=0)
            {
              $moshi='true';
            }
            else
            {
              $moshi='false';
            }

        }



        $this->assign('book', $book);
        $this->assign('chapteridx', $chapteridx);
        $this->assign('moshi', $moshi);
        $this->assign('isguanzhu', $isguanzhu);
        $this->assign('free', $free);
        $this->assign('bid', $bid);
       
        return $this->fetch("list_chapter");

    }
	
		//小说ajax
    public function doajaxidx($bid = null , $start = null)
    {
        if ($bid === null) $this->error('缺少参数');
		
		    $map['bid']=$bid;

        if($start!="")
         {$data_list=DB::table('ien_chapter')->where($map)->limit($start,20)->order('idx asc')->select();
		}
        else{
		 $data_list=DB::table('ien_chapter')->where($map)->limit($start,20)->order('idx asc')->select();}

  		foreach($data_list as $key=>$value)
		{
			$data['data']['catalog'][$key]['id']=(string)$value['id'];
			$data['data']['catalog'][$key]['title']=$value['title'];
			$data['data']['catalog'][$key]['idx']=$value['idx'];
      if($value['isvip']==1)
      {
        $bookmoney= Db::table('ien_book')->where('id', $bid)->value('score');
        if(!empty($bookmoney) && $bookmoney!=0)
        {
        $data['data']['catalog'][$key]['welth']=$bookmoney;
        }
        else
        {
        $data['data']['catalog'][$key]['welth']=module_config('agent.agent_pay_money');
        }
      }
      else
      {
			$data['data']['catalog'][$key]['welth']="0";
      }
		}
		//$data['data']=array_slice($data['data'],$start,$limit);
        $data_list=json_decode(json_encode($data),true);
        
 // var_dump($data_list);
        return  $data_list;
    }
	

    /**
     * 获取栏目面包屑导航
     * @param int $id
     * @author 拼搏 <378184@qq.com>
     */
    public function getBreadcrumb($id)
    {
        $columns = ColumnModel::where('status', 1)->column('id,pid,name,url,target,type');
        foreach ($columns as &$column) {
            if ($column['type'] == 0) {
                $column['url'] = url('cms/column/index', ['id' => $column['id']]);
            }
        }

        return Tree::config(['title' => 'name'])->getParents($columns, $id);
    }

    public function book($id=null)
    {
      if ($id === null) $this->error('缺少参数');
      $book=DB::table('ien_book')->where('id',$id)->find();
      $chapter=DB::table('ien_chapter')->where('bid',$id)->order('idx asc')->limit('5')->select();

      $this->assign('book', $book);
      $this->assign('chapter', $chapter);
      return $this->fetch("list_bookinfo");
    }
    public function tips($id=null,$page=null)
    {
      if(empty($page) || $page==1)
      {
        $start=0;
        $end=5;
      }
      else
      {
        $start=5*($page-1);
        $end=$start+5;
      }
      $gifts = '[{"id":1,"title":"\u9c9c\u82b1","img_url":"\/public\/static\/cms\/img\/gift_1.jpg","wealth":100},{"id":2,"title":"\u638c\u58f0","img_url":"\/public\/static\/cms\/img\/gift_2.jpg","wealth":388},{"id":3,"title":"\u94bb\u6212","img_url":"\/public\/static\/cms\/img\/gift_3.jpg","wealth":588},{"id":4,"title":"\u6e38\u8f6e","img_url":"\/public\/static\/cms\/img\/gift_4.jpg","wealth":888}]';
      $gifts=json_decode($gifts,true);
      
      $tips=DB::table('ien_tips')->where('bid',$id)->order('addtime desc')->limit($start,$end)->select();
      
      foreach ($tips as $key => $value) {
        $data['data'][$key]['created_at']=$value['addtime'];

        switch ($value['money']) {
          case '100':
            $data['data'][$key]['gift_id']=$gifts['0']['id'];
            $data['data'][$key]['gift_img_url']=$gifts['0']['img_url'];
            $data['data'][$key]['gift_name']=$gifts['0']['title'];
            $data['data'][$key]['gift_wealth']=100;
            break;
          case '388':
            $data['data'][$key]['gift_id']=$gifts['1']['id'];
            $data['data'][$key]['gift_img_url']=$gifts['1']['img_url'];
            $data['data'][$key]['gift_name']=$gifts['1']['title'];
            $data['data'][$key]['gift_wealth']=388;
            break;
          case '588':
            $data['data'][$key]['gift_id']=$gifts['2']['id'];
            $data['data'][$key]['gift_img_url']=$gifts['2']['img_url'];
            $data['data'][$key]['gift_name']=$gifts['2']['title'];
            $data['data'][$key]['gift_wealth']=588;
            break;
          case '888':
            $data['data'][$key]['gift_id']=$gifts['3']['id'];
            $data['data'][$key]['gift_img_url']=$gifts['3']['img_url'];
            $data['data'][$key]['gift_name']=$gifts['3']['title'];
            $data['data'][$key]['gift_wealth']=888;
            break;
          
          default:
            $data['data'][$key]['gift_id']=$gifts['3']['id'];
            $data['data'][$key]['gift_img_url']=$gifts['3']['img_url'];
            $data['data'][$key]['gift_name']=$gifts['3']['title'];
            $data['data'][$key]['gift_wealth']=888;
            break;
        }
        

        $user=DB::table('ien_admin_user')->where('openid',$value['openid'])->find();
        
        if(!empty($user))
        {
        $data['data'][$key]['headimgurl']=$user['avatar'];
        $data['data'][$key]['member_id']=$user['id'];
        $data['data'][$key]['nickname']=$user['nickname'];
        }
        else
        {
        $data['data'][$key]['headimgurl']="http://".module_config('agent.agent_rooturl')."/images/homeuser.png";
        $data['data'][$key]['member_id']=1;
        $data['data'][$key]['nickname']="匿名用户";
        }
        $data['data'][$key]['id']=$value['id'];
        $data['data'][$key]['novel_id']=$value['bid'];
      }


      return $data;

    }


}