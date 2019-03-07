<?php 

// 创建会话资源
$ch = curl_init();

// 设置URL
curl_setopt($ch, CURLOPT_URL, "baidu.com");

// 表明POST请求
curl_setopt($ch, CURLOPT_POST, 1);

// 最长时间
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

// POST数据域(数组形式)
curl_setopt($ch, CURLOPT_POSTFIELDS , http_build_query($data));

// 是否将响应结果存入变量，1是存入，0是echo出来
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// 执行
$output = curl_exec($ch);
echo $output;

// 关闭会话资源
curl_close($ch);