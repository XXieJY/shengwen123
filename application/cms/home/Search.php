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

/**
 * 前台搜索控制器
 * @package app\cms\admin
 */
class Search extends Common
{
    //搜索首页
    public function index()
    {

        $hotbook=DB::table('ien_book')->where('status','1')->where('ishot','1')->order('zhishu desc')->limit(8)->select();
        $this->assign('hotbook', $hotbook);

        return $this->fetch(); // 渲染模板
    }

    //热门推荐
    public function recommend()
    {
        $booklist=DB::table('ien_book')->where('status','1')->order('tips desc')->limit(10)->select();
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
        $data_list=json_decode(json_encode($data_list),true);
        return  $data_list;
        
        
    }
    //搜索结果
    public function search($q=null,$start=null,$limit=null)
    {
        if(empty($start))
        {
          $start=0;
        }  
        if(empty($limit))
        {
          $limit=10;
        }
        $booklist=DB::table('ien_book')->where('status','1')->where("title like '%".$q."%'")->order('zhishu desc')->limit($start,$limit)->select();
        $count=DB::table('ien_book')->where('status','1')->where("title like '%".$q."%'")->count();
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
}