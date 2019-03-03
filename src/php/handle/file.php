<?php 

include '../public/conf/connect.php';
include 'function.php';

$act = $_POST['act'];

switch ($act) {
	case 'create':
		create();
		break;
	case 'rechristen':
		rechristen();
		break;
	case 'del':
		del();
		break;
	case 'restore':
		restore();
		break;
	case 'checkFile':
		checkFile();
		break;
	case 'checkBlock':
		checkBlock();
		break;
	case 'upload':
		upload();
		break;
	case 'merge':
		merge();
		break;
	case 'uploadSuccess':
		uploadSuccess();
		break;
	case 'edit':
		edit();
		break;
	case 'save':
		save();
		break;
	case 'collect':
		collect();
		break;
}



/**
 * 新建文件/文件夹
 * 接收SESSION参数
 * 		uid:用户id
 * 		sid:学号,用于给文件夹命名
 * 		lv:用户权限,
 *  接收POST参数
 * 		cid:判断是否为课业目录
 * 		fid:当前所在目录的id
 * 		method:所创建的文件/文件夹所在环境->私有||公共||课业
 * 		type:文件类型,file或folder
 * 		ext:文件扩展名
 * 		name:文件名
 * @return 【字符串：状态码】
 * 		0:创建失败
 * 		1:创建成功
 * 		2:文件已存在于私有目录中
 * 		3:用户权限不足
 * 		4:文件已存在于回收站中（delete_time）
 * 		5:文件已存在于课业目录中
 * 		6:文件已存在于公共目录中
 */

