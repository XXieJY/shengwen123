<?php
/**
 * User: pinbo
 * Date: 2017/4/8
 * Time: 上午9:50
 */
//后台访问控制器
namespace app\agent\admin;//修改ann为模块名

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;

class Articles extends Admin{
	
	public function index(){

        $btn_edit = [
            'title' => '修改',
            'icon'  => 'fa fa-pencil',
            'class' => 'btn btn-xs btn-default',
            'href'  => url('aedit', ['id' => '__id__']),
        ];
        $btn_del = [
            'title' => '删除',
            'icon'  => 'fa fa-times',
            'class' => 'btn btn-xs btn-default ajax-get confirm',
            'href'  => url('adelete', ['id' => '__id__']),
        ];
		$book=DB::table('ien_articles')->where('uid',UID)->order('create_time desc')->paginate();
		
		return ZBuilder::make('table')
			->hideCheckbox()
            ->addColumns([ // 批量添加数据列
				['pic','分享图片','picture'],
				['biaoti', '分享标题','callback',function($book){
                     $rooturl="http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/articles/index/id/".$book['id'];
                     return "<a href='".$rooturl."' target='_blank' >".$book['title']."</a>";

                },'__data__'],
                ['bid', '推广链接ID'],
                ['zname', '章节','callback',function($book){
                    try{
                    $zname=DB::table('ien_chapter')->where('id',$book['zid'])->find();
                    $z="第".$zname['idx']."章 ".$zname['title'];

                    return (string)$z;
                }
                catch(\Exception $e){
                    return "";
                }
                
                },'__data__'],
                ['click', '点击数'],
                ['create_time', '添加日期','datetime'],
				
				['right_button', '操作', 'btn']

            ])
            ->setTableName('book')
			->addTopButton('add') 
			->addRightButton('custom',$btn_edit)
            ->addRightButton('custom',$btn_del)
            ->setRowList($book) // 设置表格数据
            ->fetch(); // 渲染模板
		}
		
       public function add(){
            if ($this->request->isPost()) {
            $data = $this->request->post();

        
            if (false === DB::table('ien_articles')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
        }

        
          // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'uid', UID],
                ['text', 'title', '分享标题', '必填'],
                ['textarea', 'description', '分享描述', '必填'],
                ['image', 'pic', '分享图片', '必填'],
                ['text', 'bid', '推广链接id', '阅读原文调用的推广链接,列表中查看'],
                ['text', 'zid', '章节id', '章节列表中查看'],
                ['Ueditor', 'content', '文案内容', '必填'],
                ['hidden', 'create_time', time()],
               

            ])
            ->isAjax(true)
            ->layout(['pic' => 4, 'bid' => 4, 'zid' => 4,])
            ->fetch();


       }

        public function aedit($id=null){
            if ($id === 0) $this->error('参数错误');
            if ($this->request->isPost()) {
            $data = $this->request->post();

        
            if (false === DB::table('ien_articles')->where('id',$data['id'])->update($data)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
             }

        $article=DB::table('ien_articles')->where('id',$id)->find();
          // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],
                ['text', 'title', '分享标题', '必填'],
                ['textarea', 'description', '分享描述', '必填'],
                ['image', 'pic', '分享图片', '必填'],
                ['text', 'bid', '推广链接id', '阅读原文调用的推广链接,列表中查看'],
                ['text', 'zid', '章节id', '章节列表中查看'],
                ['Ueditor', 'content', '文案内容', '必填'],
                ['hidden', 'create_time', time()],
               

            ])
            ->setFormData($article)
            ->isAjax(true)
            ->layout(['pic' => 4, 'bid' => 4, 'zid' => 4,])
            ->fetch();


       }
		
        public function adelete($id=null){
            Db::table('ien_articles')->where('id',$id)->delete();
            $this->success('删除成功!');


        }





}