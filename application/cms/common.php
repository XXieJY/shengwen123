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

// 门户模块公共函数库
use think\Db;

function is_weixin() { 
    //判断后台关闭返回true，开启之后判断
    if(module_config('agent.phoneopen')=='on')
    {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) { 
        return true; 
    } return false; 
    }
    else{
        return true;
    }
}
//发送短信
function sendsms($phone=null){
    $statusStr = array(
    "0" => "短信发送成功",
    "-1" => "参数不全",
    "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
    "30" => "密码错误",
    "40" => "账号不存在",
    "41" => "余额不足",
    "42" => "帐户已过期",
    "43" => "IP地址限制",
    "50" => "内容含有敏感词"
    );
    $smsapi = "http://api.smsbao.com/";
    $user = module_config('agent.phoneuser'); //短信平台帐号
    $pass = md5(module_config('agent.phonepassword')); //短信平台密码
    $c=generate_rand_str(6,3);
    DB::table('ien_sendsms_log')->insert(['phone'=>$phone,'code'=>$c,'addtime'=>time()]);
    $content="【".module_config('wechat.name')."】您的验证码是".$c." ，请不要告诉任何人哦！";//要发送的短信内容
    $phone = $phone;//要发送短信的手机号码

    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
    $result =file_get_contents($sendurl) ;
    return $statusStr[$result];
    //return "短信发送成功";
}
if (!function_exists('generate_rand_str')) {
    /**
     * 生成随机字符串
     * @param int $length 生成长度
     * @param int $type 生成类型：0-小写字母+数字，1-小写字母，2-大写字母，3-数字，4-小写+大写字母，5-小写+大写+数字
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function generate_rand_str($length = 8, $type = 0) {
        $a = 'abcdefghijklmnopqrstuvwxyz';
        $A = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $n = '0123456789';

        switch ($type) {
            case 1: $chars = $a; break;
            case 2: $chars = $A; break;
            case 3: $chars = $n; break;
            case 4: $chars = $a.$A; break;
            case 5: $chars = $a.$A.$n; break;
            default: $chars = $a.$n;
        }

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $str;
    }
}

if (!function_exists('get_column_name')) {
    /**
     * 获取栏目名称
     * @param int $cid 栏目id
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function get_column_name($cid = 0)
    {
        $column_list = model('cms/column')->getList();
        return isset($column_list[$cid]) ? $column_list[$cid]['name'] : '';
    }
}

if (!function_exists('get_model_name')) {
    /**
     * 获取内容模型名称
     * @param string $id 内容模型id
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function get_model_name($id = '')
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['name'] : '';
    }
}

if (!function_exists('get_model_title')) {
    /**
     * 获取内容模型标题
     * @param string $id 内容模型标题
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function get_model_title($id = '')
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['title'] : '';
    }
}

if (!function_exists('get_model_type')) {
    /**
     * 获取内容模型类别：0-系统，1-普通，2-独立
     * @param int $id 模型id
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function get_model_type($id = 0)
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['type'] : '';
    }
}

if (!function_exists('get_model_table')) {
    /**
     * 获取内容模型附加表名
     * @param int $id 模型id
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function get_model_table($id = 0)
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['table'] : '';
    }
}

if (!function_exists('is_default_field')) {
    /**
     * 检查是否为系统默认字段
     * @param string $field 字段名称
     * @author 拼搏 <378184@qq.com>
     * @return bool
     */
    function is_default_field($field = '')
    {
        $system_fields = cache('cms_system_fields');
        if (!$system_fields) {
            $system_fields = Db::name('cms_field')->where('model', 0)->column('name');
            cache('cms_system_fields', $system_fields);
        }
        return in_array($field, $system_fields, true);
    }
}

if (!function_exists('table_exist')) {
    /**
     * 检查附加表是否存在
     * @param string $table_name 附加表名
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function table_exist($table_name = '')
    {
        return true == Db::query("SHOW TABLES LIKE '{$table_name}'");
    }
}

if (!function_exists('time_tran')) {
    /**
     * 转换时间
     * @param int $timer 时间戳
     * @author 拼搏 <378184@qq.com>
     * @return string
     */
    function time_tran($timer)
    {
        $diff = $_SERVER['REQUEST_TIME'] - $timer;
        $day  = floor($diff / 86400);
        $free = $diff % 86400;
        if ($day > 0) {
            return $day . " 天前";
        } else {
            if ($free > 0) {
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if ($hour > 0) {
                    return $hour . " 小时前";
                } else {
                    if ($free > 0) {
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if ($min > 0) {
                            return $min . " 分钟前";
                        } else {
                            if ($free > 0) {
                                return $free . " 秒前";
                            } else {
                                return '刚刚';
                            }
                        }
                    } else {
                        return '刚刚';
                    }
                }
            } else {
                return '刚刚';
            }
        }
    }
}