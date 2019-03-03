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

// 新建文件/文件夹
function create(){
	$uid = $_POST['uid'];
	$sid = $_POST['sid'];
	$lv = $_POST['lv'];
	$cid = $_POST['cid'];
	$fid = $_POST['fid'];
	$method = $_POST['method'];
	$type = $_POST['type'];
	$ext = $_POST['ext'];
	$path = $_POST['path'];
	$name = $_POST['name'];
	$time = time();

	if ($type == 'file') {
		$table = PREFIX.FILE;
	} else if ($type == 'folder') {
		$table = PREFIX.FOLDER;
	}

	if ($fid == 0) {
		if ($method == 'user' || ($method == 'lesson' && $lv > 0)) {
			// 课业目录权限高
			$path = ROOT.'data/home/'.$sid;
		} else if ($method == 'public') {
			$path = ROOT.'data/public';
		}
	}
	$path = $path.'/'.$name;

	if ($type == 'file') {
		$res = touch(getCode($path));
	} else if ($type == 'folder') {
		$res = mkdir(getCode($path), 0777);
	}

	if ($res) {
		if ($method == 'user') {
			$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`, `is_backup`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time}, 1)";
		} else if ($method == 'public') {
			$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`, `is_pub`, `is_backup`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time}, 1, 1)";
		} else if ($method == 'lesson') {
			$ins = "INSERT INTO `{$table}` (`uid`, `fid`, `name`, `path`, `create_time`, `update_time`, `is_les`, `is_backup`) VALUES ({$uid}, {$fid}, '{$name}', '{$path}', {$time}, {$time}, {$cid}, 1)";
		}
		mysql_query($ins);
		$result['status'] = 1;
	}
	echo $result['status'];
}

// 重命名文件/文件夹
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
}

// 删除文件/文件夹
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

// 还原文件/文件夹
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

// (取消)收藏文件/文件夹
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

			$folderSel = "SELECT `id` FROM `".PREFIX.FOLDER."` WHERE `id` = {$id} || `path` LIKE '{$path}/%'";
			$folderSql = mysql_query($folderSel);
			while ($folderRow = mysql_fetch_array($folderSql)) {
				$folderId[] = $folderRow['id'];
			}

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

// 检测文件是否存在
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

// 上传成功操作数据库
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

	while ($fid > 0){
		$sel = "SELECT `fid` FROM `".PREFIX.FOLDER."` WHERE `id` = {$fid}";
		$sql = mysql_query($sel);
		$row = mysql_fetch_array($sql)[0];

		$upd = "UPDATE `".PREFIX.FOLDER."` SET `size` = `size` + {$size} WHERE `id` = {$fid}";
		mysql_query($upd);
		$fid = $row;
	}

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