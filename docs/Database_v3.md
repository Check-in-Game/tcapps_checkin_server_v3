# tcapps-checkin-server v3

## 数据库设计
```
#擦灰记录表v3
create table tcapps_checkin_v3_clean_list(
  uid int unsigned primary key not null comment "用户ID",
  check_time datetime not null comment "擦灰时间"
)comment="擦灰记录表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#物品列表v3
create table tcapps_checkin_v3_items(
  iid int unsigned auto_increment primary key not null comment "物品ID",
  iname varchar(64) not null comment "物品名称",
  tid int unsigned not null default 1 comment "商品类型",
  image varchar(512) not null default '' comment "图片链接",
  description text not null comment "物品描述",
  recycle_value int unsigned not null comment "回收价格",
  status tinyint not null default 1 comment "状态"
)comment="物品列表",engine=InnoDB default character set utf8 collate utf8_general_ci;
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (1, '粉色可莫尔', 2, 'https://checkin-static.twocola.com/cdn/v3/basic_resources/comber_lt.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (2, '蓝色可莫尔', 2, 'https://checkin-static.twocola.com/cdn/v3/basic_resources/comber_rt.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (3, '绿色可莫尔', 2, 'https://checkin-static.twocola.com/cdn/v3/basic_resources/comber_lb.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (4, '黄色可莫尔', 2, 'https://checkin-static.twocola.com/cdn/v3/basic_resources/comber_rb.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (5, '可莫尔', 2, 'https://checkin-static.twocola.com/cdn/v3/basic_resources/comber.svg', '基础资源的一种', 50, 1);

#物品类型列表v3
create table tcapps_checkin_v3_items_types(
  tid int unsigned primary key not null comment "类型ID",
  sname varchar(64) unique not null comment "类型名称"
)comment="物品类型列表",engine=InnoDB default character set utf8 collate utf8_general_ci;
INSERT INTO `tcapps_checkin_v3_items_types` (`tid`, `sname`) VALUES (1, '勋章');
INSERT INTO `tcapps_checkin_v3_items_types` (`tid`, `sname`) VALUES (2, '基本资源');
INSERT INTO `tcapps_checkin_v3_items_types` (`tid`, `sname`) VALUES (3, '重要资源');
INSERT INTO `tcapps_checkin_v3_items_types` (`tid`, `sname`) VALUES (4, '特殊资源');
INSERT INTO `tcapps_checkin_v3_items_types` (`tid`, `sname`) VALUES (5, 'Worker');

#积分系统v3
create table tcapps_checkin_v3_user_point(
  uid int unsigned primary key not null comment "用户ID",
  point int not null comment "积分数量"
)comment="积分系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#背包系统v3
create table tcapps_checkin_v3_user_items(
  uid int unsigned primary key not null comment "用户ID",
  items json not null comment "物品"
)comment="背包系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#勋章系统v3
create table tcapps_checkin_v3_badges(
  bid int unsigned auto_increment primary key not null comment "勋章ID",
  bname varchar(255) unique not null comment "勋章名称",
  description varchar(255) not null comment "勋章描述",
  image varchar(512) not null comment "图片链接",
  bgcolor varchar(64) not null comment "背景颜色",
  fgcolor varchar(64) not null comment "前景颜色",
  status tinyint not null default 1 comment "状态"
)comment="勋章系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#管理员等级表
create table tcapps_checkin_admin_level(
  uid int unsigned primary key not null comment "用户ID",
  level tinyint unsigned not null comment "管理等级",
  update_time datetime not null comment "更新时间",
  status tinyint not null default 1 comment "状态"
)comment="管理员等级表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#管理员权限注册表
create table tcapps_checkin_admin_register(
  uid int unsigned not null comment "用户ID",
  rid int unsigned not null comment "权限ID",
  status tinyint not null default 1 comment "状态"
)comment="管理员权限表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#管理员权限表
create table tcapps_checkin_admin_rights_list(
  rid int unsigned auto_increment primary key not null comment "权限ID",
  rname varchar(256) not null comment "权限名称",
  level_need tinyint unsigned not null default 255 comment "提权最低等级",
  description text not null comment "解释",
  status tinyint not null default 1 comment "状态"
)comment="管理员权限表",engine=InnoDB default character set utf8 collate utf8_general_ci;
#1
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('site_owner', 255, '站长权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('site_optmize', 255, '系统管理权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('admin_level_update', 255, '允许此管理提升其他用户管理等级', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('admin_level_remove', 255, '允许此管理清空其他用户管理等级', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('activity_search', 255, '搜索有关活动内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('activity_add', 255, '增加有关活动内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('activity_update', 255, '修改有关活动内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('activity_delete', 255, '删除有关活动内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('notices_search', 255, '搜索有关公告内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('notices_add', 255, '增加有关公告内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('notices_update', 255, '修改有公告内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('notices_delete', 255, '删除有公告内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('user_search', 255, '管理用户时搜索权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('user_manage', 255, '管理用户时更新用户信息权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('conpensate_add', 255, '增加系统补偿积分', 1);
#2
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('goods_search', 255, '搜索有关商品内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('goods_add', 255, '增加有关商品内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('goods_update', 255, '增加有关商品内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('goods_delete', 255, '增加有关商品内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('badges_search', 255, '搜索有关勋章内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('badges_add', 255, '增加有关勋章内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('badges_update', 255, '修改有勋章内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('badges_delete', 255, '删除有勋章内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('effects_search', 255, '搜索有关效果内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('effects_add', 255, '增加有关效果内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('effects_update', 255, '修改有效果内容的权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('effects_delete', 255, '删除有效果内容的权限', 1);

#系统设置表
create table tcapps_checkin_system(
  skey varchar(255) primary key not null comment "设置键",
  svalue varchar(2048) not null comment "设置值",
  description varchar(2048) not null comment "解释"
)comment="系统设置表",engine=InnoDB default character set utf8 collate utf8_general_ci;
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('register_available', 'true', '是否开通注册通道');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('badges_wear_limit', 1, '用户佩戴勋章数量限制');

```
