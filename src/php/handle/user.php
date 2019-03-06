<?php 

include '../public/conf/connect.php';
include 'function.php';

/**
 * register：注册
 * login：登录
 * createClass：新建班级
 * classEdit：修改班级
 * joinClass：加入班级
 * revoke：撤销申请/取消班级
 * status：班级状态
 * checkLogged：检测是否已登录
 * userInfo：返回用户个人信息
 */
$act = $_POST['act'];

switch ($act) {
	case 'register':
		register();
		break;
	case 'login':
		login();
		break;
	case 'createClass':
		createClass();
		break;
	case 'classEdit':
		classEdit();
		break;
	case 'joinClass':
		joinClass();
		break;
	case 'revoke':
		revoke();
		break;
	case 'status':
		status();
		break;
	case 'checkLogged':
		checkLogged();
		break;
	case 'userInfo':
		userInfo();
		break;
}


/**
 * 注册
 * 接收POST参数
 * @return 【字符串：状态码】 	 
 * 			0：数据库写入记录失败 
 * 			1：数据库写入记录成功
 * 			2：记录已存在
 * 			-1：为传入sid（学号）
 */
function register(){
	if (isset($_POST['sid'])){
		$sid = $_POST['sid'];
		$time = time();
		$username = $_POST['username'];
		$password = password_hash($_POST['password'].'`~!@#$%^&*', PASSWORD_DEFAULT);

		$sel = "SELECT COUNT(`id`) FROM `".PREFIX.USER."` WHERE `sid` = {$sid}";
		$sql = mysql_query($sel);
		$num = mysql_fetch_array($sql)[0];

		if ($num == 0) {
			$ins = "INSERT INTO `".PREFIX.USER."` (`sid`, `username`, `password`, `create_time`, `update_time`) VALUES ({$sid}, '{$username}', '{$password}', {$time}, {$time})";
			$res = mysql_query($ins);

			if ($res){
				// 为用户新建文件夹：如果文件夹不存在的话
				if (!file_exists(getCode(ROOT.'data/home/'.$sid)))
					mkdir(getCode(ROOT.'data/home/'.$sid), 0777);

				$result['status'] = 1;
			} else {
				$result['status'] = 0;
			}
		} else {
			$result['status'] = 2;
		}
	} else {
		$result['status'] = -1;
	}
	echo json_encode($result);
}


/**
 * 登录
 * 注：登录成功会在SESSIO里记录uid、sid、username、lv
 * 接收POST参数
 * @return 【字符串：状态码】
 * 			0:学号不存在或密码错误
 * 			1：登录成功
 * 			-1：未传入学号
 */
function login(){
	if (isset($_POST['sid'])){
		$sid = $_POST['sid'];
		$password = $_POST['password'].'`~!@#$%^&*';

		$sel = "SELECT `id`, `sid`, `username`, `password`, `lv` FROM `".PREFIX.USER."` WHERE `sid` = {$sid} AND `status` = 1";
		$selSql = mysql_query($sel);
		$selRow = mysql_fetch_assoc($selSql);

		if ($selSql){
			// password_verify验证密码是否和散列值匹配
			if (password_verify($password, $selRow['password'])){
				session_start();
				$_SESSION['uid'] = $selRow['id'];
				$_SESSION['sid'] = $selRow['sid'];
				$_SESSION['username'] = $selRow['username'];
				$_SESSION['lv'] = $selRow['lv'];
				$result['status'] = 1;
			} else {
				$result['status'] = 0;
			}
		} else {
			$result['status'] = 0;
		}
	} else {
		$result['status'] = -1;
	}
	echo json_encode($result);
}



/**
 * 申请(新建)班级——登录后
 * 接收POST参数，
 * 		grade:学年; 
 * 		classname:班级名;
 * 		num:学生人数
 * 		instructor：辅导员
 * @return 【字符串：状态码】
 * 			1:成功
 * 			0：失败
 */
function createClass(){
	$uid = $_SESSION['uid'];
	$grade = $_POST['grade'];
	$classname = $_POST['classname'];
	$num = $_POST['num'];
	$instructor = $_POST['instructor'];
	$time = time();

	$ins = "INSERT INTO `".PREFIX.CLA."` (`classname`, `grade`, `uid`, `num`, `instructor`, `create_time`, `update_time`) VALUES ('$classname', {$grade}, {$uid}, {$num}, '{$instructor}', $time, $time)";
	$sql = mysql_query($ins);

	$result['status'] = $sql ? 1 : 0;
	echo json_encode($result);
}



