<?php
/**
 * User: pinbo
 * Date: 2017/4/8
 * Time: 上午9:50
 * b363c3ddb38a38e6c4fd7653fdc3cf00
 */
//后台访问控制器
namespace app\agent\admin;//修改ann为模块名

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;

class Book extends Admin{
	
	function index($group = 'tab1'){
        $list_tab = [
        'tab1' => ['title' => '小说管理', 'url' => url('index', ['group' => 'tab1'])],
        'tab2' => ['title' => '限免小说', 'url' => url('index', ['group' => 'tab2'])],
        ];
        switch ($group) {
        case 'tab1':
		$map = $this->getMap();
		$order = $this->getOrder();
		$book=DB::table('ien_book')->where($map)->order($order)->order('zhishu desc')->paginate();
		$xstype=DB::table('ien_cms_field')->where('id',82)->find();
		$xstype = explode("\r\n", $xstype['options']); 
		$tstype=DB::table('ien_cms_field')->where('id',49)->find();
		$tstype = explode("\r\n", $tstype['options']); 
		$btnRecycle = [
                'title' => '章节管理',
                'icon'  => 'fa fa-fw fa-book',
                
                'href'  => url('book/chapter', ['id' => '__id__'])
            ];
         $btnAdd = [
                'title' => '添加小说',
                'icon'  => 'fa fa-plus-circle',
                'class' => 'btn btn-primary confirm',
                'href'  => url('book/bookadd')
            ];
         $btnDelete = [
                'title' => '删除',
                'icon'  => 'fa fa-fw fa-remove',
                'class' => 'btn btn-xs btn-default ajax-get confirm',
                'href'  => url('book/bookdel', ['id' => '__id__']),
                'data-title' => '真的要删除吗？无法恢复!',
			    'data-tips' => '删除小说同时删除所有章节',
			    'data-confirm' => '删除吧',
			    'data-cancel' => '再想想'

            ];
            $btnlead = [
                'title' => '导入小说',
                'icon'  => 'fa fa-fw fa-book',
                
                'href'  => url('book/booklead')
            ];
            
		return ZBuilder::make('table')
			->hideCheckbox()
            ->setTabNav($list_tab,  $group)
            ->addColumns([ // 批量添加数据列
            	['id', 'ID'],
				['image','封面','picture'],
				['title', '小说名称'],
				['cid','频道','text', '', ['2'=>'男生','3'=>'女生']],
				['xstype','小说状态','text', '', $xstype],
				['tstype','小说类型','text', '', $tstype],
				['zhishu','派单指数','text.edit'],
				['status','状态','switch'],
				['tips','打赏金额','text.edit'],
				['score','消费书币','text.edit'],
                ['gzzj','关注章节','text.edit'],
                ['zhuishu','追书人数','text.edit'],
                ['ishot','热门','switch'],
				['right_button', '操作', 'btn']

            ])
            ->setTableName('book')
            ->addOrder('id,zhishu,tips')
            ->setSearch(['id' => 'ID', 'title' => '小说名称'])
			->addFilter('cid', ['2'=>'男生','3'=>'女生'])
			->addFilter('xstype', $xstype)
			->addFilter('tstype', $tstype)
			->addTopButton('custom', $btnAdd,[],true) 
            ->addTopButton('custom', $btnlead) 
			->addRightButton('custom', $btnRecycle) 
			->addRightButton('edit')
			->addRightButton('custom', $btnDelete) 
            ->setRowList($book) // 设置表格数据
            ->fetch(); // 渲染模板
            break;

        case 'tab2':
        $map = $this->getMap();
        $order = $this->getOrder();
        $book=DB::table('ien_book')->where($map)->where('isfree','1')->where('free_stime','<= time',time())->where('free_etime','>= time',time())->order($order)->order('zhishu desc')->paginate();
        $xstype=DB::table('ien_cms_field')->where('id',82)->find();
        $xstype = explode("\r\n", $xstype['options']); 
        $tstype=DB::table('ien_cms_field')->where('id',49)->find();
        $tstype = explode("\r\n", $tstype['options']); 
        $btnRecycle = [
                'title' => '章节管理',
                'icon'  => 'fa fa-fw fa-book',
                
                'href'  => url('book/chapter', ['id' => '__id__'])
            ];
         $btnAdd = [
                'title' => '添加小说',
                'icon'  => 'fa fa-plus-circle',
                'class' => 'btn btn-primary confirm',
                'href'  => url('book/bookadd')
            ];
         $btnDelete = [
                'title' => '删除',
                'icon'  => 'fa fa-fw fa-remove',
                'class' => 'btn btn-xs btn-default ajax-get confirm',
                'href'  => url('book/bookdel', ['id' => '__id__']),
                'data-title' => '真的要删除吗？无法恢复!',
                'data-tips' => '删除小说同时删除所有章节',
                'data-confirm' => '删除吧',
                'data-cancel' => '再想想'

            ];
        return ZBuilder::make('table')
            ->hideCheckbox()
            ->setTabNav($list_tab,  $group)
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['image','封面','picture'],
                ['title', '小说名称'],
                ['cid','频道','text', '', ['2'=>'男生','3'=>'女生']],
                ['xstype','小说状态','text', '', $xstype],
                ['tstype','小说类型','text', '', $tstype],
                ['zhishu','派单指数','text.edit'],
                ['status','状态','switch'],
                ['tips','打赏金额','text.edit'],
                ['score','消费书币','text.edit'],
                ['gzzj','关注章节','text.edit'],
                ['zhuishu','追书人数','text.edit'],
                ['ishot','热门','switch'],
                ['right_button', '操作', 'btn']

            ])
            ->setTableName('book')
            ->addOrder('id,zhishu,tips')
            ->setSearch(['id' => 'ID', 'title' => '小说名称'])
            ->addFilter('cid', ['2'=>'男生','3'=>'女生'])
            ->addFilter('xstype', $xstype)
            ->addFilter('tstype', $tstype)
            ->addTopButton('custom', $btnAdd,[],true) 
            ->addRightButton('custom', $btnRecycle) 
            ->addRightButton('edit')
            ->addRightButton('custom', $btnDelete) 
            ->setRowList($book) // 设置表格数据
            ->fetch(); // 渲染模板
            break;
        }

		}
		
		//添加小说
		public function bookadd(){

			if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['free_stime']=strtotime($data['free_stime']);
            $data['free_etime']=strtotime($data['free_etime']);
            if($data['isfree']='on')
            {
                $data['isfree']=1;
            }
            if(!empty($data['tj']))
            {
            $data['tj']=implode(',',$data['tj']);
        	}
        	else{
        	$data['tj']="";
        	}
            if (DB::table('ien_book')->insert($data)) {
               // Cache::clear();
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
			}
            if(empty($data['ishot']))
            {
                $data['ishot']=0;
            }
            elseif($data['ishot']=="on")
            {
                $data['ishot']=1;
            }
            if(empty($data['ismanhua']))
            {
                $data['ismanhua']=0;
            }
            elseif($data['ismanhua']=="on")
            {
                $data['ismanhua']=1;
            }
			$xstype=DB::table('ien_cms_field')->where('id',82)->find();
			$xstype = explode("\r\n", $xstype['options']); 
			$tstype=DB::table('ien_cms_field')->where('id',49)->find();
			$tstype = explode("\r\n", $tstype['options']); 
			$tj=DB::table('ien_cms_field')->where('id',53)->find();
			$tj = explode("\r\n", $tj['options']); 
			return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题', '必填'],
                ['textarea', 'desc', '简介', '必填' ],
                ['image','image','封面图片','必填'],
                ['text', 'zuozhe', '作者', '必填'],
                ['text', 'zishu', '字数', '必填'],
                ['text', 'zhishu', '指数', '必填'],
                ['radio','tstype','小说类型','必填',$tstype],
                ['radio','xstype','小说状态','必填',$xstype],
                ['checkbox','tj','推荐位','选填',$tj],

                ['datetime', 'free_stime', '限免开始时间', ],
                ['datetime', 'free_etime', '限免结束时间', ],
                

                ['select', 'cid','分类','必填', ['2'=>'男生','3'=>'女生']],
                ['text', 'tips', '打赏金额', '选填'],
                ['select', 'status', '启用','',['1'=>'启用','0'=>'下架'],1],
                ['switch', 'isfree', '是否限免', ],
                ['switch', 'ishot', '是否热门', ],
                ['switch', 'ismanhua', '是否漫画', '如果是漫画这里必须选择，否则文案出错！'],
                ['text', 'zhuishu', '追书人数', '选填'],

                ['hidden', 'uid', UID],
				['hidden', 'sort',100],
                ['hidden', 'model',6],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])	
            ->layout(['zuozhe' => 4, 'zishu' => 4, 'zhishu' => 4, 'cid' => 3,'tips' => 3,'status' => 3,'ishot'=>3,'isfree'=>6,'free_stime'=>3,'free_etime'=>3,'ismanhua'=>3,'zhuishu'=>3])
			->isAjax(true)
            ->fetch();

		}

				//修改小说
		public function edit($id=null){
			if ($id === 0) $this->error('参数错误');
			if ($this->request->isPost()) {
            $data = $this->request->post();
            if($data['isfree']='on')
            {
                $data['isfree']=1;
            }
            $data['free_stime']=strtotime($data['free_stime']);
            $data['free_etime']=strtotime($data['free_etime']);
            if(!empty($data['tj']))
            {
            $data['tj']=implode(',',$data['tj']);
        	}
        	else{
        	$data['tj']="";
        	}
            if(empty($data['ishot']))
            {
                $data['ishot']=0;
            }
            elseif($data['ishot']=="on")
            {
                $data['ishot']=1;
            }
            if(empty($data['ismanhua']))
            {
                $data['ismanhua']=0;
            }
            elseif($data['ismanhua']=="on")
            {
                $data['ismanhua']=1;
            }

            if (DB::table('ien_book')->where('id',$data['id'])->update($data)) {
               // Cache::clear();
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
			}
			$xstype=DB::table('ien_cms_field')->where('id',82)->find();
			$xstype = explode("\r\n", $xstype['options']); 
			$tstype=DB::table('ien_cms_field')->where('id',49)->find();
			$tstype = explode("\r\n", $tstype['options']); 
			$tj=DB::table('ien_cms_field')->where('id',53)->find();
			$tj = explode("\r\n", $tj['options']); 
			$info=DB::table('ien_book')->where('id',$id)->find();
            $info['free_stime']=date('Y-m-d H:i',$info['free_stime']);
            $info['free_etime']=date('Y-m-d H:i',$info['free_etime']);
            
			return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题', '必填'],
                ['textarea', 'desc', '简介', '必填' ],
                ['image','image','封面图片','必填'],
                ['text', 'zuozhe', '作者', '必填'],
                ['text', 'zishu', '字数', '必填'],
                ['text', 'zhishu', '指数', '必填'],
                ['radio','tstype','小说类型','必填',$tstype],
                ['radio','xstype','小说状态','必填',$xstype],
                ['checkbox','tj','推荐位','选填',$tj],

                ['Datetime', 'free_stime', '限免开始时间', ],
                ['Datetime', 'free_etime', '限免结束时间', ],
               

                ['select', 'cid','分类','必填', ['2'=>'男生','3'=>'女生']],
                ['text', 'tips', '打赏金额', '选填'],
                ['select', 'status', '启用','',['1'=>'启用','0'=>'下架'],1],
                ['switch', 'isfree', '是否限免', ],
                ['switch', 'ishot', '是否热门', ],
                ['switch', 'ismanhua', '是否漫画', '如果是漫画这里必须选择，否则文案出错！'],
                ['text', 'zhuishu', '追书人数', '选填'],
                
                ['hidden', 'id'],
                ['hidden', 'uid'],
				['hidden', 'sort'],
                ['hidden', 'model'],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])	

            ->setFormData($info)
            ->layout(['zuozhe' => 4, 'zishu' => 4, 'zhishu' => 4, 'cid' => 3,'tips' => 3,'status' => 3,'ishot'=>3,'isfree'=>6,'free_stime'=>3,'free_etime'=>3,'ismanhua'=>3,'zhuishu'=>3])
			->isAjax(true)
            ->fetch();

		}

		//删除小说同时删除章节
		public function bookdel($id=null)
		{
			if ($id === 0) $this->error('参数错误');
			DB::table('ien_book')->where('id',$id)->delete();
			DB::table('ien_chapter')->where('bid',$id)->delete();
			$this->success('删除成功');

		}

		//章节管理
		public function chapter($id=null)
		{
			if ($id === 0) $this->error('参数错误');
			// 自定义按钮
		$btnAdd = [
                'title' => '添加',
                'icon'  => 'fa fa-plus-circle',
                'class' => 'btn btn-primary confirm',
                'href'  => url('book/chapteradd')
            ];
         $btnEdit = [
                'title' => '修改',
                'icon'  => 'fa fa-pencil',
                'class' => 'btn btn-xs btn-default confirm',
                'href'  => url('book/chapteredit', ['id' => '__id__'])
            ];
         $btnDelete = [
                'title' => '删除',
                'icon'  => 'fa fa-fw fa-remove',
                'class' => 'btn btn-xs btn-default ajax-get confirm',
                'href'  => url('book/chapterdel', ['id' => '__id__']),
                'data-title' => '真的要删除吗？',
			    'data-tips' => '删除后,无法恢复!',
			    'data-confirm' => '删除吧',
			    'data-cancel' => '再想想'

            ];
			
		$map = $this->getMap();
		$map['bid']=$id;
        $data_list = DB::table('ien_chapter')->where($map)->order('idx asc')->paginate();
		$data_listxs = DB::table('ien_book')->where('id',$id)->find();
		
		return ZBuilder::make('table')
			->setPageTips('【注意】删除后不可恢复！')
			->hideCheckbox()
			->setPageTitle($data_listxs['title'])
            ->addColumns([ // 批量添加数据列
			
				['id','章节ID'],
				
                ['title', '名称'],
                ['isvip', '是否VIP','switch'],

				['right_button', '操作', 'btn']

            ])
			->setTableName('chapter')
			->addTopButton('custom',$btnAdd)
			->addRightButton('custom',$btnEdit)
			->addRightButton('custom',$btnDelete)
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
		}

		//添加章节
		public function chapteradd(){

			if ($this->request->isPost()) {
            $data = $this->request->post();
            
            if (DB::table('ien_chapter')->insert($data)) {
               // Cache::clear();
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
			}
			
			return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题', '必填'],
                ['textarea', 'content', '内容', '必填' ],
                ['text','idx','章节排序','必填'],
                ['text','bid','小说ID','必填'],
                ['select', 'isvip', '是否VIP','',['1'=>'是','0'=>'否'],1],

                ['hidden', 'cid',5],
                ['hidden', 'status',1],
                ['hidden', 'uid', UID],
				['hidden', 'sort',100],
                ['hidden', 'model',7],
				['hidden', 'create_time', $this->request->time()],
				['hidden', 'update_time', $this->request->time()],

            ])	
            ->layout(['bid' => 4, 'idx' => 4, 'isvip' => 4])
			->isAjax(true)
            ->fetch();

		}

		//修改章节
		public function chapteredit($id=null){

			if ($id === 0) $this->error('参数错误');
			if ($this->request->isPost()) {
            $data = $this->request->post();
            if (DB::table('ien_chapter')->where('id',$data['id'])->update($data)) {
               // Cache::clear();
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
			}
			$info=DB::table('ien_chapter')->where('id',$id)->find();
			return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题', '必填'],
                ['textarea', 'content', '内容', '必填' ],
                ['text','idx','章节排序','必填'],
                ['text','bid','小说ID','必填'],
                ['select', 'isvip', '是否VIP','',['1'=>'是','0'=>'否']],

                ['hidden', 'id'],
				['hidden', 'update_time', $this->request->time()],

            ])	
            ->layout(['bid' => 4, 'idx' => 4, 'isvip' => 4])
            ->setFormData($info)
			->isAjax(true)
            ->fetch();

		}

		//删除小说同时删除章节
		public function chapterdel($id=null)
		{
			if ($id === 0) $this->error('参数错误');
			DB::table('ien_chapter')->where('id',$id)->delete();
			$this->success('删除成功');

		}

        public function booklead(){



            if ($this->request->isPost()){
            $data = $this->request->post();
            $bid=$data['bid'];
           echo $bid;exit();
            $idx=1;
            $filename=$data['bookurl'];
            $content=file_get_contents($filename);
            if (strpos($content, "\xEF\xBB\xBF") === 0) {
                $content = substr($content, 3);
            }
            //set_time_limit(0);
            //ini_set('memory_limit', '-1');
            /* ###(.*?)\r+(.*?)(?=###) */
            //小说章节格式
            //###第一章 回车 文章内容 回车  ###第二章 回车 文章内容 回车 ###
            $reg="/###(.*?)\n+(.*?)(?=###)/is";
            preg_match_all($reg, $content,$arr);
            foreach ($arr[1] as $key => $value) {
                $book[$key]['title']=str_replace(array("\r\n", "\r", "\n"),'',$arr[1][$key]);
                $book[$key]['content']=str_replace(array("\r\n", "\r", "\n"),'<br />&nbsp;&nbsp;&nbsp;&nbsp;',$arr[2][$key]);

                $book[$key]['idx']=$idx;
                if($idx<$vip)
                {
                    $book[$key]['isvip']=0;
                }
                else{
                    $book[$key]['isvip']=1;
                }
                $book[$key]['bid']=$bid;
                $book[$key]['cid']=5;
                $book[$key]['uid']=1;
                $book[$key]['model']=7;
                $book[$key]['sort']=100;
                $book[$key]['status']=1;
                $idx++;

                $a=Db::table('ien_chapter')->insert($book[$key]);

            }
            $this->success('导入成功');


            }

            $dir="book";
            $result = array();
            $handle = opendir($dir);
            if ( $handle )
            {
                while ( ( $file = readdir ( $handle ) ) !== false )
                {
                    if ( $file != '.' && $file != '..')
                    {
                        $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                        if ( is_dir ( $cur_path ) )
                        {
                            $result['dir'][$cur_path] = read_all_dir ( $cur_path );
                        }
                        else
                        {
                            $result['file'][] = $cur_path;
                        }
                    }
                }
                closedir($handle);
            }
            foreach ($result['file'] as $key => $value) {
                $bookurl[$value]=$value;
            }
            return ZBuilder::make('form')
            ->setPageTips('小说txt文件请用管理面板或FTP上传至：根目录/book/ 中，刷新本页面自动加载。<br/> 使用手册：https://www.kancloud.cn/hackerhht/hsbook/472659', 'danger')
          //  ->addSelect('bookurl', '选择小说', '选择导入的小说',$bookurl)
           ->addFormItem('text', 'bid', '导入目标小说ID')
         //   ->addFormItem('text', 'vip', '收费章节')
            ->fetch();




        }





}