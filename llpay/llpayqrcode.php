<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>连连支付二维码支付接口</title>
</head>
<?php


/* *
 * 功能：连连支付二维码支付接口接入页
 * 版本：1.0
 * 修改日期：2014-06-17
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */
require_once ("llpay.config.php");
require_once ("lib/llpay_apipost_submit.class.php");

/**************************请求参数**************************/

//必填

//商家订单号
$no_order = $_POST['no_order'];

//商户号
$oid_partner = $_POST['oid_partner'];

//订单金额
$money_order = $_POST['money_order'];
//商家订单时间
$dt_order = $_POST['dt_order'];
//商家通知地址
$notify_url = $_POST['notify_url'];
//支付方式
$pay_type = $_POST['pay_type'];
//风控参数
$risk_item = $_POST['risk_item'];
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
	
);
//建立请求
$llpaySubmit = new LLpaySubmit($llpay_config);
$html_text = $llpaySubmit->buildRequestJSON($parameter,$llpay_gateway_new);
echo $html_text;
?>
</body>
</html>