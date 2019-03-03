<?php 

include '../public/conf/connect.php';

$act = $_POST['act'];

switch ($act) {
	case 'count':
		countData();
		break;
	case 'user':
		userData();
		break;
	case 'lesson':
		lessonData();
		break;
	case 'public':
		publicData();
		break;
	case 'collect':
		collectData();
		break;
	case 'delete':
		delData();
		break;
	case 'classAudit':
		classAuditData();
		break;
	case 'userAudit':
		userAuditData();
		break;
}



/**
 * 数据统计
 * 接收SESSION参数
 * 		uid:用户id
 * 		lv:权限
 * @return 【字符串：数组】
 * 		{folderAllNum:文件夹总数
 * 		fileAllNum:文件总数
 * 		collectAllNum:所有用户总收藏数
 * 		memberNum:用户总数
 * 		delAllNum:回收站的总数
 * 		spaceAllNum:已用空间大小}
 * 		folderNum:用户文件夹总数
 * 		fileNum:用户文件总数
 * 		collectNum:用户收藏总数
 * 		delNum:用户回收站总数
 * 		spaceNum:用户已用空间大小
 * 		spaceMaxNum:用户空间总大小
 */
function countData(){
	$uid = $_SESSION['uid'];
	$lv = $_SESSION['lv'];

	// 统计各个用户的文件夹数量
	$folderSel = "SELECT COUNT(`id`) FROM `".PREFIX.FOLDER."` WHERE `uid` = {$uid} AND `delete_time` = 0 AND `is_pub` = 0";
	$folderSql = mysql_query($folderSel);
	$folderNum = $folderSql ? mysql_fetch_array($folderSql)[0] : 0;

	// 统计各个用户的文件数量
	$fileSel = "SELECT COUNT(`id`) FROM `".PREFIX.FILE."` WHERE `uid` = {$uid} AND `delete_time` = 0 AND `is_pub` = 0";
	$fileSql = mysql_query($fileSel);
	$fileNum = $fileSql ? mysql_fetch_array($fileSql)[0] : 0;

	// 统计各个用户的收藏数量
	$collectSel = "SELECT COUNT(`id`) FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid}";
	$collectSql = mysql_query($collectSel);
	$collectNum = $collectSql ? mysql_fetch_array($collectSql)[0] : 0;

	// 统计回收站的数量
	$folderSel = "SELECT COUNT(`id`) FROM `".PREFIX.FOLDER."` WHERE `uid` = {$uid} AND `delete_time` != 0";
	$fileSel = "SELECT COUNT(`id`) FROM `".PREFIX.FILE."` WHERE `uid` = {$uid} AND `delete_time` != 0";
	$folderSql = mysql_query($folderSel);
	$fileSql = mysql_query($fileSel);
	$folderDelNum = $folderSql ? mysql_fetch_array($folderSql)[0] : 0;
	$fileDelNum = $fileSql ? mysql_fetch_array($fileSql)[0] : 0;
	$delNum = $folderDelNum + $fileDelNum;

	// 用户已用空间 / 最大可用空间
	$spaceSel = "SELECT `space`, `max_space` FROM `".PREFIX.USER."` WHERE `id` = {$uid}";
	$spaceSql = mysql_query($spaceSel);

	while ($row = mysql_fetch_assoc($spaceSql)) {
		$spaceNum = $row['space'];
		$spaceMaxNum = $row['max_space'];
	}

	$spaceNum = formatBytes($spaceNum);
	$spaceMaxNum = formatBytes($spaceMaxNum);

	if ($lv > 0) {
		// 统计所有用户的文件夹数量
		$folderAllSel = "SELECT COUNT(`id`) FROM `".PREFIX.FOLDER."` WHERE `delete_time` = 0 AND `is_pub` = 0";
		$folderAllSql = mysql_query($folderAllSel);
		$folderAllNum = $folderAllSql ? mysql_fetch_array($folderAllSql)[0] : 0;

		// 统计所有用户的文件数量
		$fileAllSel = "SELECT COUNT(`id`) FROM `".PREFIX.FILE."` WHERE `delete_time` = 0 AND `is_pub` = 0";
		$fileAllSql = mysql_query($fileAllSel);
		$fileAllNum = $fileAllSql ? mysql_fetch_array($fileAllSql)[0] : 0;

		// 统计所有用户的收藏数量
		$collectAllSel = "SELECT COUNT(`id`) FROM `".PREFIX.COLLECT."`";
		$collectAllSql = mysql_query($collectAllSel);
		$collectAllNum = $collectAllSql ? mysql_fetch_array($collectAllSql)[0] : 0;

		// 统计用户的数量
		$memberSel = "SELECT COUNT(`id`) FROM `".PREFIX.USER."`";
		$memberSql = mysql_query($memberSel);
		$memberNum = $memberSql ? mysql_fetch_array($memberSql)[0] : 0;

		// 统计所有回收站的数量
		$folderAllSel = "SELECT COUNT(`id`) FROM `".PREFIX.FOLDER."` WHERE `delete_time` != 0";
		$fileAllSel = "SELECT COUNT(`id`) FROM `".PREFIX.FILE."` WHERE `delete_time` != 0";
		$folderAllSql = mysql_query($folderAllSel);
		$fileAllSql = mysql_query($fileAllSel);
		$folderAllDelNum = $folderAllSql ? mysql_fetch_array($folderAllSql)[0] : 0;
		$fileAllDelNum = $fileAllSql ? mysql_fetch_array($fileAllSql)[0] : 0;
		$allDelNum = $folderAllDelNum + $fileAllDelNum;

		$spaceAllSel = "SELECT `space` FROM `".PREFIX.USER."`";
		$spaceAllSql = mysql_query($spaceAllSel);

		while ($spaceAllRow = mysql_fetch_assoc($spaceAllSql)) {
			$spaceAllNum += $spaceAllRow['space'];
		}

		$spaceAllNum = formatBytes($spaceAllNum);

		$result['folderAllNum'] = $folderAllNum;
		$result['fileAllNum'] = $fileAllNum;
		$result['collectAllNum'] = $collectAllNum;
		$result['memberNum'] = $memberNum;
		$result['delAllNum'] = $allDelNum;
		$result['spaceAllNum'] = $spaceAllNum;
	}

	$result['folderNum'] = $folderNum;
	$result['fileNum'] = $fileNum;
	$result['collectNum'] = $collectNum;
	$result['delNum'] = $delNum;
	$result['spaceNum'] = $spaceNum;
	$result['spaceMaxNum'] = $spaceMaxNum;

	echo json_encode($result);
}



