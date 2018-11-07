<?php

/* *
 * 配置文件
 * 版本：1.0
 * 日期：2014-06-16
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */

//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
$llpay_config['oid_partner'] = '201708210000817312';

//秘钥格式注意不能修改（左对齐，右边有回车符）
$llpay_config['RSA_PRIVATE_KEY'] ='-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQCna2R1vkG2aXnXRiQVVegZO3dNthRtql9HqBihPXTzd6DFp6xO
/spGM1ZWzFA8WO1d0ublH/rXddVw8rCuefNyvskhynEIv/SDB1WF4aAOJVTLzPCg
JkwdPt70ZE1829G9PcIerqsD5iULpVUbS6Yei3VtwAUCB5+o5uz0vqtZdwIDAQAB
AoGAQam1NRjx3QFxcCVqcZPH/F1+ZVRMntZ7mDctsc/Q1szMmbkxs6nq262250Qr
FAAs2Oem96abU3Y7yJjnAweBm5UfkNrqv6IMB1KnjmeOZjdMtPF61gfmHlbZs0c0
lz+Vb/AYrKxxykwsFLu6UzkGkrnqNGKXBA3qYzQFgVp93EECQQDRzf6t2U8s68QA
4Ijz7hVkKS7Jwq71SDR0jE1uUSpRR7NnZLxwqEzMwtac7MO4OG4GsgvkYSEN3XoP
fXcSZrDFAkEAzEhIsbX5/Bc7z7sDT7qZU0SCf45vXxtFpLcaUrrsbynftpFdaSF7
mersNemCAo8dv/5HrSetS9c01Ub2OSTNCwJAL3qNiuwllcXY+lXVyFX1s0/6jaAo
Fcv8Su/BShjGGdS/DcPnPRtpHeFtzd9qv1LN22gJdupgH5IiYEUFYD2q/QJAM1gX
rVLnugy848FFW8yyrNMTWawbIfRg1L2QyCpjMq17pQ90QvA/eLgrFoHSQ4JlH9Rx
f3vxepZkmRZ03Hf7cQJAC6jluJmyPl/6Ul+JdBiSQgoUUcLmFR69xLe+4Dj7lo1U
Un1g1qaYf31uQzlCpLjt+d+liNn9XoG9z8fSUsT3LA==
-----END RSA PRIVATE KEY-----
';	

//安全检验码，以数字和字母组成的字符
$llpay_config['key'] = '2121';

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//版本号
$llpay_config['version'] = '1.0';

//防钓鱼ip
$llpay_config['userreq_ip'] = '10.10.246.110';

//证件类型
$llpay_config['id_type'] = '0';

//签名方式 不需修改
$llpay_config['sign_type'] = strtoupper('RSA');

//订单有效时间  分钟为单位，默认为10080分钟（7天） 
$llpay_config['valid_order'] ="30";

//字符编码格式 目前支持 gbk 或 utf-8
$llpay_config['input_charset'] = strtolower('utf-8');

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$llpay_config['transport'] = 'http';
?>