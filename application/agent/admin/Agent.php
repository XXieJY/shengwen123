<?php
/**
 * User: pinbo
 * Date: 2017/4/8
 * Time: 上午9:50
 * c08129327b9fa0a4bc29b4f070c9877e
 */
//后台访问控制器
namespace app\agent\admin;//修改ann为模块名

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\agent\model\Novel as NovelModel;
use app\agent\model\Agent as AgentModel;
use think\Db;

class Agent extends Admin{
	
	function index($group = 'tab1'){
		$list_tab = [
        'tab1' => ['title' => '推广链接', 'url' => url('index', ['group' => 'tab1'])],
        'tab2' => ['title' => '群推广链接', 'url' => url('index', ['group' => 'tab2'])],
        ];
        switch ($group) {
        case 'tab1':
		$map  = $this->getMap();
		$map['uid']=UID;
		$map['isqun']=0;
		$usercount="";
		$usermoney="";
		//单独拼接组合查询
        $data_list = AgentModel::where($map)->order('id desc')->paginate();
        $agentuser = DB::table('ien_admin_user')->where('id',UID)->find();
		$data=array();
		foreach($data_list as $key=>$value)
		{
			$data_list[$key]['click']=$value['click'];
			$data_list[$key]['name']=$value['name'];
			$data_list[$key]['id']=$value['id'];
			if($value['ljlx']==3)
			{

			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/index/index/?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}
			}

			else{
			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$value['zid'].".html?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}

			}
			$data_list[$key]['attention_count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->where('isguanzhu',1)->count();
			$data_list[$key]['count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->count();
			$data_list[$key]['url']=$data_list[$key]['url'].'<button class="btn btn-xs btn-default" data-clipboard-text="'.$data_list[$key]['url'].'">复制</button>';
			$data_list[$key]['money']=DB::view('ien_admin_user')
			->where('ien_pay_log.tgid',$value['id'])
			->view('ien_pay_log','money','ien_admin_user.openid=ien_pay_log.uid')
			->where('ien_pay_log.status',1)
            ->where('ien_pay_log.isout',0)
			->sum('ien_pay_log.money');
			$chapter=DB::table('ien_chapter')->where('id',$value['zid'])->find();
			$chapter['content']=str_replace("\n", "</p><p>", str_replace(" ", "", $chapter['content']));
			$data_list[$key]['chaptername']=$chapter['title'];
			$book=DB::table('ien_book')->where('id',$chapter['bid'])->find();
			$data_list[$key]['bookname']=$book['title'];
			if($book['gzzj']!=0)
			{
				$data_list[$key]['gzzj']=$book['gzzj'];
			}
			else
			{
				if($agentuser['guanzhu']!="" && $agentuser['guanzhu']!=0)
				{
					$data_list[$key]['gzzj']=$agentuser['guanzhu'];
				}
				else
				{
					$data_list[$key]['gzzj']=module_config("agent.agent_guanzhu");
				}
			}
			
			}
			
			//自定义JS 复制到剪切板
      	    $myjs = <<<EOF
            <script type="text/javascript">
              var clipboard =  new Clipboard('.btn');
               clipboard.on('success', function(e) {
            	alert("复制成功");
        	});

        	clipboard.on('error', function(e) {
                alert("复制失败");
       		});
            </script>
EOF;
		
		// 自定义按钮
            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '添加首页推广链接',
                'href'  => url('agent/addindex')
            ];
			 $btnorder = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-shopping-cart',
                'title' => '订单明细',
                'href'  => url('agent/order', ['id' => '__id__'])
            ];
			 $btnwxedit = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-edit',
                'title' => '文字文案',
                'href'  => url('agent/wxedit', ['id' => '__id__']),
				'target' => '_blank',
            ];
            $btnwxeditimg = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-edit',
                'title' => '图片文案',
                'href'  => url('agent/wxedit', ['id' => '__id__','mode'=>'image']),
				'target' => '_blank',
            ];

            $btnoutexcel = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '数据导出',
				'href'  => url('agent/export'),
				
            ];

             $css = <<<EOF
           <style>
               .column-url { max-width:270px;}
           </style>