/**
 * 用户目录列表
 * 接收SESSION参数
 * 		uid:学生id
 * 		sid:学号
 * @return 【字符串：详细信息】
 */
function userData(){

	$uid = $_SESSION['uid'];
	$sid = $_SESSION['sid'];
	$fid = $_POST['fid'];

	$result['fid'] = $fid;

	// 获取用户目录数据
	$folderSel = "SELECT `id`, `fid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FOLDER."` WHERE `uid` = {$uid} AND `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 0 AND `is_les` = 0";
	$folderSql = mysql_query($folderSel);
	$folderNum = mysql_num_rows($folderSql);
	$folderNum = $folderSql ? $folderNum : 0;

	while ($folderRow = mysql_fetch_assoc($folderSql)){
		$result['folder'][] = $folderRow;
	}

	for ($i = 0; $i < count($result['folder']); $i++) {
		$result['folder'][$i]['create_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['create_time']);
		$result['folder'][$i]['update_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['update_time']);
	}

	$fileSel = "SELECT `id`, `fid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FILE."` WHERE `uid` = {$uid} AND `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 0 AND `is_les` = 0";
	$fileSql = mysql_query($fileSel);
	$fileNum = mysql_num_rows($fileSql);
	$fileNum = $fileSql ? $fileNum : 0;

	while ($fileRow = mysql_fetch_assoc($fileSql)){
		$result['file'][] = $fileRow;
	}

	for ($i = 0; $i < count($result['file']); $i++) {
		$result['file'][$i]['create_time'] = date('Y-m-d H:i:s', $result['file'][$i]['create_time']);
		$result['file'][$i]['update_time'] = date('Y-m-d H:i:s', $result['file'][$i]['update_time']);
	}

	$result['num'] = $folderNum + $fileNum;

	if ($fid > 0){
		$folderPathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$folderPathSql = mysql_query($folderPathSel);
		$folderPathRow = mysql_fetch_assoc($folderPathSql);
		$result['path'] = substr($folderPathRow['path'], strpos($folderPathRow['path'], $sid));

		while ($fid != 0){
			$result['url'][] = (int)$fid;
			$urlIdSel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$urlIdSql = mysql_query($urlIdSel);
			$urlIdRow = mysql_fetch_assoc($urlIdSql);
			$fid = $urlIdRow['fid'];
		}
	} else {
		$result['path'] = $sid;
	}
	echo json_encode($result);
}



/**
 * 课业目录列表
 * 接收SESSION参数
 * 		uid:学生id
 * 		sid:学号
 * 		lv:权限
 * @return 【字符串：详细信息】
 */
function lessonData(){
	$uid = $_SESSION['uid'];
	$sid = $_SESSION['sid'];
	$lv = $_SESSION['lv'];
	$fid = $_POST['fid'];

	$result['fid'] = $fid;
	$result['uid'] = $uid;
	$result['lv'] = $lv;

	// 设置cid
	if ($fid != 0) {
		$sel = "SELECT `is_les` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$sql = mysql_query($sel);
		$cidStr = mysql_fetch_array($sql)[0];
	} else if ($fid == 0) {
		// 查询用户所在的班级
		// 学生和老师可能同时加入多个班级，学生会发生分班，老师教授多个班级
		$sel = "SELECT `cid` FROM `".PREFIX.MYCLA."` AS m INNER JOIN `".PREFIX.CLA."` AS c ON `cid` = c.`id` WHERE m.`uid` = {$uid} AND m.`status` = 1 AND c.`status` = 1";
		$sql = mysql_query($sel);

		while ($row = mysql_fetch_array($sql)[0]) {
			$cid[] = $row;
		}

		// cidStr若干个班级号
		$cidStr = implode(', ', $cid);
		$cidStr = rtrim($cidStr, ', ');
	}
	$result['cid'] = strpos($cidStr, ',') ? 0 : $cidStr;

	// 获取课业目录数据
	if ($fid == 0 || $lv > 0) {
		$folderSel = "SELECT `id`, `fid`, `uid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FOLDER."` WHERE `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 0 AND `is_les` IN ({$cidStr})";
	} else {// lv>0 OR `uid` = {$uid}  lv>0是为了筛掉学生自己的文件或文件夹，显示老师的;  `is_les` = {$cidStr}，进入到else语句即进入了二级目录，$cidStr成了一个确定的值
		$folderSel = "SELECT f.`id`, `fid`, `uid`, `name`, `size`, f.`create_time`, f.`update_time` FROM `".PREFIX.FOLDER."` AS f INNER JOIN `".PREFIX.USER."` AS u ON `uid` = u.`id` WHERE (`lv` > 0 OR `uid` = {$uid}) AND `fid` = {$fid} AND `delete_time` = 0 AND `is_les` = {$cidStr}";
	}
	$folderSql = mysql_query($folderSel);
	$folderNum = mysql_num_rows($folderSql);
	$folderNum = $folderSql ? $folderNum : 0;

	while ($folderRow = mysql_fetch_assoc($folderSql)){
		$result['folder'][] = $folderRow;
	}

	for ($i = 0; $i < count($result['folder']); $i++) {
		$result['folder'][$i]['create_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['create_time']);
		$result['folder'][$i]['update_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['update_time']);
	}

	// 道理同上
	if ($fid == 0 || $lv > 0) {
		$fileSel = "SELECT `id`, `fid`, `uid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FILE."` WHERE `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 0 AND `is_les` IN ({$cidStr})";
	} else {
		$fileSel = "SELECT f.`id`, `fid`, `uid`, `name`, `size`, f.`create_time`, f.`update_time` FROM `".PREFIX.FILE."` AS f INNER JOIN `".PREFIX.USER."` AS u ON uid = u.`id` WHERE (`lv` > 0 OR `uid` = {$uid}) AND `fid` = {$fid} AND `delete_time` = 0 AND `is_les` = {$cidStr}";
	}
	$fileSql = mysql_query($fileSel);
	$fileNum = mysql_num_rows($fileSql);
	$fileNum = $fileSql ? $fileNum : 0;

	while ($fileRow = mysql_fetch_assoc($fileSql)){
		$result['file'][] = $fileRow;
	}

	for ($i = 0; $i < count($result['file']); $i++) {
		$result['file'][$i]['create_time'] = date('Y-m-d H:i:s', $result['file'][$i]['create_time']);
		$result['file'][$i]['update_time'] = date('Y-m-d H:i:s', $result['file'][$i]['update_time']);
	}

	$result['num'] = $folderNum + $fileNum;

	if ($fid > 0){
		$folderPathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$folderPathSql = mysql_query($folderPathSel);
		$folderPathRow = mysql_fetch_assoc($folderPathSql);
		$result['path'] = substr($folderPathRow['path'], strpos($folderPathRow['path'], 'home/')+5);

		while ($fid != 0){
			$result['url'][] = (int)$fid;
			$urlIdSel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$urlIdSql = mysql_query($urlIdSel);
			$urlIdRow = mysql_fetch_assoc($urlIdSql);
			$fid = $urlIdRow['fid'];
		}
	} else {
		$result['path'] = $sid;
	}
	echo json_encode($result);
}



/**
 * 公共目录列表
 * 接收SESSION参数
 * 		uid:学生id
 * 		lv:权限
 * 		fid:当前所在文件夹id
 * @return 【字符串：详细信息】
 */
function publicData(){
	$uid = $_SESSION['uid'];
	$lv = $_SESSION['lv'];
	$fid = $_POST['fid'];

	$result['fid'] = $fid;
	$result['lv'] = $lv;
	$result['uid'] = $uid;

	// 获取公共目录数据
	$folderSel = "SELECT `id`, `fid`, `uid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FOLDER."` WHERE `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 1";
	$folderSql = mysql_query($folderSel);
	$folderNum = mysql_num_rows($folderSql);
	$folderNum = $folderSql ? $folderNum : 0;

	while ($folderRow = mysql_fetch_assoc($folderSql)){
		$result['folder'][] = $folderRow;
	}

	for ($i = 0; $i < count($result['folder']); $i++) {
		$result['folder'][$i]['create_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['create_time']);
		$result['folder'][$i]['update_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['update_time']);
	}

	$fileSel = "SELECT `id`, `fid`, `uid`, `name`, `size`, `create_time`, `update_time` FROM `".PREFIX.FILE."` WHERE `delete_time` = 0 AND `fid` = {$fid} AND `is_pub` = 1";
	$fileSql = mysql_query($fileSel);
	$fileNum = mysql_num_rows($fileSql);
	$fileNum = $fileSql ? $fileNum : 0;

	while ($fileRow = mysql_fetch_assoc($fileSql)){
		$result['file'][] = $fileRow;
	}

	for ($i = 0; $i < count($result['file']); $i++) {
		$result['file'][$i]['create_time'] = date('Y-m-d H:i:s', $result['file'][$i]['create_time']);
		$result['file'][$i]['update_time'] = date('Y-m-d H:i:s', $result['file'][$i]['update_time']);
	}

	$result['num'] = $folderNum + $fileNum;

	// 获取用户是否已有收藏数据
	$folderColSel = "SELECT c.`fid` FROM `".PREFIX.COLLECT."` AS c INNER JOIN `".PREFIX.FOLDER."` AS f ON c.`fid` = f.`id` WHERE c.`uid` = {$uid} AND `type` = 'folder' AND f.`fid` = {$fid}";
	$folderColSql = mysql_query($folderColSel);
	while ($folderColRow = mysql_fetch_assoc($folderColSql)) {
		$result['folderId'][] = $folderColRow['fid'];
	}

	$fileColSel = "SELECT c.`fid` FROM `".PREFIX.COLLECT."` AS c INNER JOIN `".PREFIX.FILE."` AS f ON c.`fid` = f.`id` WHERE c.`uid` = {$uid} AND `type` = 'file' AND f.`fid` = {$fid}";
	$fileColSql = mysql_query($fileColSel);
	while ($fileColRow = mysql_fetch_assoc($fileColSql)) {
		$result['fileId'][] = $fileColRow['fid'];
	}

	if ($fid > 0){
		$folderPathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$folderPathSql = mysql_query($folderPathSel);
		$folderPathRow = mysql_fetch_assoc($folderPathSql);
		$result['path'] = substr($folderPathRow['path'], strpos($folderPathRow['path'], 'public/'));

		while ($fid != 0){
			$result['url'][] = (int)$fid;
			$urlIdSel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$urlIdSql = mysql_query($urlIdSel);
			$urlIdRow = mysql_fetch_assoc($urlIdSql);
			$fid = $urlIdRow['fid'];
		}
	} else {
		$result['path'] = 'public';
	}
	echo json_encode($result);
}



/**
 * 收藏夹列表
 * 接收SESSION参数
 * 		uid:学生id
 * 		fid:当前所在文件夹id
 * @return 【字符串：详细信息】
 */
function collectData(){
	$uid = $_SESSION['uid'];
	$fid = $_POST['fid'];

	$folderSel = "SELECT f.`id`, f.`fid`, c.`uid`, `name`, `size`, c.`create_time` FROM `".PREFIX.COLLECT."` AS c INNER JOIN `".PREFIX.FOLDER."` AS f ON c.`fid` = f.`id` WHERE c.`uid` = {$uid} AND `type` = 'folder' AND f.`fid` = {$fid}";
	$fileSel = "SELECT f.`id`, f.`fid`, c.`uid`, `name`, `size`, c.`create_time` FROM `".PREFIX.COLLECT."` AS c INNER JOIN `".PREFIX.FILE."` AS f ON c.`fid` = f.`id` WHERE c.`uid` = {$uid} AND `type` = 'file' AND f.`fid` = {$fid}";
	$folderSql = mysql_query($folderSel);
	$fileSql = mysql_query($fileSel);


	$folderNum = mysql_num_rows($folderSql);
	$folderNum = $folderSql ? $folderNum : 0;
	$fileNum = mysql_num_rows($fileSql);
	$fileNum = $fileSql ? $fileNum : 0;
	$result['num'] = $folderNum + $fileNum;

	while ($folderRow = mysql_fetch_assoc($folderSql)) {
		$result['folder'][] = $folderRow;
	}
	while ($fileRow = mysql_fetch_assoc($fileSql)) {
		$result['file'][] = $fileRow;
	}

	for ($i = 0; $i < count($result['folder']); $i++) {
		$result['folder'][$i]['create_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['create_time']);
	}
	for ($i = 0; $i < count($result['file']); $i++) {
		$result['file'][$i]['create_time'] = date('Y-m-d H:i:s', $result['file'][$i]['create_time']);
	}

	if ($fid > 0){
		$folderPathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$folderPathSql = mysql_query($folderPathSel);
		$folderPathRow = mysql_fetch_assoc($folderPathSql);
		$result['path'] = substr($folderPathRow['path'], strpos($folderPathRow['path'], 'public/'));

		while ($fid != 0){
			$result['url'][] = (int)$fid;
			$urlIdSel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$urlIdSql = mysql_query($urlIdSel);
			$urlIdRow = mysql_fetch_assoc($urlIdSql);
			$fid = $urlIdRow['fid'];
		}
	} else {
		$result['path'] = 'public';
	}

	echo json_encode($result);
}



/**
 * 回收站列表
 * 接收SESSION参数
 * 		uid:学生id
 * @return 【字符串：详细信息】
 */
function delData(){
	$uid = $_SESSION['uid'];

	// 获取回收站文件夹数据
	$sel = "SELECT `id`, `fid` FROM `".PREFIX.FOLDER."` WHERE `uid` = {$uid} AND `delete_time` != 0";
	$sql = mysql_query($sel);

	// 判断文件夹是否有包含关系
	while ($row = mysql_fetch_assoc($sql)) {
		$folder_id_fid[] = $row['id'].'-'.$row['fid'];
	}

	for ($i = 0; $i < count($folder_id_fid); $i++) {
		$folder_id_fid_tmp[] = explode('-', $folder_id_fid[$i]);
	}

	for ($i = 0; $i < count($folder_id_fid_tmp); $i++) {
		$folder_id[] = $folder_id_fid_tmp[$i][0];
		$folder_fid[] = $folder_id_fid_tmp[$i][1];
	}

	for ($i = 0; $i < count($folder_fid); $i++) {
		if (!in_array($folder_fid[$i], $folder_id)) {
			$sel = "SELECT `id`, `fid`, `name`, `size`, `delete_time` FROM `".PREFIX.FOLDER."` WHERE `id` = {$folder_id[$i]}";
			$sql = mysql_query($sel);
			while ($row = mysql_fetch_assoc($sql)) {
				$result['folder'][] = $row;
			}
		}
	}

	// 获取回收站文件数据
	$sel = "SELECT `id`, `fid` FROM `".PREFIX.FILE."` WHERE `uid` = {$uid} AND `delete_time` != 0";
	$sql = mysql_query($sel);

	// 判断文件是否有被包含关系
	while ($row = mysql_fetch_assoc($sql)) {
		$file_id_fid[] = $row['id'].'-'.$row['fid'];
	}

	for ($i = 0; $i < count($file_id_fid); $i++) {
		$file_id_fid_tmp[] = explode('-', $file_id_fid[$i]);
	}

	for ($i = 0; $i < count($file_id_fid_tmp); $i++) {
		$file_id[] = $file_id_fid_tmp[$i][0];
		$file_fid[] = $file_id_fid_tmp[$i][1];
	}

	for ($i = 0; $i < count($file_fid); $i++) {
		if (!in_array($file_fid[$i], $folder_id)) {
			$sel = "SELECT `id`, `fid`, `name`, `size`, `delete_time` FROM `".PREFIX.FILE."` WHERE `id` = {$file_id[$i]}";
			$sql = mysql_query($sel);
			while ($row = mysql_fetch_assoc($sql)) {
				$result['file'][] = $row;
			}
		}
	}

	// 转换时间格式
	for ($i = 0; $i < count($result['folder']); $i++) {
		$result['folder'][$i]['delete_time'] = date('Y-m-d H:i:s', $result['folder'][$i]['delete_time']);
	}

	for ($i = 0; $i < count($result['file']); $i++) {
		$result['file'][$i]['delete_time'] = date('Y-m-d H:i:s', $result['file'][$i]['delete_time']);
	}

	$folderNum = count($result['folder']);
	$fileNum = count($result['file']);
	$result['num'] = $folderNum + $fileNum;

	echo json_encode($result);
}



/**
 * 班级列表
 * 无参数
 * @return 【字符串：详细信息】
 */
function classAuditData(){
	$sel = "SELECT c.`id`, `grade`, `classname`, `num`, `instructor`, c.`create_time`, c.`update_time`, c.`status`, `sid`, `username` FROM `".PREFIX.CLA."` AS c INNER JOIN `".PREFIX.USER."` AS u ON `uid` = u.`id`";
	$sql = mysql_query($sel);
	$num = mysql_num_rows($sql);

	while ($row = mysql_fetch_assoc($sql)) {
		$result['class'][] = $row;
	}

	for ($i = 0; $i < count($result['class']); $i++) {
		$result['class'][$i]['create_time'] = date('Y-m-d H:i:s', $result['class'][$i]['create_time']);
		$result['class'][$i]['update_time'] = date('Y-m-d H:i:s', $result['class'][$i]['update_time']);
	}

	$result['num'] = $num;
	echo json_encode($result);
}



/**
 * 班级成员审核列表
 * 无参数
 * @return 【字符串：详细信息】
 */
function userAuditData(){
	$where = !empty($_POST['cid']) ? "`cid` = {$_POST['cid']}" : "m.`status` = 0";

	$sel = "SELECT m.`id`, `sid`, `username`, m.`status`, `grade`, `classname`, m.`create_time`, m.`update_time` FROM `".PREFIX.MYCLA."` AS m INNER JOIN `".PREFIX.CLA."` AS c ON `cid` = c.`id` INNER JOIN `".PREFIX.USER."` AS u ON m.`uid` = u.`id` WHERE {$where}";
	$sql = mysql_query($sel);
	$num = mysql_num_rows($sql);

	while ($row = mysql_fetch_assoc($sql)) {
		$result['user'][] = $row;
	}

	for ($i = 0; $i < count($result['user']); $i++) {
		$result['user'][$i]['create_time'] = date('Y-m-d H:i:s', $result['user'][$i]['create_time']);
		$result['user'][$i]['update_time'] = date('Y-m-d H:i:s', $result['user'][$i]['update_time']);
	}

	$result['num'] = $num;
	echo json_encode($result);
}



// 转换字节单位
function formatBytes($size) {
	$units = array('B', 'K', 'M', 'G', 'T');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	return round($size, 2).$units[$i];
}