function create(){
	$uid = $_SESSION['uid'];
	$sid = $_SESSION['sid'];
	$lv = $_SESSION['lv'];
	$cid = (isset($_POST['cid']) && !empty($_POST['cid'])) ? $_POST['cid'] : 0;
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$type = $_POST['type'];
	$ext = $_POST['ext'];
	$name = $_POST['name'];
	$time = time();

	if ($type == 'file') {
		$table = PREFIX.FILE;
		$name = $name.'.'.$ext;
	} else if ($type == 'folder') {
		$table = PREFIX.FOLDER;
	}

	if ($method == 'user') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `uid` = {$uid} AND `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
	} else if ($method == 'public') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 1";
	} else if ($method == 'lesson') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
	}

	$sql = mysql_query($sel);
	$num = mysql_num_rows($sql);

	if ($num == 0) {
		if ($fid == 0) {
			if ($method == 'user' || ($method == 'lesson' && $lv > 0)) {
				// 课业目录权限高
				$folderPath = ROOT.'data/home/'.$sid;
			} else if ($method == 'public') {
				$folderPath = ROOT.'data/public';
			} else {
				$level = 'low';
			}
		} else {
			$sel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$sql = mysql_query($sel);
			$folderPath = mysql_fetch_array($sql)[0];
		}

		if (!isset($level) && $folderPath) {
			
			if($method == "public"){
				$path = $folderPath.'/'.$name;
			}else{
				$path = $fid == 0 ? $folderPath : $folderPath.'/'.$name;
			}

			if ($fid>0 && $type == 'file') {
				$res = touch(getCode($path));
			} else if ($type == 'folder') {
				$res = mkdir(getCode($path), 0777);
			}

			if (isset($res)) {
				if ($method == 'user') {
					$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time})";
				} else if ($method == 'public') {
					$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`, `is_pub`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time}, 1)";
				} else if ($method == 'lesson') {
					$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`, `is_les`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time}, {$cid})";
				}
				mysql_query($ins);
				$result['status'] = 1;
			} else {
				$result['status'] = 0;
			}
		} else {
			$result['status'] = $folderPath ? 3 : 0;
		}
	} else {
		$row = mysql_fetch_assoc($sql);
		if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 0) {
			$result['status'] = 2;
		} else if ($row['delete_time'] == 0 && $row['is_les'] != 0 && $row['is_pub'] == 0) {
			$result['status'] = 5;
		} else if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 1) {
			$result['status'] = 6;
		} else {
			$result['status'] = 4;
		}
	}

	echo json_encode($result);

	// 备份
	/* if (defined('BACKUP') && !empty(BACKUP) && $result['status'] == 1) {
		$url = BACKUP.'handle/backup.php';
		$curlData = array(
			'act' => $_POST['act'],
			'uid' => $uid,
			'sid' => $sid,
			'lv' => $lv,
			'cid' => $cid,
			'fid' => $fid,
			'method' => $method,
			'type' => $type,
			'path' => $folderPath,
			'name' => $name
		);
		$res = curlPost($url, $curlData);
		if ($res == 1) {
			$upd = "UPDATE `{$table}` SET `is_backup` = 1 WHERE `path` = '{$path}'";
			mysql_query($upd);
		}
	} */
}



/**
 * 重命名文件/文件夹
 * 接收SESSION参数
 * 		uid:用户id
 * 接收POST参数
 * 		fid:文件或文件夹的上级目录id
 * 		method:所创建的文件/文件夹所在环境->私有||公共||课业
 * 		id:当前文件或文件夹的id
 * 		type:文件类型,file或folder
 * 		name:文件名
 * @return 【字符串：状态码】
 * 		1:更改成功
 * 		2:文件已存在于私有目录中
 * 		4:文件已存在于回收站中（delete_time）
 * 		5:文件已存在于课业目录中
 * 		6:文件已存在于公共目录中
 * 		update_time:更改时间
 */
function rechristen(){
	$uid = $_SESSION['uid'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$id = $_POST['id'];
	$type = $_POST['type'];
	$name = $_POST['name'];
	$time = time();

	if ($type == 'file') {
		$table = PREFIX.FILE;
	} else if ($type == 'folder') {
		$table = PREFIX.FOLDER;
	}

	if ($method == 'user') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `uid` = {$uid} AND `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
	} else if ($method == 'public') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 1";
	} else if ($method == 'lesson') {
		$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `{$table}` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
	}

	$sql = mysql_query($sel);
	$num = mysql_num_rows($sql);

	if ($num == 0) {
		$sel = "SELECT `path` FROM `{$table}` WHERE `id` = {$id}";
		$sql = mysql_query($sel);
		$path = mysql_fetch_array($sql)[0];
		$newPath = substr($path, 0, strrpos($path, '/')+1).$name;
		$relatedFolderId[] = $id;
		$relatedFolderPath[] = $newPath;

		$res = rename(getCode($path), getCode($newPath));

		if ($res) {
			$upd = "UPDATE `{$table}` SET `name` = '{$name}', `path` = '{$newPath}', `update_time` = {$time} WHERE `id` = {$id}";
			mysql_query($upd);

			// 更新相关文件夹
			$sel = "SELECT `id`, `path` FROM `".PREFIX.FOLDER."` WHERE `path` LIKE '{$path}/%' AND `id` != {$id}";
			$sql = mysql_query($sel);
			while ($row = mysql_fetch_assoc($sql)) {
				$relatedFolderId[] = $row['id'];
				$relatedFolderPath[] = $newPath.substr($row['path'], strlen($path));
			}

			for ($i = 1; $i < count($relatedFolderId); $i++) {
				$upd = "UPDATE `".PREFIX.FOLDER."` SET `path` = '{$relatedFolderPath[$i]}' WHERE `id` = {$relatedFolderId[$i]}";
				mysql_query($upd);
			}

			// 更新子级相关文件
			for ($i = 0; $i < count($relatedFolderId); $i++) {
				$sel = "SELECT `id`, `path` FROM `".PREFIX.FILE."` WHERE `fid` = {$relatedFolderId[$i]}";
				$sql = mysql_query($sel);
				$num = mysql_num_rows($sql);

				if ($num != 0) {
					while ($row = mysql_fetch_assoc($sql)) {
						$relatedFileId[] = $row['id'];
						$relatedFilePath[] = $newPath.substr($row['path'], strlen($path));
					}
				} else {
					continue;
				}
			}

			for ($i = 0; $i < count($relatedFileId); $i++) {
				$upd = "UPDATE `".PREFIX.FILE."` SET `path` = '{$relatedFilePath[$i]}' WHERE `id` = {$relatedFileId[$i]}";
				mysql_query($upd);
			}

			$result['status'] = 1;
			$result['update_time'] = date('Y-m-d H:i:s', $time);
		} else {
			$result['status'] = 0;
		}
	} else {
		$row = mysql_fetch_assoc($sql);
		if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 0) {
			$result['status'] = 2;
		} else if ($row['delete_time'] == 0 && $row['is_les'] != 0 && $row['is_pub'] == 0) {
			$result['status'] = 5;
		} else if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 1) {
			$result['status'] = 6;
		} else {
			$result['status'] = 4;
		}
	}
	echo json_encode($result);
	if (defined('BACKUP') && !empty(BACKUP) && $result['status'] == 1) {
		// $uid = $_SESSION['uid'];
		// $fid = $_POST['fid'];
		// $method = $_POST['method'];
		// $id = $_POST['id'];
		// $type = $_POST['type'];
		// $name = $_POST['name'];

		$url = BACKUP.'handle/backup.php';
		$curlData = array(
			'act' => $_POST['act'],
			'uid' => $uid,
			'fid' => $fid,
			'method' => $method,
			'type' => $type,
			'name' => $name
		);
		curlPost($url, $curlData);
	}
}



