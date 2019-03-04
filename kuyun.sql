/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : kuyun

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2019-03-04 18:43:12
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ky_class
-- ----------------------------
DROP TABLE IF EXISTS `ky_class`;
CREATE TABLE `ky_class` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `classname` varchar(60) NOT NULL COMMENT '班级名称',
  `grade` int(4) unsigned NOT NULL COMMENT '年级',
  `uid` int(11) unsigned NOT NULL COMMENT '申请人编号',
  `num` int(3) unsigned NOT NULL COMMENT '学生人数',
  `instructor` varchar(20) NOT NULL COMMENT '辅导员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='班级表';

-- ----------------------------
-- Records of ky_class
-- ----------------------------

-- ----------------------------
-- Table structure for ky_collect
-- ----------------------------
DROP TABLE IF EXISTS `ky_collect`;
CREATE TABLE `ky_collect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) unsigned NOT NULL COMMENT '用户主键',
  `type` varchar(10) NOT NULL COMMENT '文件类型',
  `fid` int(11) unsigned NOT NULL COMMENT '文件主键',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='用户收藏表';

-- ----------------------------
-- Records of ky_collect
-- ----------------------------

-- ----------------------------
-- Table structure for ky_file
-- ----------------------------
DROP TABLE IF EXISTS `ky_file`;
CREATE TABLE `ky_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件编号',
  `uid` int(11) unsigned NOT NULL COMMENT '用户编号',
  `fid` int(11) DEFAULT '0' COMMENT '文件所在的文件夹编号',
  `name` varchar(255) NOT NULL COMMENT '文件名称',
  `path` varchar(255) NOT NULL COMMENT '文件的物理路径',
  `size` bigint(20) unsigned DEFAULT '0' COMMENT '文件大小',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `delete_time` int(10) unsigned NOT NULL COMMENT '删除时间',
  `is_pub` int(1) unsigned DEFAULT '0' COMMENT '是否为公共目录内容',
  `is_les` int(11) unsigned DEFAULT '0' COMMENT '是否为课业',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ky_file