/**
 * 修改班级信息——登录后
 * 接收POST参数，
 * 		id:班级id
 * 		grade:学年
 * 		classname:班级名
 * 		num:学生人数
 * 		instructor:辅导员
 * @return 【字符串：状态码】
 * 			1:成功
 * 			0:失败
 */
function classEdit(){
	$id = $_POST['id'];
	$grade = $_POST['grade'];
	$classname = $_POST['classname'];
	$num = $_POST['num'];
	$instructor = $_POST['instructor'];
	$time = time();

	$upd = "UPDATE `".PREFIX.CLA."` SET `grade` = {$grade}, `classname` = '{$classname}', `num` = {$num}, `instructor` = '{$instructor}' WHERE `id` = {$id}";
	$sql = mysql_query($upd);

	$result['status'] = $sql ? 1 : 0;
	echo json_encode($result);
}



/**
 * 加入班级——登录后
 * 注：加入班级需要老师审核，此时status=0,审核通过status=1
 * 接收POST参数：
 * 		cid:用户id
 * @return 【字符串：状态码】
 * 		1:加入成功
 * 		0:加入失败
 * 		2:已存在
 */
function joinClass(){
	$uid = $_SESSION['uid'];
	$cid = $_POST['cid'];
	$time = time();

	$sel = "SELECT COUNT(`id`) FROM `".PREFIX.MYCLA."` WHERE `uid` = {$uid} AND `cid` = {$cid}";
	$sql = mysql_query($sel);
	$num = mysql_fetch_array($sql)[0];

	if ($num == 0) {
		$ins = "INSERT INTO `".PREFIX.MYCLA."` (`cid`, `uid`, `create_time`, `update_time`) VALUES ({$cid}, {$uid}, {$time}, {$time})";
		$sql = mysql_query($ins);

		$result['status'] = $sql ? 1 : 0;
	} else {
		$result['status'] = 2;
	}
	echo json_encode($result);
}



/**
 * 撤销申请/取消加入班级——登录后
 * 接收POST参数：
 * 		type:user/class
 * @return 【字符串/状态码】
 * 		1:操作成功
 * 		0:操作失败
 */
function revoke(){
	$id = $_POST['id'];
	$type = $_POST['type'];

	if ($type == 'class') {
		$table = PREFIX.CLA;
	} else if ($type == 'user') {
		$table = PREFIX.MYCLA;
	}

	$del = "DELETE FROM `{$table}` WHERE `id` = {$id}";
	$sql = mysql_query($del);

	$result['status'] = $sql ? 1 : 0;
	echo json_encode($result);
}



/**
 * 班级状态，表示班级审核通过或班级未经审核或被关闭
 * 接收POST参数：
 * 		type:user/class
 * 		status:on/off
 * 		id:用户id
 * @return 【字符串/状态码】
 * 		1:操作成功
 * 		0:操作失败
 */
function status(){
	$type = $_POST['type'];
	$status = $_POST['status'];
	$id = $_POST['id'];
	$time = time();

	if ($type == 'class') {
		$table = PREFIX.CLA;
	} else if ($type == 'user') {
		$table = PREFIX.MYCLA;
	}

	if ($status == 'on') {
		$upd = "UPDATE `{$table}` SET `status` = 0, `update_time` = {$time} WHERE `id` = {$id}";
	} else if ($status == 'off') {
		$upd = "UPDATE `{$table}` SET `status` = 1, `update_time` = {$time} WHERE `id` = {$id}";
	}
	$sql = mysql_query($upd);

	$result['status'] = $sql ? 1 : 0;
	echo json_encode($result);
}



function checkLogged(){
	if(isset($_SESSION['uid']) && isset($_SESSION['sid']) && isset($_SESSION['username']) && isset($_SESSION['lv'])){
		$result['status'] = 1;
	}else{
		$result['status'] = 0;
	}
	echo json_encode($result);
}


function userInfo(){
	$result['sid'] = $_SESSION['sid'];
	$result['username'] = $_SESSION['username'];
	$result['lv'] = $_SESSION['lv'];
	echo json_encode($result);
}