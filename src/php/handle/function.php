<?php 

// 根据当前系统进行文件编码
function getCode($file){
	if (stripos(PHP_OS, 'win') !== FALSE) {
		return iconv('UTF-8', 'GBK', $file);
	} else if (stripos(PHP_OS, 'linux') !== FALSE) {
		return $file;
	}
}

// curl 实现双机备份
function curlPost($url, $data){
	// 初始化 curl
	$curl = curl_init();

	// 设置 curl 参数
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	// 执行 curl
	$res = curl_exec($curl);

	// 关闭 curl
	curl_close($curl);

	// 输出执行结果
	return $res;
}