-- ----------------------------
INSERT INTO `ky_file` VALUES ('68', '36', '26', 'aaa-测试文件-7.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-7.txt', '0', '1551659986', '1551659986', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('62', '36', '26', 'aaa-测试文件-1.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-1.txt', '0', '1551659948', '1551659948', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('63', '36', '26', 'aaa-测试文件-2.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-2.txt', '0', '1551659968', '1551659968', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('64', '36', '26', 'aaa-测试文件-3.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-3.txt', '0', '1551659973', '1551659973', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('65', '36', '26', 'aaa-测试文件-4.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-4.txt', '0', '1551659976', '1551659976', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('66', '36', '26', 'aaa-测试文件-5.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-5.txt', '0', '1551659980', '1551659980', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('67', '36', '26', 'aaa-测试文件-6.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1/aaa-测试文件-6.txt', '0', '1551659983', '1551659983', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('69', '36', '27', 'bbb-测试文件-1.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-1.txt', '0', '1551660024', '1551660024', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('70', '36', '27', 'bbb-测试文件-2.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-2.txt', '0', '1551660038', '1551660038', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('71', '36', '27', 'bbb-测试文件-3.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-3.txt', '0', '1551660043', '1551660043', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('72', '36', '27', 'bbb-测试文件-4.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-4.txt', '0', '1551660045', '1551660045', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('73', '36', '27', 'bbb-测试文件-5.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-5.txt', '0', '1551660048', '1551660048', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('74', '36', '27', 'bbb-测试文件-6.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2/bbb-测试文件-6.txt', '0', '1551660050', '1551660050', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('75', '36', '28', 'ccc-测试文件-1.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-1.txt', '0', '1551660061', '1551660061', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('76', '36', '28', 'ccc-测试文件-2.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-2.txt', '0', '1551660064', '1551660064', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('77', '36', '28', 'ccc-测试文件-3.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-3.txt', '0', '1551660066', '1551660066', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('78', '36', '28', 'ccc-测试文件-4.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-4.txt', '0', '1551660069', '1551660069', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('79', '36', '28', 'ccc-测试文件-5.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-5.txt', '0', '1551660071', '1551660071', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('80', '36', '28', 'ccc-测试文件-6.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-6.txt', '0', '1551660074', '1551660074', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('81', '36', '28', 'ccc-测试文件-7.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3/ccc-测试文件-7.txt', '0', '1551660076', '1551660076', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('82', '36', '0', '测试文件-1.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件-1.txt', '0', '1551660089', '1551660089', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('83', '36', '0', '测试文件-2.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件-2.txt', '0', '1551660092', '1551660092', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('84', '36', '0', '测试文件-3.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件-3.txt', '0', '1551660095', '1551660095', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('85', '36', '0', '测试文件-4.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件-4.txt', '0', '1551660100', '1551660100', '0', '0', '0');
INSERT INTO `ky_file` VALUES ('86', '36', '0', '蜜蜂老牛黄瓜-测试文件-1.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/public/蜜蜂老牛黄瓜-测试文件-1.txt', '0', '1551660221', '1551660221', '0', '1', '0');
INSERT INTO `ky_file` VALUES ('87', '36', '0', '蜜蜂老牛黄瓜-测试文件-2.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/public/蜜蜂老牛黄瓜-测试文件-2.txt', '0', '1551660225', '1551660225', '0', '1', '0');
INSERT INTO `ky_file` VALUES ('88', '36', '0', '蜜蜂老牛黄瓜-测试文件-3.txt', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/public/蜜蜂老牛黄瓜-测试文件-3.txt', '0', '1551660228', '1551660228', '0', '1', '0');

-- ----------------------------
-- Table structure for ky_folder
-- ----------------------------
DROP TABLE IF EXISTS `ky_folder`;
CREATE TABLE `ky_folder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件夹编号',
  `uid` int(11) unsigned NOT NULL COMMENT '用户编号',
  `fid` int(11) unsigned NOT NULL COMMENT '上级文件夹编号',
  `name` varchar(255) NOT NULL COMMENT '文件夹名称',
  `path` varchar(255) NOT NULL COMMENT '文件夹的物理路径',
  `size` bigint(20) unsigned DEFAULT '0' COMMENT '文件夹大小',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `delete_time` int(10) unsigned NOT NULL COMMENT '删除时间',
  `is_pub` int(1) unsigned DEFAULT '0' COMMENT '是否为公共目录内容',
  `is_les` int(11) unsigned DEFAULT '0' COMMENT '是否为课业，存放用户所在班级的id号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ky_folder
-- ----------------------------
INSERT INTO `ky_folder` VALUES ('25', '16', '0', '', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715115', '0', '1551619432', '1551619432', '0', '0', '0');
INSERT INTO `ky_folder` VALUES ('26', '36', '0', '测试文件夹-1', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1', '0', '1551659717', '1551659717', '0', '0', '0');
INSERT INTO `ky_folder` VALUES ('27', '36', '0', '测试文件夹-2', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-2', '0', '1551659758', '1551659758', '0', '0', '0');
INSERT INTO `ky_folder` VALUES ('28', '36', '0', '测试文件夹-3', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-3', '0', '1551659762', '1551659762', '0', '0', '0');
INSERT INTO `ky_folder` VALUES ('29', '36', '0', '测试文件夹-4', 'F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-4', '0', '1551659766', '1551659766', '0', '0', '0');

-- ----------------------------
-- Table structure for ky_myclass
-- ----------------------------
DROP TABLE IF EXISTS `ky_myclass`;
CREATE TABLE `ky_myclass` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cid` int(11) unsigned NOT NULL COMMENT '班级编号',
  `uid` int(11) unsigned NOT NULL COMMENT '用户编号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='班级成员表';

-- ----------------------------
-- Records of ky_myclass
-- ----------------------------

-- ----------------------------
-- Table structure for ky_user
-- ----------------------------
DROP TABLE IF EXISTS `ky_user`;
CREATE TABLE `ky_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
  `sid` char(11) NOT NULL COMMENT '学号',
  `username` varchar(15) NOT NULL COMMENT '姓名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '用户状态',
  `lv` tinyint(1) unsigned DEFAULT '0' COMMENT '用户权限',
  `space` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户已用空间',
  `max_space` bigint(20) unsigned NOT NULL DEFAULT '209715200' COMMENT '最大可用空间',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ky_user
-- ----------------------------
INSERT INTO `ky_user` VALUES ('26', '20177715115', '蜜蜂老牛黄瓜--0', '$2y$10$paSQNZL3oP1nZiIWCoT.feP3d7V2v6yMQM5Vt4eP235Fe3wnU6LxS', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('27', '20177715118', '蜜蜂老牛黄瓜--3', '$2y$10$jArMRSaPiYEGFRZcF8scqeXToiD73IaRPTDF9/fWgtfaROBGTs/Ze', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('28', '20177715120', '蜜蜂老牛黄瓜--5', '$2y$10$KJs1op1mqZQ4dlNe7KfvlueWdYvjIzQwHnmueRvDyPoHdirmijnXu', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('29', '20177715119', '蜜蜂老牛黄瓜--4', '$2y$10$53Ku.QyGuePHEUpgjOP5Fu9Dz8/PA44Px.rohB6ZkdNj50Df5NTvK', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('30', '20177715117', '蜜蜂老牛黄瓜--2', '$2y$10$B74UFY2j2m10mQP/VTioreWEvXZHSefNmkSTo.XgEybtsStfUQiTy', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('31', '20177715116', '蜜蜂老牛黄瓜--1', '$2y$10$FfJcgFfCfILTruREyRuJAeEKRdKJ8iQ45nk7tg29eB2iYsdLdyRB2', '1', '0', '0', '209715200', '1551619543', '1551619543');
INSERT INTO `ky_user` VALUES ('32', '20177715121', '蜜蜂老牛黄瓜--6', '$2y$10$CZKNnGxk46Tju4L1D0jwE.7q.0SPNypzNjzKgVBUzWBEeL1OwCEhC', '1', '0', '0', '209715200', '1551619544', '1551619544');
INSERT INTO `ky_user` VALUES ('33', '20177715122', '蜜蜂老牛黄瓜--7', '$2y$10$JWx/2mx3A65lTYkvbl/Yj.H2PVAoHGWFF21g20W34XmD0G4CBLFtq', '1', '0', '0', '209715200', '1551619544', '1551619544');
INSERT INTO `ky_user` VALUES ('34', '20177715123', '蜜蜂老牛黄瓜--8', '$2y$10$orEVikmd7TeSBDsoHv3xNO2x23YCte24Z2BDSC0kDFHmRvCjmy3su', '1', '0', '0', '209715200', '1551619544', '1551619544');
INSERT INTO `ky_user` VALUES ('35', '20177715124', '蜜蜂老牛黄瓜--9', '$2y$10$sReYRbH89NzI3URsxVZcq.TRcvbQB0LCwaXlbiVR2zHGd1D1YYQpC', '1', '0', '0', '209715200', '1551619544', '1551619544');
INSERT INTO `ky_user` VALUES ('36', '20177715114', '蜜蜂老牛黄瓜', '$2y$10$4R.fNuhJDM.WRnmWYQdCCO65H7KS37sC8hEW/FDAqJLl.d18egZHO', '1', '0', '0', '209715200', '1551659327', '1551659327');