EOF;

		return ZBuilder::make('table')
			
			->hideCheckbox()
			->setTabNav($list_tab,  $group)
            ->addColumns([ // 批量添加数据列

				['mingcheng','推广名称','callback', function($data_list){
    	return "<span style='color:#337AB7'>推广名称:".$data_list['name']."</span><br>创建时间:".date('Y-m-d H:i:s',$data_list['create_time']); // 可以用$data接收到其他字段的数据
    }, '__data__'],
				['url','入口页面'],
                ['click','点击'],
				['yonghu','用户数','callback', function($data_list){
    	return "<span style='color:#337AB7'>注册用户数:".$data_list['count']."</span><br>关注用户数:".$data_list['attention_count']; 
    }, '__data__'],
				['money','充值金额'],
				['lirun','推广成本','callback', function($data_list){
    	return "<span style='color:#337AB7'>推广成本:".$data_list['tgcb']."</span><br>推广利润:".($data_list['money']-$data_list['tgcb']); 
    }, '__data__'],
				['rukou','入口章节','callback', function($data_list){
    	return "<span style='color:#337AB7'>".$data_list['bookname']."</span><Br>".$data_list['chaptername']."<br><span style='color:#337AB7'>关注章节: 第".$data_list['gzzj']."章</span>"; // 可以用$data接收到其他字段的数据
    }, '__data__'],
				['right_button', '操作', 'btn']

            ])
			->setSearch(['name' => '推广名称'])
			->addTimeFilter('create_time')
			->addTopButton('custom',$btnindex)
			->addTopButton('custom',$btnoutexcel)
			->addRightButton('custom',$btnorder)
			->addRightButton('custom',$btnwxedit)
			->addRightButton('custom',$btnwxeditimg)
			->addRightButton('edit')
			->addRightButton('delete')
            ->setRowList($data_list) // 设置表格数据
            ->js('clipboard')
          	->setExtraJs($myjs)
          	->setExtraCss($css)
            ->fetch(); // 渲染模板
		   break;

        case 'tab2':
        $map  = $this->getMap();
		$map['uid']=UID;
		$map['isqun']=1;
		$usercount="";
		$usermoney="";
		//单独拼接组合查询
        $data_list = AgentModel::where($map)->order('id desc')->paginate();
        $agentuser = DB::table('ien_admin_user')->where('id',UID)->find();
		$data=array();
		foreach($data_list as $key=>$value)
		{
			$data_list[$key]['click']=$value['click'];
			$data_list[$key]['name']=$value['name'];
			$data_list[$key]['id']=$value['id'];
			if($value['ljlx']==3)
			{

			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/index/index/?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}
			}

			else{
			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$value['zid'].".html?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}

			}
			$data_list[$key]['attention_count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->where('isguanzhu',1)->count();
			$data_list[$key]['count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->count();
			$data_list[$key]['url']=$data_list[$key]['url'].'<button class="btn btn-xs btn-default" data-clipboard-text="'.$data_list[$key]['url'].'">复制</button>';
			$data_list[$key]['money']=DB::view('ien_admin_user')
			->where('ien_pay_log.tgid',$value['id'])
			->view('ien_pay_log','money','ien_admin_user.openid=ien_pay_log.uid')
			->where('ien_pay_log.status',1)
            ->where('ien_pay_log.isout',0)
			->sum('ien_pay_log.money');
			$chapter=DB::table('ien_chapter')->where('id',$value['zid'])->find();
			$data_list[$key]['chaptername']=$chapter['title'];
			$book=DB::table('ien_book')->where('id',$chapter['bid'])->find();
			$data_list[$key]['bookname']=$book['title'];
			if($book['gzzj']!=0)
			{
				$data_list[$key]['gzzj']=$book['gzzj'];
			}
			else
			{
				if($agentuser['guanzhu']!="" && $agentuser['guanzhu']!=0)
				{
					$data_list[$key]['gzzj']=$agentuser['guanzhu'];
				}
				else
				{
					$data_list[$key]['gzzj']=module_config("agent.agent_guanzhu");
				}
			}
			
			}
			
			//自定义JS 复制到剪切板
      	    $myjs = <<<EOF
            <script type="text/javascript">
              var clipboard =  new Clipboard('.btn');
               clipboard.on('success', function(e) {
            	alert("复制成功");
        	});

        	clipboard.on('error', function(e) {
                alert("复制失败");
       		});
            </script>
EOF;
		
		// 自定义按钮
            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '添加首页推广链接',
                'href'  => url('agent/addindex')
            ];
			 $btnorder = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-shopping-cart',
                'title' => '订单明细',
                'href'  => url('agent/order', ['id' => '__id__'])
            ];
			 $btnwxedit = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-edit',
                'title' => '文字文案',
                'href'  => url('agent/wxedit', ['id' => '__id__']),
				'target' => '_blank',
            ];
            $btnwxeditimg = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-edit',
                'title' => '图片文案',
                'href'  => url('agent/wxedit', ['id' => '__id__','mode'=>'image']),
				'target' => '_blank',
            ];

            $btnoutexcel = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '数据导出',
				'href'  => url('agent/export'),
				
            ];

             $css = <<<EOF
           <style>
               .column-url { max-width:270px;}
           </style>