/**
 * 删除文件/文件夹，单个删除或批量删除，由data参数决定
 * 接收POST参数
 * 		func:空或del
 * 		data:一维数组/二维数组；一维数组：data[0]=file||folder; data[1]=id.  二维数组由若干个一维数组组成
 * @return 【字符串：状态码】
 * 		1:删除成功
 * 		0:删除失败
 */
function del(){
	$uid = $_SESSION['uid'];
	$method = $_POST['method'];
	$func = $_POST['func'];
	$data = $_POST['data'];
	$time = time();

	// 判断是否为批量删除
	if (count($data) == count($data, 1)) {
		// 单文件删除
		$table = $data[0] == 'folder' ? PREFIX.FOLDER : PREFIX.FILE;

		// 判断是否为彻底删除
		if ($method == 0 && $func != 'del') {
			// 软删除
			if ($data[0] == 'file') {
				$upd = "UPDATE `{$table}` SET `delete_time` = {$time} WHERE `id` = {$data[1]}";
				$res = mysql_query($upd);
			} else if ($data[0] == 'folder') {
				$sel = "SELECT `path` FROM `{$table}` WHERE `id` = {$data[1]}";
				$sql = mysql_query($sel);
				$path = mysql_fetch_array($sql)[0];

				$upd = "UPDATE `".PREFIX.FILE."` SET `delete_time` = {$time} WHERE `path` LIKE '{$path}/%'";
				mysql_query($upd);

				$upd = "UPDATE `{$table}` SET `delete_time` = {$time} WHERE `id` = {$data[1]} OR `path` LIKE '{$path}/%'";
				$res = mysql_query($upd);
			}
		} else if ($method == 1 || $func == 'del') {
			// var_dump($_POST);
			// 真正删除文件和数据库记录
			if ($data[0] == 'file') {
				$sel = "SELECT `fid`, `size`, `path` FROM `{$table}` WHERE `id` = {$data[1]}";
				$sql = mysql_query($sel);
				$row = mysql_fetch_assoc($sql);
				$fid = $row['fid'];
				$size = $row['size'];
				$path = $row['path'];

				$res = unlink(getCode($path));

				if ($res) {
					// 更新文件夹和已用空间的大小
					while ($fid > 0){
						$sel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
						$sql = mysql_query($sel);
						$row = mysql_fetch_array($sql)[0];

						$upd = "UPDATE `".PREFIX.FOLDER."` SET `size` = `size` - {$size} WHERE `id` = {$fid}";
						mysql_query($upd);
						$fid = $row;
					}

					$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` - {$size} WHERE `id` = {$uid}";
					mysql_query($upd);

					$del = "DELETE FROM `{$table}` WHERE `id` = {$data[1]}";
					mysql_query($del);
				}
			} else if ($data[0] == 'folder') {
				$sel = "SELECT `size`, `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$data[1]}";
				$sql = mysql_query($sel);
				$row = mysql_fetch_assoc($sql);
				$size = $row['size'];
				$path = $row['path'];

				// 删除文件夹中所有的文件
				$sel = "SELECT `id`, `path` FROM `".PREFIX.FILE."` WHERE `path` LIKE '{$path}/%'";
				$sql = mysql_query($sel);

				while ($row = mysql_fetch_assoc($sql)) {
					$res = unlink(getCode($row['path']));

					if ($res) {
						$del = "DELETE FROM `".PREFIX.FILE."` WHERE `id` = {$row['id']}";
						mysql_query($del);
					} else {
						break;
					}
				}

				// 删除文件夹中所有的文件夹(包括自身)
				$sel = "SELECT `id`, `path` FROM `{$table}` WHERE `id` = {$data[1]} OR `path` LIKE '{$path}/%' ORDER BY `id` DESC";
				$sql = mysql_query($sel);

				while ($row = mysql_fetch_assoc($sql)) {
					$res = rmdir(getCode($row['path']));
					
					if ($res) {
						$del = "DELETE FROM `{$table}` WHERE `id` = {$row['id']}";
						mysql_query($del);
					} else {
						break;
					}
				}

				$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` - {$size} WHERE `id` = {$uid}";
				mysql_query($upd);
			}
		}
	} else {
		// 批量删除
		for ($i = 0; $i < count($data); $i++) {
			$table = $data[$i][0] == 'folder' ? PREFIX.FOLDER : PREFIX.FILE;

			// 判断是否为彻底删除
			if ($method == 0 && $func != 'del') {
				// 软删除
				if ($data[$i][0] == 'file') {
					$upd = "UPDATE `{$table}` SET `delete_time` = {$time} WHERE `id` = {$data[$i][1]}";
					$res = mysql_query($upd);
				} else if ($data[$i][0] == 'folder') {
					$sel = "SELECT `path` FROM `{$table}` WHERE `id` = {$data[$i][1]}";
					$sql = mysql_query($sel);
					$path = mysql_fetch_array($sql)[0];

					$upd = "UPDATE `".PREFIX.FILE."` SET `delete_time` = {$time} WHERE `path` LIKE '{$path}/%'";
					mysql_query($upd);

					$upd = "UPDATE `{$table}` SET `delete_time` = {$time} WHERE `id` = {$data[$i][1]} OR `path` LIKE '{$path}/%'";
					$res = mysql_query($upd);
				}
			} else if ($method == 1 || $func == 'del') {
				// 真正删除文件
				if ($data[$i][0] == 'file') {
					$sel = "SELECT `fid`, `size`, `path` FROM `{$table}` WHERE `id` = {$data[$i][1]}";
					$sql = mysql_query($sel);
					$row = mysql_fetch_assoc($sql);
					$fid = $row['fid'];
					$size = $row['size'];
					$path = $row['path'];
					// var_dump($path);exit;
					$res = unlink(getCode($path));

					if ($res) {
						// 更新文件夹和已用空间的大小
						while ($fid > 0){
							$sel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
							$sql = mysql_query($sel);
							$row = mysql_fetch_array($sql)[0];

							$upd = "UPDATE `".PREFIX.FOLDER."` SET `size` = `size` - {$size} WHERE `id` = {$fid}";
							mysql_query($upd);
							$fid = $row;
						}

						$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` - {$size} WHERE `id` = {$uid}";
						mysql_query($upd);

						$del = "DELETE FROM `{$table}` WHERE `id` = {$data[$i][1]}";
						mysql_query($del);
					} else {
						break;
					}
				} else if ($data[$i][0] == 'folder') {
					$sel = "SELECT `size`, `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$data[$i][1]}";
					$sql = mysql_query($sel);
					$row = mysql_fetch_assoc($sql);
					$size = $row['size'];
					$path = $row['path'];

					// 删除文件夹中所有的文件
					$sel = "SELECT `id`, `path` FROM `".PREFIX.FILE."` WHERE `path` LIKE '{$path}/%'";
					$sql = mysql_query($sel);

					while ($row = mysql_fetch_assoc($sql)) {
						$res = unlink(getCode($row['path']));

						if ($res) {
							$del = "DELETE FROM `".PREFIX.FILE."` WHERE `id` = {$row['id']}";
							mysql_query($del);
						} else {
							break;
						}
					}

					// 删除文件夹中所有的文件夹(包括自身)
					$sel = "SELECT `id`, `path` FROM `{$table}` WHERE `id` = {$data[$i][1]} OR `path` LIKE '{$path}/%' ORDER BY `id` DESC";
					$sql = mysql_query($sel);

					while ($row = mysql_fetch_assoc($sql)) {
						$res = rmdir(getCode($row['path']));
						
						if ($res) {
							$del = "DELETE FROM `{$table}` WHERE `id` = {$row['id']}";
							mysql_query($del);
						} else {
							break;
						}
					}

					$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` - {$size} WHERE `id` = {$uid}";
					mysql_query($upd);
				}
			}
		}
	}

	$result['status'] = $res ? 1 : 0;
	echo json_encode($result);
}



/**
 * 还原文件/文件夹，单个还原或批量还原，由data参数决定
 *
 * @return 【字符串：状态码】
 * 		1:还原成功
 * 		0:还原失败
 */
function restore(){
	$data = $_POST['data'];

	if (count($data) == count($data, 1)) {
		$table = $data[0] == 'folder' ? PREFIX.FOLDER : PREFIX.FILE;

		if ($data[0] == 'file') {
			$upd = "UPDATE `{$table}` SET `delete_time` = 0 WHERE `id` = {$data[1]}";
			$sql = mysql_query($upd);
		} else if ($data[0] == 'folder') {
			$sel = "SELECT `path` FROM `{$table}` WHERE `id` = {$data[1]}";
			$sql = mysql_query($sel);
			$path = mysql_fetch_array($sql)[0];

			$upd = "UPDATE `".PREFIX.FILE."` SET `delete_time` = 0 WHERE `path` LIKE '{$path}/%'";
			mysql_query($upd);

			$upd = "UPDATE `{$table}` SET `delete_time` = 0 WHERE `id` = {$data[1]} OR `path` LIKE '{$path}/%'";
			$sql = mysql_query($upd);
		}
	} else {
		for ($i = 0; $i < count($data); $i++) {
			$table = $data[$i][0] == 'folder' ? PREFIX.FOLDER : PREFIX.FILE;

			if ($data[$i][0] == 'file') {
				$upd = "UPDATE `{$table}` SET `delete_time` = 0 WHERE `id` = {$data[$i][1]}";
				$sql = mysql_query($upd);
			} else if ($data[$i][0] == 'folder') {
				$sel = "SELECT `path` FROM `{$table}` WHERE `id` = {$data[$i][1]}";
				$sql = mysql_query($sel);
				$path = mysql_fetch_array($sql)[0];

				$upd = "UPDATE `".PREFIX.FILE."` SET `delete_time` = 0 WHERE `path` LIKE '{$path}/%'";
				mysql_query($upd);

				$upd = "UPDATE `{$table}` SET `delete_time` = 0 WHERE `id` = {$data[$i][1]} OR `path` LIKE '{$path}/%'";
				$sql = mysql_query($upd);
			}
		}
	}

	$result['status'] = $sql ? 1 : 0;
	echo json_encode($result);
}



/**
 * (取消)收藏文件/文件夹，单个(取消)收藏或批量(取消)收藏，由data参数决定
 * 接收SESSION参数
 * 		uid:用户id
 * 接收POST参数
 * 		method:collect||uncollect
 * 		data:一维数组/二维数组；一维数组：data[0]=file||folder; data[1]=id.  二维数组由若干个一维数组组成
 * @return 【字符串：状态码】
 * 		1:(取消)收藏成功
 * 		0:(取消)收藏失败
 * 		2:已收藏
 */
function collect(){
	$uid = $_SESSION['uid'];
	$method = $_POST['method'];
	$data = $_POST['data'];
	$time = time();

	if (count($data) == count($data, 1)) {
		$type = $data[0];
		$id = $data[1];

		// 判断文件是否已被收藏，并获取其id
		$sel = "SELECT `id` FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `type` = '{$type}' AND `fid` = {$id}";
		$sql = mysql_query($sel);

		if ($type == 'folder') {
			// 查询被收藏文件夹的路径
			$pathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$id}";
			$pathSql = mysql_query($pathSel);
			$path = mysql_fetch_array($pathSql)[0];

			// 找出子文件夹
			$folderSel = "SELECT `id` FROM `".PREFIX.FOLDER."` WHERE `id` = {$id} || `path` LIKE '{$path}/%'";
			$folderSql = mysql_query($folderSel);
			while ($folderRow = mysql_fetch_array($folderSql)) {
				$folderId[] = $folderRow['id'];
			}

			// 找出全部子文件
			$fileSel = "SELECT `id` FROM `".PREFIX.FILE."` WHERE `path` LIKE '{$path}/%'";
			$fileSql = mysql_query($fileSel);
			while ($fileRow = mysql_fetch_array($fileSql)) {
				$fileId[] = $fileRow['id'];
			}
		} else if ($type == 'file') {
			$fileId[] = $id;
		}

		$num = $method == 'collect' ? mysql_num_rows($sql) : 0;

		if ($num == 0) {
			if ($method == 'collect') {
				for ($i = 0; $i < count($folderId); $i++) {
					$ins = "INSERT INTO `".PREFIX.COLLECT."` (`uid`, `type`, `fid`, `create_time`, `update_time`) VALUES ({$uid}, 'folder', {$folderId[$i]}, {$time}, {$time})";
					mysql_query($ins);
				}

				for ($i = 0; $i < count($fileId); $i++) {
					$ins = "INSERT INTO `".PREFIX.COLLECT."` (`uid`, `type`, `fid`, `create_time`, `update_time`) VALUES ({$uid}, 'file', {$fileId[$i]}, {$time}, {$time})";
					mysql_query($ins);
				}
			} else {
				for ($i = 0; $i < count($folderId); $i++) {
					$del = "DELETE FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `fid` = {$folderId[$i]} AND `type` = 'folder'";
					mysql_query($del);
				}

				for ($i = 0; $i < count($fileId); $i++) {
					$del = "DELETE FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `fid` = {$fileId[$i]} AND `type` = 'file'";
					mysql_query($del);
				}
			}

			$result['status'] = $sql ? 1 : 0;
		} else {
			$result['status'] = 2;
		}
	} else {
		for ($i = 0; $i < count($data); $i++) {
			$type = $data[$i][0];
			$id = $data[$i][1];

			// 判断文件是否已被收藏，并获取其id
			$sel = "SELECT `id` FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `type` = '{$type}' AND `fid` = {$id}";
			$sql = mysql_query($sel);

			if ($type == 'folder') {
				// 查询将被取消收藏文件夹的路径
				$pathSel = "SELECT `path` FROM `".PREFIX.FOLDER."` WHERE `id` = {$id}";
				$pathSql = mysql_query($pathSel);
				$path = mysql_fetch_array($pathSql)[0];

				$folderSel = "SELECT `id` FROM `".PREFIX.FOLDER."` WHERE `id` = {$id} || `path` LIKE '{$path}/%'";
				$folderSql = mysql_query($folderSel);
				while ($folderRow = mysql_fetch_array($folderSql)) {
					$folderId[] = $folderRow['id'];
				}
			} else if ($type == 'file') {
				$fileId[] = $id;
			}

			$num = $method == 'collect' ? mysql_num_rows($sql) : 0;

			if ($num == 0) {
				if ($method == 'collect') {
					for ($i = 0; $i < count($folderId); $i++) {
						$ins = "INSERT INTO `".PREFIX.COLLECT."` (`uid`, `type`, `fid`, `create_time`, `update_time`) VALUES ({$uid}, 'folder', {$folderId[$i]}, {$time}, {$time})";
						mysql_query($ins);
					}

					for ($i = 0; $i < count($fileId); $i++) {
						$ins = "INSERT INTO `".PREFIX.COLLECT."` (`uid`, `type`, `fid`, `create_time`, `update_time`) VALUES ({$uid}, 'file', {$fileId[$i]}, {$time}, {$time})";
						mysql_query($ins);
					}
				} else {
					for ($i = 0; $i < count($folderId); $i++) {
						$del = "DELETE FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `fid` = {$folderId[$i]} AND `type` = 'folder'";
						mysql_query($del);
					}

					for ($i = 0; $i < count($fileId); $i++) {
						$del = "DELETE FROM `".PREFIX.COLLECT."` WHERE `uid` = {$uid} AND `fid` = {$fileId[$i]} AND `type` = 'file'";
						mysql_query($del);
					}
				}

				$result['status'] = $sql ? 1 : 0;
			} else {
				$result['status'] = 2;
			}
		}
	}
	echo json_encode($result);
}



/**
 * 检测文件是否存在
 * 接收SESSION参数
 * 		uid:学生id
 * 		lv:用户权限
 * 接收POST参数
 * 		fid:上级文件夹id
 * 		method:所创建的文件/文件夹所在环境->私有||公共||课业
 * 		name:文件名
 *
 * @return 【字符串：状态码】
 * 		0:文件不存在
 * 		1:班级目录下的第一级目录为课程目录，不允许有零散的文件与它们同级
 * 		2:文件已存在于私有目录中
 * 		3:文件已存在于课业目录中
 * 		4:文件已存在于公共目录中
 * 		5:文件已存在于回收站中（delete_time）
 */
function checkFile(){
	$uid = $_SESSION['uid'];
	$lv = $_SESSION['lv'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$name = $_POST['name'];

	if ($method == 'lesson' && $fid == 0 && $lv == 0) {
		$result['status'] = 1;
	} else {
		// 查询是否已存在该文件
		if ($method == 'user') {
			$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `".PREFIX.FILE."` WHERE `uid` = {$uid} AND `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
		} else if ($method == 'public') {
			$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `".PREFIX.FILE."` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 1";
		} else if ($method == 'lesson') {
			$sel = "SELECT `delete_time`, `is_les`, `is_pub` FROM `".PREFIX.FILE."` WHERE `fid` = {$fid} AND `name` = '{$name}' AND `is_pub` = 0";
		}
		$sql = mysql_query($sel);
		$num = mysql_num_rows($sql);

		// 文件已存在
		if ($num != 0) {
			$row = mysql_fetch_assoc($sql);
			if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 0) {
				$result['status'] = 2;
			} else if ($row['delete_time'] == 0 && $row['is_les'] == 1 && $row['is_pub'] == 0) {
				$result['status'] = 3;
			} else if ($row['delete_time'] == 0 && $row['is_les'] == 0 && $row['is_pub'] == 1) {
				$result['status'] = 4;
			} else {
				$result['status'] = 5;
			}
		} else {
			$result['status'] = 0;
		}
	}
	echo json_encode($result);
}