EOF;

		return ZBuilder::make('table')
			
			->hideCheckbox()
			->setTabNav($list_tab,  $group)
            ->addColumns([ // 批量添加数据列

				['mingcheng','推广名称','callback', function($data_list){
    	return "<span style='color:#337AB7'>推广名称:".$data_list['name']."</span><br>创建时间:".date('Y-m-d H:i:s',$data_list['create_time']); // 可以用$data接收到其他字段的数据
    }, '__data__'],
				['url','入口页面'],
                ['click','点击'],
				['yonghu','用户数','callback', function($data_list){
    	return "<span style='color:#337AB7'>注册用户数:".$data_list['count']."</span><br>关注用户数:".$data_list['attention_count']; 
    }, '__data__'],
				['money','充值金额'],
				['lirun','推广成本','callback', function($data_list){
    	return "<span style='color:#337AB7'>推广成本:".$data_list['tgcb']."</span><br>推广利润:".($data_list['money']-$data_list['tgcb']); 
    }, '__data__'],
				['rukou','入口章节','callback', function($data_list){
    	return "<span style='color:#337AB7'>".$data_list['bookname']."</span><Br>".$data_list['chaptername']."<br><span style='color:#337AB7'>关注章节: 第".$data_list['gzzj']."章</span>"; // 可以用$data接收到其他字段的数据
    }, '__data__'],
				['right_button', '操作', 'btn']

            ])
			->setSearch(['name' => '推广名称'])
			->addTimeFilter('create_time')
			//->addTopButton('custom',$btnindex)
			->addTopButton('custom',$btnoutexcel)
			->addRightButton('custom',$btnorder)
			->addRightButton('custom',$btnwxedit)
			->addRightButton('custom',$btnwxeditimg)
			->addRightButton('edit')
			->addRightButton('delete')
            ->setRowList($data_list) // 设置表格数据
            ->js('clipboard')
          	->setExtraJs($myjs)
          	->setExtraCss($css)
            ->fetch(); // 渲染模板

        break;
    	}
		
		
		}
		//微信文案创建
	function wxcreate($id = null,$mode=null){

		///更新sqlend
		if ($id === 0) $this->error('参数错误');
		$nextid=$this->nextchapter($id);
		$leixing=$this->getleixing($id);
		$title=$this->getbook($id);
		$this->assign('title', $title);
		$this->assign('id', $id);
		$this->assign('nextid', $nextid);
		$this->assign('leixing', $leixing);
		//$ismanhua=DB::table()
		if($title['ismanhua']==1)
		{
			return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号'],1],
                ['text', 'tgcb', '推广成本', '必填'],
                ['hidden', 'uid', UID],
                ['hidden', 'zid', $id],
                ['hidden', 'ljlx', 1],
                ['hidden', 'titleid',0],
                ['hidden', 'imageid',0],
				['hidden', 'tempid',0],
                ['hidden', 'footid',0],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])
			//->addStatic('', '当前小说章节', '', $data_listxs['title'])
			->isAjax(true)
            ->fetch('wxcreatemanhua');
		}
		else{
		if($mode=="image")
		{
		return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号'],1],
                ['text', 'tgcb', '推广成本', '必填'],
                ['hidden', 'uid', UID],
                ['hidden', 'zid', $id],
                ['hidden', 'ljlx', 1],
                ['hidden', 'titleid',0],
                ['hidden', 'imageid',0],
				['hidden', 'tempid',0],
                ['hidden', 'footid',0],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])
			//->addStatic('', '当前小说章节', '', $data_listxs['title'])
			->isAjax(true)
            ->fetch('wxcreateimg');
        }
        else{
        	return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号'],1],
                ['text', 'tgcb', '推广成本', '必填'],
                ['hidden', 'uid', UID],
                ['hidden', 'zid', $id],
                ['hidden', 'ljlx', 1],
                ['hidden', 'titleid',0],
                ['hidden', 'imageid',0],
				['hidden', 'tempid',0],
                ['hidden', 'footid',0],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])
			//->addStatic('', '当前小说章节', '', $data_listxs['title'])
			
			->isAjax(true)
            ->fetch('wxcreate');
        }

    }



		}
	//ajax 下一章
	function nextchapter($id = null){
		if ($id === 0) $this->error('参数错误');
		$chapter=DB::table('ien_chapter')->where('id',$id)->find();
		$chapternextidx=$chapter['idx']+1;
		$data=Db::query('select id from ien_chapter where bid='.$chapter['bid'].' and idx='.$chapternextidx);
		$data['content']=str_replace("\n", "</p><p>", str_replace(" ", "", $data['content']));
		return $data;
		}
	//ajax 类型查询
	function getleixing($id = null){
		if ($id === 0) $this->error('参数错误');
		$chapter=DB::table('ien_chapter')->where('id',$id)->find();
		$data=DB::table('ien_book')->where('id',$chapter['bid'])->find();
		return $data;
		}
		
	//ajax保存文案推广链接
	function savewa($id=null,$type=null,$article_id=null,$referrer_type=null,$force_follow_chapter_idx=null,$description=null,$tgcb=null,$wx_article_title_id=null,$wx_article_cover_id=null,$wx_article_body_template_id=null,$wx_article_footer_template_id=null){
	
		$data['uid']=UID;
		$data['zid']=$article_id;
		$data['titleid']=$wx_article_title_id;
		$data['imageid']=$wx_article_cover_id;
		$data['tempid']=$wx_article_body_template_id;
		$data['footid']=$wx_article_footer_template_id;
		$data['name']=$description;
		$data['gzh']=$referrer_type;
		$data['create_time']=time();	
		$data['update_time']=time();	
		$data['ljlx']=2;
		$data['tgcb']=$tgcb;
		$bookid=DB::table('ien_chapter')->where('id',$article_id)->column('bid');
		$data['bid']=$bookid['0'];
		$res['id']=DB::table('ien_agent')->insertGetId($data);
		$res['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$data['zid'].".html?t=".$res['id'];

		if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$res['url']);
					foreach (json_decode(json_encode($shorturl->url->url_short),true) as $k => $v)
					{
					$res['url']=$v;
					}
				}
		

		if($res['id'])
		{
			return $res;
			}
		else{return false;}
		
		}
	//ajax 获取推广记录-没写完
	function getagent($id=null){
		if ($id === 0) $this->error('参数错误');
		$map['id']=$id;
		$data=DB::table('ien_agent')->where($map)->find();
		return $data;
		}
		
	//ajax 上一章id
	function getpre($id)
	{
		if ($id === 0) $this->error('参数错误');
		$map['id']=$id;
		$data=DB::table('ien_chapter')->where($map)->find();
		$idx=$data['idx']-1;
		$mapa['idx']=$idx;
		$mapa['bid']=$data['bid'];
		$res=DB::table('ien_chapter')->where($mapa)->find();
        $res['content']=str_replace("\n", "</p><p>", str_replace(" ", "", $res['content']));

        return $res;
		}	
	//微信文案编辑
	function wxedit($id=null,$mode=null)
	{
		if ($id === 0) $this->error('参数错误');
		$agent=DB::table('ien_agent')->where('id',$id)->find();
		$this->assign('agent', $agent);
		$preactirle=$this->getpre($agent['zid']);
		$this->assign('preactirle', $preactirle);
		$this->assign('id', $id);
		$book=DB::table('ien_book')->where('id',$preactirle['bid'])->find();
		$this->assign('book', $book);
		$url="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$agent['zid'].".html?t=".$id;
		if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$url);
					$url=$shorturl->url->url_short;
				}
		
		$this->assign('url', $url);
		if($book['ismanhua']==1)
		{
			return ZBuilder::make('form')
	            ->addFormItems([
	                ['text', 'name', '派单渠道名称', '必填'],
	            ])
				//->addStatic('', '当前小说章节', '', $data_listxs['title'])
				
				->isAjax(true)
	            ->fetch('wxeditmanhua');
		}
		else
		{
			if($mode=="image")
			{
				return ZBuilder::make('form')
	            ->addFormItems([
	                ['text', 'name', '派单渠道名称', '必填'],
	            ])
				//->addStatic('', '当前小说章节', '', $data_listxs['title'])
				
				->isAjax(true)
	            ->fetch('wxeditimg');
			}
			else{
				return ZBuilder::make('form')
	            ->addFormItems([
	                ['text', 'name', '派单渠道名称', '必填'],
	            ])
				//->addStatic('', '当前小说章节', '', $data_listxs['title'])
				
				->isAjax(true)
	            ->fetch('wxedit');
			}
		}
		
		}
		
	//ajax 调用标题
	function gettitle($id = null)
	{
		//if ($id === 0) $this->error('参数错误');
		$leixing=$this->getleixing($id);
		$map['fenlei']=0;
		$map['leixing']=$id;
		$data=DB::table('ien_fodder')->where($map)->select();
		foreach($data as $key=>$value )
		{
			$datab[$key]['id']=$value['id'];
			$datab[$key]['category_id']=$value['leixing'];
			$datab[$key]['title']=$value['title'];
			$datab[$key]['created_at']=$value['create_time'];
		}
		return $datab;
		}
	
	//ajax 调用图片
	function getimage($id = null){
		//if ($id == "") $this->error('参数错误');
		//$leixing=$this->getleixing($id);
		$map['fenlei']=1;
		$map['leixing']=$id;
		$data=DB::table('ien_fodder')->where($map)->select();
		foreach($data as $key=>$value )
		{
			$datab[$key]['id']=$value['id'];
			$datab[$key]['category_id']=$value['leixing'];
			$datab[$key]['cover_url']="http://".$_SERVER['SERVER_NAME']."/public/static/agent/image/".$value['title'];
			$datab[$key]['created_at']=$value['create_time'];
		}
		return $datab;
		
		
		}

	//ajax api_detect_snapshot
	function api_detect_snapshot(){
		$code=$_POST['code'];

		//ien_blob
		$filename=DB::table('ien_blob')->where('title',$code)->value('title');

		if(!$filename)
		{
			//$data['payload']="http://novel.ieasynet.net/admin.php/Agent/Agent/getblob/title/".$code;
			$data['url']=false;
		}
		else
		{
			$data['url']="http://". module_config('agent.agent_rooturl')."/index.php/cms/articles/getblob/title/".$code;
		}
		return $data;

	}
	
	//ajax api_upload_snapshot
	function api_upload_snapshot(){
		if(count($_FILES) > 0) {  
		    if(is_uploaded_file($_FILES['snapshot']['tmp_name'])) {
		    // 转成二进制
		    $imgBlob =file_get_contents($_FILES['snapshot']['tmp_name']);  
			}  
		}
		$filename=DB::table('ien_blob')->where('title',$_POST['code'])->value('title');
		if(empty($filename))
		{
		DB::table('ien_blob')->insert(['title'=>$_POST['code'],'content'=>$imgBlob]);
		}
		$res['url']="http://". module_config('agent.agent_rooturl')."/index.php/cms/articles/getblob/title/".$_POST['code'];
		return $res;

	}
	function api_generate_qrcode_referral_image($referral_id=null,$template_id=null){


		$filename="qrcode_".UID."_".$referral_id."_".$template_id.".png";
		$filenameewm="qrcode_".UID."_".$referral_id."_".$template_id.".ewm.png";
		$issave=glob("qrcode/".$filename);
		if(!$issave)
		{
			$agent=DB::table('ien_agent')->where('id',$referral_id)->find();
			$url="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$agent['zid'].".html?t=".$referral_id;
			$dst_path="public/static/agent/img/qr-footer".$template_id.".png";

				$ewm="http://qr.liantu.com/api.php?text=".$url."&w=196";
				$src_path = 'qrcode/'.$filenameewm;  
		        $targetName = 'qrcode/'.$filenameewm;  
		        $ch = curl_init();  
		        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');  
		        $fp = fopen($targetName,'wb');  
		        curl_setopt($ch,CURLOPT_URL,$ewm);  
		        curl_setopt($ch,CURLOPT_FILE,$fp);  
		        curl_setopt($ch,CURLOPT_HEADER,0);  
		        curl_exec($ch);  
		        curl_close($ch);  
		        fclose($fp);
				//创建图片的实例

				$dst = imagecreatefromstring(file_get_contents($dst_path));
				$src = imagecreatefromstring(file_get_contents($src_path));

				//获取水印图片的宽高
				list($src_w, $src_h) = getimagesize($src_path);
				//将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
				imagecopymerge($dst, $src, 355, 18, 0, 0, 196, 196,100);
				//如果水印图片本身带透明色，则使用imagecopy方法
				//imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
				//输出图片
				list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);

				
				switch ($dst_type) {
				    case 1://GIF
				        header('Content-Type: image/gif');
				        imagegif($dst,'qrcode/'.$filename);
				        break;
				    case 2://JPG
				        header('Content-Type: image/jpeg');
				        imagejpeg($dst,'qrcode/'.$filename);
				        break;
				    case 3://PNG
				        header('Content-Type: image/png');
				        imagepng($dst,'qrcode/'.$filename);
				        break;
				    default:
				        break;
				}
				imagedestroy($dst);
				imagedestroy($src);
		}

		$data[url]="http://".$_SERVER['SERVER_NAME']."/qrcode/".$filename;
		return $data;
	}
	//ajax二维码
	function getqrcode(){
		$data[0]['id']=1;
		$data[0]['preview_img']="http://".$_SERVER['SERVER_NAME']."/public/static/agent/img/qr-footer1-preview.png";
		$data[0]['template']="<img style='max-width:100% src='http://".$_SERVER['SERVER_NAME']."/public/static/agent/img/qr-footer1.png'/>";
		$data[1]['id']=2;
		$data[1]['preview_img']="http://".$_SERVER['SERVER_NAME']."/public/static/agent/img/qr-footer2-preview.png";
		$data[1]['template']="<img style='max-width:100% src='http://".$_SERVER['SERVER_NAME']."/public/static/agent/img/qr-footer2.png'/>";
		return $data;
	}
	//ajax获取小说信息
	function getbook($id = null)
	{
		if ($id === 0) $this->error('参数错误');
		$map['id']=$id;
		$data=DB::table('ien_chapter')->where($map)->find();
		$mapb['id']=$data['bid'];
		$datab=DB::table('ien_book')->where($mapb)->find();
		return $datab;
	}
	
	//ajax 文章信息
	function getactirle($id = null){
		if ($id === 0) $this->error('参数错误');
		$book=$this->getbook($id);
		$image="http://".$_SERVER['SERVER_NAME'].get_thumb($book['image']);
		$chapter=DB::table('ien_chapter')->where('id',$id)->find();
		$data=array('id'=>$id,'title'=>$chapter['title'],'novel'=>array('id'=>$book['id'],'title'=>$book['title'],'avatar'=>$image));
		return json($data);
		
		
		}
	
	
	//ajax 调用底部
	function getfooter()
	{
		$map['fenlei']=3;
		$data=DB::table('ien_fodder')->where($map)->select();
		foreach($data as $key=>$value )
		{
			$datab[$key]['id']=$value['title'];
			$datab[$key]['preview_img'] =  "http://".$_SERVER['SERVER_NAME']."/public/static/agent/image/".$value['title'].".jpg";
			$datab[$key]['template']=$value['content'];
		}
		
		
		return $datab;
		}
	
	//ajax 调用模板
	function gettemp($mode=null){

		if($mode=="image")
		{
			$map['fenlei']=4;
		}
		else{
			$map['fenlei']=2;
		}

		
		$data=DB::table('ien_fodder')->where($map)->select();
		foreach($data as $key=>$value )
		{
			$datab[$key]['id']=$value['title'];
			$datab[$key]['preview_img'] =  "http://".$_SERVER['SERVER_NAME']."/public/static/agent/image/".$value['title'].".jpg";
			$datab[$key]['template']=$value['content'];
		}
		
		
		return $datab;
		
		}
	
	//ajax 调用文章内容
	function getcontent($id = null)
	{
		if ($id === 0) $this->error('参数错误');
		$chapter=DB::table('ien_chapter')->where('id',$id)->find();
       // $chapter['content'] = str_replace("\n", "</p><p>", str_replace(" ", "", $chapter['content']));
		if($chapter['idx']>5)
		{
			return false;
		}
		$data=Db::query('select id,idx,title,content as paragraphs from ien_chapter where bid='.$chapter['bid'].' and idx<='.$chapter['idx'].' order by idx asc');
       // $data['content'] = str_replace("\n", "</p><p>", str_replace(" ", "", $data['content']));
		//$data=json_decode(json_encode($data),true);
		$book=DB::table('ien_book')->where('id',$chapter['bid'])->find();
		if($book['ismanhua']==1)
		{
			foreach($data as $key=>$value )
			{
				//$data[$key]['paragraphs']=["<img src='www.baidu.com' height=110 width=110>","<img src='www.baidu.com' height=110 width=110>"];
				//$data[$key]['paragraphs'] = explode("<br />&nbsp;&nbsp;&nbsp;&nbsp;",$value['paragraphs']);
	            $a.=$value['paragraphs'];
				
			}
			return json($a);	

		}
		else
		{
			foreach($data as $key=>$value )
			{
				
				//$data[$key]['paragraphs'] = explode("<br />&nbsp;&nbsp;&nbsp;&nbsp;",$value['paragraphs']);
                $data[$key]['paragraphs'] = explode("\n",$value['paragraphs']);
				
				
			}
			return json($data);	
		}


		
		}
	
	
	//创建生成链接
   function linkcreate($id = null){
	   if ($id === 0) $this->error('参数错误');
	   // 保存文档数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
			
		if ($data['name'] == '') {
            $this->error('标题不能为空');
            return false;
        }
        $bookid=DB::table('ien_chapter')->where('id',$data['zid'])->column('bid');
		$data['bid']=$bookid['0'];
        if (false === DB::table('ien_agent')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
        }
		
		$data_zj = DB::table('ien_chapter')->where('id',$id)->find();
		$data_xs = DB::table('ien_book')->where('id',$data_zj['bid'])->find();
		$this->assign('data_zj',$data_zj);
		$this->assign('data_xs',$data_xs);
		  // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号'],1],
                ['text', 'tgcb', '推广成本', '必填'],
                ['hidden', 'uid', UID],
                ['hidden', 'zid', $id],
                ['hidden', 'ljlx', 1],
                ['hidden', 'titleid',0],
                ['hidden', 'imageid',0],
				['hidden', 'tempid',0],
                ['hidden', 'footid',0],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])
			//->addStatic('', '当前小说章节', '', $data_listxs['title'])
			
			->isAjax(true)
            ->fetch('linkcreate');
	   
	   
	   
	   }
	   
	   //编辑标题
	   function edit($id=null){
		   if ($id === 0) $this->error('参数错误');
		   if ($this->request->isPost()) {
            $data = $this->request->post();
					
			
            if ($model = AgentModel::update($data)) {
               // Cache::clear();
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
			}
		   $info = AgentModel::get($id);
		   $this->assign('info',$info);
		   return ZBuilder::make('form')
            ->addFormItems([
				['hidden','id',$id],
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号']],
                ['text', 'tgcb', '推广成本', '必填'],
				['hidden', 'update_time', $this->request->time()],

            ])
			->setFormData($info)
			->isAjax(true)
            ->fetch('editym');
		   
		   
		   }
		   //添加首页链接
	   function addindex(){
		   if ($this->request->isPost()) {
            $data = $this->request->post();
					
			
            if ($model = AgentModel::create($data)) {
               // Cache::clear();
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
			}
		   return ZBuilder::make('form')
            ->addFormItems([
				['hidden','ljlx','3'],
				['hidden','uid',UID],
                ['text', 'name', '派单渠道名称', '必填'],
                ['radio', 'gzh', '派单渠道类型', '必填' ,['1'=>'认证公众号','0'=>'未认证公众号']],
                ['text', 'tgcb', '推广成本', '必填'],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])
			->isAjax(true)
            ->fetch();
		   
		   
		   }
		  function delete($ids=null)
		  {
			  if ($ids === null) $this->error('参数错误');
			  // 删除并记录日志
			if ($model = DB::table('ien_agent')->delete($ids)) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
			  }

		public function order($id=null)
		{
			$map="";
    		$mapa = $this->getMap();
    		$key=array_keys($mapa);
    		$i=0;
    		foreach($mapa as $k=>$value)
    		{	
    			
    			$name="ien_".$key[$i];
    			$map[$name]=$value;
    			$i++;
    		}

			$user=DB::view('ien_admin_user')
			->view('ien_pay_log','payid,money,type,status,addtime as laddtime,paytype','ien_admin_user.openid=ien_pay_log.uid')
			->where('ien_pay_log.paytype <> 0')
            ->where('ien_pay_log.isout',0)
			->where('ien_pay_log.tgid',$id)->where($map)->order('ien_pay_log.addtime desc')->paginate();

			return ZBuilder::make('table')	
			->hideCheckbox()
			->setSearch(['admin_user.nickname' => '用户名'])
            ->addFilter('pay_log.paytype',['1'=>'VIP会员','2'=>'普通充值'])
            ->addFilter('pay_log.type',['1'=>'公众号支付','2'=>'第三方支付'])
            ->addFilter('pay_log.status',['1'=>'已支付','0'=>'未支付'])
            ->addTimeFilter('pay_log.addtime')
            ->addColumns([// 批量添加数据列

				['payid', '订单ID','text'],
				['paytype','订单类型',['1'=>'VIP会员','2'=>'普通充值']],
                ['nickname','用户'],
				['money','充值金额'],
				['type', '支付方式', ['1'=>'公众号支付']],
				['status', '订单状态', ['1'=>'已支付','0'=>'未支付']],
				['laddtime', '添加时间', 'datetime'],

            ])
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板
			

		}

		public function kouliang($id=null)
		{

			$map="";
    		$mapa = $this->getMap();
    		$key=array_keys($mapa);
    		$i=0;
    		foreach($mapa as $k=>$value)
    		{	
    			
    			$name="ien_".$key[$i];
    			$map[$name]=$value;
    			$i++;
    		}

			$user=DB::view('ien_admin_user')
			->view('ien_pay_log','payid,money,type,status,addtime as laddtime,paytype','ien_admin_user.openid=ien_pay_log.uid')
			->where('ien_pay_log.paytype <> 0')
			->where('ien_pay_log.isout','1')->where($map)->order('ien_pay_log.addtime desc')
			->paginate();

			$today=DB::table('ien_pay_log')->where('status','1')->where('isout','1')->whereTime('paytime', 'today')->sum('money');
            $allday=DB::table('ien_pay_log')->where('status','1')->where('isout','1')->sum('money');

			return ZBuilder::make('table')	
			->hideCheckbox()
			->setPageTips('今日平台扣量合计:'.$today.'元<Br>累计平台扣量合计:'.$allday.'元')
            ->setSearch(['admin_user.nickname' => '用户名'])
            ->addFilter('pay_log.paytype',['1'=>'VIP会员','2'=>'普通充值'])
            ->addFilter('pay_log.type',['1'=>'公众号支付','2'=>'第三方支付'])
            ->addFilter('pay_log.status',['1'=>'已支付','0'=>'未支付'])
            ->addTimeFilter('pay_log.addtime')
            ->addColumns([// 批量添加数据列

				['payid', '订单ID','text'],
				['paytype','订单类型',['1'=>'VIP会员','2'=>'普通充值']],
                ['nickname','用户'],
				['money','充值金额'],
				['type', '支付方式', ['1'=>'公众号支付','0'=>'未知']],
				['status', '订单状态', ['1'=>'已支付','0'=>'未支付']],
				['laddtime', '添加时间', 'datetime'],

            ])
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板
			

		}


		//数据导出
  	    public function export()
    	{
        // 查询数据
		$map="";
        $map = $this->getMap();
		$map['uid']=UID;
		$usercount="";
		$usermoney="";
		//单独拼接组合查询
        $data_list = AgentModel::where($map)->order('id desc')->paginate(5000);
		$data=array();
		foreach($data_list as $key=>$value)
		{
			$data_list[$key]['click']=$value['click'];
			$data_list[$key]['name']=$value['name'];
			$data_list[$key]['id']=$value['id'];
			if($value['ljlx']==3)
			{

			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/index/index/?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}
			}

			else{
			$data_list[$key]['url']="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$value['zid'].".html?t=".$value['id'];
				//判断开启短连接
			if(module_config("agent.agent_short_url")=="on")
				{
					$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$data_list[$key]['url']);
					$data_list[$key]['url']=$shorturl->url->url_short;
				}

			}
			
			$data_list[$key]['count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->count();
          	$data_list[$key]['attention_count']=DB::table('ien_admin_user')->where('tgid',$value['id'])->where('isguanzhu',1)->count();
			$data_list[$key]['money']=DB::view('ien_admin_user')
			->where('ien_pay_log.tgid',$value['id'])
			->view('ien_pay_log','money','ien_admin_user.openid=ien_pay_log.uid')
			->where('ien_pay_log.status',1)
			->sum('ien_pay_log.money');
          	
          	//
          	$data_list[$key]['money']==NULL ? $data_list[$key]['money']=0 : $data_list[$key]['money'];
          	$data_list[$key]['tgcb']=$data_list[$key]['tgcb'];
          	$data_list[$key]['lirun']=$data_list[$key]['money']-$data_list[$key]['tgcb'];
			
			}

        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
				['id', 'auto', 'ID'],
				['name','auto','推广名称'],
				['url','auto','入口页面'],
                ['click','auto','点击'],
				['count','auto','注册用户数'],
              	['attention_count','auto','关注用户数'],
				['money','auto','充值金额'],
				['tgcb', 'auto','推广成本'],
				['lirun','auto','利润'],
				['create_time','auto','创建时间','datetime']
		];

		
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [time(), $cellName, $data_list]);
    }


    public function agentqun(){
    	$map = $this->getMap();
        $data_list = DB::table('ien_book')->where($map)->order('zhishu desc')->paginate();
		// 自定义按钮
        $btnsina = [
                'class' => 'btn btn-primary js-get',
                'icon'  => 'fa fa-plus-circle',
                'title' => '批量新浪端链接复制',
                'href'  => url('agent/quncopy',['mode'=>'sina'])
            ];
        $btnlong = [
                'class' => 'btn btn-primary js-get',
                'icon'  => 'fa fa-plus-circle',
                'title' => '批量普通链接复制',
                'href'  => url('agent/quncopy',['mode'=>'long'])
            ];




		$css = <<<EOF
           <style>
                .column-desc{width:400px;}
           </style>
EOF;

$myjs = <<<EOF
            <script type="text/javascript">
              var clipboard =  new Clipboard('.btn');
               clipboard.on('success', function(e) {
            	alert("复制成功");
        	});

        	clipboard.on('error', function(e) {
                alert("复制失败");
       		});
            </script>
EOF;

		return ZBuilder::make('table')
			->setPageTips('【注意】生成的链接产生的收益，可以在推广链接->群推广链接中查看')
			->setSearch(['title' => '名称', 'desc' => '描述'])
            ->addColumns([ // 批量添加数据列
            	['id','ID'],
			   ['image', '封面', 'picture'],

              // ['title', '名称','link',url('zjlist', ['id' => '__id__']),'_blank'],

               ['biaoti', '名称','callback', function($data_list){
               
               	if($data_list['ishot']==1)
               	{

               		$tb="<img src='/public/static/agent/image/new.jpg' height='15'>";
               	}
               	else
               	{
               		$tb="";
               	}

    	return "<a href='".url('zjlist', ['id' => $data_list['id']])."' target='_blank'>".$data_list['title']."</a>".($tb); 
    }, '__data__'],
			    ['url', 'URL','callback', function($data_list){
			    
			    $tglj=DB::table('ien_agent')->where('bid',$data_list['id'])->where('uid',UID)->where('isdel',0)->where('isqun',1)->order('id desc')->find();
			    if(empty($tglj))
			    {
			    	return "<a class='btn btn-xs btn-default ajax-get' href='".url('agent/qtglink', ['id' => $data_list['id']])."'>创建链接</a>"; 
			    }           
			    else{

			    	$url="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$tglj['zid'].".html?t=".$tglj['id'];
			    	$copy="<button class='btn btn-xs btn-default' data-clipboard-text=".$url.">复制</button>";
			    	$del="<a class='btn btn-xs btn-default ajax-get' href=".url('agent/qtglinkdel', ['id' => $data_list['id']]).">删除</a>";
			    	return $url.$copy."&nbsp;&nbsp;&nbsp;&nbsp;".$del;
			    }
			    

			    	
			    }, '__data__'],

			    ['shorturl', 'SHORTURL','callback', function($data_list){
			    
			    $tglj=DB::table('ien_agent')->where('bid',$data_list['id'])->where('uid',UID)->where('isdel',0)->where('isqun',1)->order('id desc')->find();
			    if(empty($tglj))
			    {
			    	return ""; 
			    }           
			    else{

			    	$url="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$tglj['zid'].".html?t=".$tglj['id'];
			    	
			    	$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$url);
					$res=$shorturl->url->url_short;
			    	return $res;
			    }
			    
			    	
			    }, '__data__'],
			   
			   

            ])
			->setTableName('book')
			->addFilter('cid',['2'=>'男生','3'=>'女生']) // 添加筛选		
			->addFilter('tstype',parse_attr(module_config('agent.agent_novel_type'))) // 添加筛选			
			->addFilter('status',parse_attr(module_config('agent.agent_book_is'))) // 添加筛选	
			->addOrder('zhishu')
			->setExtraCss($css)
			->js('clipboard')
			->addTopButton('custom', $btnsina)
			->addTopButton('custom', $btnlong)
          	->setExtraJs($myjs)
			->addRightButton('custom',$btn)
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板

    }

    public function qtglink($id=null){
    	$cid=DB::table('ien_chapter')->where('idx',1)->where('bid',$id)->find();
    	if(empty($cid))
    	{
    		$this->error('章节数据错误！请联系管理员！');
    	}
    	$data['ljlx']=4;
    	$data['uid']=UID;
		$data['name']='群推广-'.$cid['title'];
		$data['gzh']=0;
		$data['tgcb']=0;
        $data['create_time']=$this->request->time();
        $data['update_time']=$this->request->time();
        $data['bid']=$id;
        $data['zid']=$cid['id'];
        $data['isqun']=1;
        $data['isdel']=0;
        DB::table('ien_agent')->insert($data);
		$this->success('新增成功');



    }

    public function qtglinkdel($id=null){
    	DB::table('ien_agent')->where('bid',$id)->where('isqun',1)->update(['isdel'=>1]);
    	$this->success('删除成功');

    }

    public function quncopy($mode=null,$ids=null){
    	$id=explode(",",$ids);
    	foreach($id as $key=>$value)
    	{
    		$tglj=DB::table('ien_agent')->where('bid',$value)->where('uid',UID)->where('isdel',0)->where('isqun',1)->order('id desc')->find();
    		$book=DB::table('ien_book')->where('id',$value)->find();
    		if(!empty($tglj))
    		{
	    		if($mode=="long")
	    		{
	    			$data.=$book['title'].":&nbsp;&nbsp;&nbsp;&nbsp;"."http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$tglj['zid'].".html?t=".$tglj['id']."<br/>";
	    		}
	    		if($mode=="sina")
	    		{
	    			$url="";
	    			$url="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$tglj['zid'].".html?t=".$tglj['id'];
	    			$apiurl="http://api.t.sina.com.cn/short_url/shorten.xml?source=3271760578&url_long=";
					$shorturl = simplexml_load_file($apiurl.$url);
					$data.=$book['title'].":&nbsp;&nbsp;&nbsp;&nbsp;".$shorturl->url->url_short."<br/>";
	    		}
    		}
    	}
    	$this->assign('content', $data);
    	return ZBuilder::make('table')
            ->fetch('linkcopy'); // 渲染模板

    }



}