// md5检测已上传的分片
function checkBlock(){
	$md5 = $_POST['md5'];

	// 通过MD5唯一标识找到缓存文件
	$path = ROOT.'tmp/'.$md5;

	if (file_exists($path)) {
		// 有断点
		// 遍历成功的文件
		$block_info = scandir($path);

		// 除去无用文件
		foreach ($block_info as $key => $block) {
			if ($block == '.' || $block == '..') unset($block_info[$key]);
		}

		echo json_encode(['block_info' => $block_info]);
	} else {
		// 无断点
		echo json_encode([]);
	}
}



// 整理分片
function merge(){
	$sid = $_SESSION['sid'];
	$lv = $_SESSION['lv'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$md5 = $_POST['md5'];
	$name = $_POST['name'];

	// 找出分片文件
	$dir = ROOT.'tmp/'.$md5;

	// 获取分片文件内容
	$block_info = scandir($dir);

	// 除去无用文件
	foreach ($block_info as $key => $block) {
	    if ($block == '.' || $block == '..') unset($block_info[$key]);
	}

	// 数组按照正常规则排序
	natsort($block_info);

	// 定义保存文件的路径
	if ($fid == 0) {
		if ($method == 'user' || ($method == 'lesson' && $lv > 0)) {
			$path = ROOT.'data/home/'.$sid;
		} else if ($method == 'public') {
			$path = ROOT.'data/public';
		}
	} else {
		$sel = "SELECT `path` FROM ".PREFIX.FOLDER." WHERE `id` = {$fid}";
		$sql = mysql_query($sel);
		$path = mysql_fetch_array($sql)[0];
	}
	$save_file = getCode($path.'/'.$name);

	// 开始写入
	$out = @fopen($save_file, "wb");

	// 增加文件锁
	if (flock($out, LOCK_EX)) {
		foreach ($block_info as $b) {
			// 读取文件
			if (!$in = @fopen($dir.'/'.$b, "rb")) {
				break;
			}

			// 写入文件
			while ($buff = fread($in, 4096)) {
				fwrite($out, $buff);
			}

			@fclose($in);
			@unlink($dir.'/'.$b);
		}
		flock($out, LOCK_UN);
	}
	@fclose($out);
	@rmdir($dir);

	echo json_encode(["code" => "0"]);
}



// 上传文件
function upload(){
	set_time_limit(0);

	$sid = $_SESSION['sid'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$md5value = $_POST['md5value'];
	$chunk = $_POST['chunk'];
	$tmp = $_FILES['file']['tmp_name'];
	$size = $_FILES['file']['size'];
	$name = $_FILES['file']['name'];

	if ($size <= 5242880) {
		$_SESSION[$name] = file_get_contents($tmp);
	}

	// 临时存放文件分片的目录 - 以MD5为唯一标识
	$dir = ROOT.'tmp/'.$md5value;

	if (!file_exists($dir)) mkdir($dir, 0777, true);

	// 移入缓存文件保存
	move_uploaded_file($tmp, $dir.'/'.$chunk);
}



/**
 * 上传成功操作数据库
 * 接收SESSION参数
 * 		uid:用户id
 * 		sid:学号
 * 		lv:权限
 * 		content:暂存在SESSION中的数据
 * 接收POST参数
 * 		fid:文件夹id
 * 		method:所创建的文件/文件夹所在环境->私有||公共||课业
 * 		cid:是否为课业目录
 * 		name:文件名
 * 		size:文件大小
 * @return 【字符串：状态码】
 * 		1:上传成功且数据库记录成功
 * 		0:上传文件失败或数据库记录失败
 */
function uploadSuccess(){
	$uid = $_SESSION['uid'];
	$sid = $_SESSION['sid'];
	$lv = $_SESSION['lv'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$cid = (isset($_POST['cid']) && !empty($_POST['cid'])) ? $_POST['cid'] : 0;
	$name = $_POST['name'];
	$size = $_POST['size'];
	$time = time();
	$content = $_SESSION[$name];

	// 定义保存文件的路径
	if ($fid == 0) {
		if ($method == 'user' || ($method == 'lesson' && $lv > 0)) {
			$path = ROOT.'data/home/'.$sid;
		} else if ($method == 'public') {
			$path = ROOT.'data/public';
		}
	} else {
		$sel = "SELECT `path` FROM ".PREFIX.FOLDER." WHERE `id` = {$fid}";
		$sql = mysql_query($sel);
		$path = mysql_fetch_array($sql)[0];
	}
	$path = $path.'/'.$name;

	if ($method == 'user') {
		$ins = "INSERT INTO `".PREFIX.FILE."` (`uid`, `fid`, `name`, `path`, `size`, `create_time`, `update_time`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$size}, {$time}, {$time})";
	} else if ($method == 'public') {
		$ins = "INSERT INTO `".PREFIX.FILE."` (`uid`, `fid`, `name`, `path`, `size`, `create_time`, `update_time`, `is_pub`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$size}, {$time}, {$time}, 1)";
	} else if ($method == 'lesson') {
		$ins = "INSERT INTO `".PREFIX.FILE."` (`uid`, `fid`, `name`, `path`, `size`, `create_time`, `update_time`, `is_les`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$size}, {$time}, {$time}, {$cid})";
	}
	$sql = mysql_query($ins);

	// 更新目录大小
	while ($fid > 0){
		$sel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$sql = mysql_query($sel);
		$row = mysql_fetch_array($sql)[0];

		$upd = "UPDATE `".PREFIX.FOLDER."` SET `size` = `size` + {$size} WHERE `id` = {$fid}";
		mysql_query($upd);
		$fid = $row;
	}

	// 更新用户已使用空间大小
	$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` + {$size} WHERE `id` = {$uid}";
	mysql_query($upd);

	if ($sql) {
		if ($size <= 5242880) {
			$res = file_put_contents(getCode($path), $content);

			if ($res !== FALSE) {
				unset($_SESSION[$name]);
				$result['status'] = 1;
			} else {
				$result['status'] = 0;
			}
		} else {
			$result['status'] = 1;
		}
	} else {
		$result['status'] = 0;
	}

	echo json_encode($result);
}



// 编辑文件
function edit(){
	$id = $_POST['id'];

	$sel = "SELECT `path` FROM `".PREFIX.FILE."` WHERE `id` = {$id}";
	$sql = mysql_query($sel);
	$path = mysql_fetch_array($sql)[0];

	// 打开文件
	$path = getCode($path);
	echo file_get_contents($path);
}



// 保存文件
function save(){
	$uid = $_SESSION['uid'];
	$id = $_POST['id'];
	$value = $_POST['value'];
	$time = time();

	$sel = "SELECT `fid`, `size`, `path` FROM `".PREFIX.FILE."` WHERE `id` = {$id}";
	$sql = mysql_query($sel);
	$row = mysql_fetch_assoc($sql);
	$fid = $row['fid'];
	$size = $row['size'];
	$path = $row['path'];
	$path = getCode($path);

	// 追加内容
	$res = file_put_contents($path, $value);
	if ($res !== FALSE) {
		$newSize = filesize($path);
		$offsetSize = $newSize - $size;

		$upd = "UPDATE `".PREFIX.FILE."` SET `size` = {$newSize}, `update_time` = {$time} WHERE `id` = {$id}";
		mysql_query($upd);

		while ($fid > 0){
			$sel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
			$sql = mysql_query($sel);
			$row = mysql_fetch_array($sql)[0];

			$upd = "UPDATE `".PREFIX.FOLDER."` SET `size` = `size` + {$offsetSize} WHERE `id` = {$fid}";
			mysql_query($upd);
			$fid = $row;
		}

		$upd = "UPDATE `".PREFIX.USER."` SET `space` = `space` + {$offsetSize} WHERE `id` = {$uid}";
		mysql_query($upd);
		$result['status'] = 1;
	} else {
		$result['status'] = 0;
	}
	echo json_encode($result);
}