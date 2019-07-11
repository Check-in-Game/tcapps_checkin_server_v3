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
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (1, '粉色可莫尔', 2, '/cdn/v3/basic_resources/comber_lt.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (2, '蓝色可莫尔', 2, '/cdn/v3/basic_resources/comber_rt.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (3, '绿色可莫尔', 2, '/cdn/v3/basic_resources/comber_lb.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (4, '黄色可莫尔', 2, '/cdn/v3/basic_resources/comber_rb.svg', '基础资源的一种', 10, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (5, '可莫尔', 2, '/cdn/v3/basic_resources/comber.svg', '基础资源的一种', 50, 1);
#v2版本勋章
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (6, '内测勋章', 1, '/cdn/v2/badges/1.png', '传说中的勋章之一', 0, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (7, '公测勋章', 1, '/cdn/v2/badges/2.png', '传说中的勋章之一', 0, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (8, '佬勋章', 1, '/cdn/v2/badges/3.svg', '传说中的勋章之一', 0, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (9, '萌勋章', 1, '/cdn/v2/badges/4.svg', '传说中的勋章之一', 0, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (10, '一起加倍吧！（1.2倍纪念版）', 1, '/cdn/v2/badges/5.svg', '传说中的勋章之一，看起来似乎有一股很强的力量……', 1000, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (11, '一起加倍吧！（1.3倍纪念版）', 1, '/cdn/v2/badges/6.svg', '传说中的勋章之一，看起来似乎有一股很强的力量……', 1000, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (12, 'v2勋章', 1, '/cdn/v2/badges/7.svg', '传说中的勋章之一，看起来似乎有一股很强的力量……', 2000, 1);
INSERT INTO `tcapps_checkin_v3_items` (`iid`, `iname`, `tid`, `image`, `description`, `recycle_value`, `status`) VALUES (13, 'Worker兑换券', 1, '/cdn/v3/basic_resources/worker.svg', '一种基础资源的兑换券，兑换后可用于产出资源', 1000, 1);

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

#Worker注册表v3
#1为正常状态，2为寄售
create table tcapps_checkin_v3_user_workers(
  wid int unsigned primary key auto_increment not null comment "WorkerID",
  uid int unsigned not null comment "所有者UID",
  fid int unsigned not null comment "区域ID",
  level int unsigned not null comment "等级",
  update_time datetime not null comment "更新时间",
  status tinyint not null default 1 comment "状态"
)comment="Worker注册表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#Worker产区表v3
create table tcapps_checkin_v3_user_workers_field(
  fid int unsigned primary key auto_increment not null comment "产区ID",
  fname varchar(32) unique not null comment "产区名称",
  iid int unsigned not null comment "产出资源ID",
  speed float unsigned default 0.1 not null comment "产出速度/h",
  times float unsigned default 1 not null comment "产出倍率",
  limi_count int unsigned default 0 not null comment "限制数量",
  limi_level int unsigned default 1 not null comment "限制等级",
  status tinyint not null default 1 comment "状态"
)comment="Worker产区表",engine=InnoDB default character set utf8 collate utf8_general_ci;

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

#商城系统v3
create table tcapps_checkin_v3_shop(
  cid int unsigned primary key auto_increment not null comment "物品ID",
  iid int unsigned not null comment "物品ID",
  cost int unsigned not null comment "商品售价",
  starttime datetime not null default '1970-01-01 00:00:00' comment "销售开始时间",
  endtime datetime not null default '1970-01-01 00:00:00' comment "销售结束时间",
  sid tinyint unsigned not null default 1 comment "展示类型",
  all_count int unsigned not null default 0 comment "总销售数量",
  rebuy int unsigned not null default 1 comment "最多购买数量",
  onsale tinyint unsigned not null default 0 comment "促销状态",
  sale_starttime datetime not null default '1970-01-01 00:00:00' comment "促销开始时间",
  sale_endtime datetime not null default '1970-01-01 00:00:00' comment "促销结束时间",
  sale_cost int unsigned not null comment "促销价格",
  description text not null comment "商品描述",
  status tinyint not null default 1 comment "状态"
)comment="商城系统",engine=InnoDB default character set utf8 collate utf8_general_ci;
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('1', '20', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '0', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '20', 'Worker可产出的基础资源', '1');
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('2', '20', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '0', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '20', 'Worker可产出的基础资源', '1');
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('3', '20', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '0', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '20', 'Worker可产出的基础资源', '1');
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('4', '20', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '0', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '20', 'Worker可产出的基础资源', '1');
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('13', '5000', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '0', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '5000', '可用于产出可莫尔资源', '1');
INSERT INTO `tcapps_checkin_v3_shop` (`iid`, `cost`, `starttime`, `endtime`, `sid`, `all_count`, `rebuy`, `onsale`, `sale_starttime`, `sale_endtime`, `sale_cost`, `description`, `status`) VALUES ('13', '1', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1', '0', '1', '0', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '5000', '可用于产出可莫尔资源', '1');

#购买记录v3
create table tcapps_checkin_v3_purchase_records(
  pid int unsigned auto_increment primary key not null comment "购买ID",
  uid int unsigned not null comment "用户ID",
  cid int unsigned not null comment "商品ID",
  iid int unsigned not null comment "物品ID",
  item_count int unsigned not null comment "购买数量",
  cost int unsigned not null default 0 comment "花费",
  purchase_time datetime not null comment "购买时间",
  status tinyint not null default 1 comment "状态"
)comment="购买记录",engine=InnoDB default character set utf8 collate utf8_general_ci;

#回收记录v3
create table tcapps_checkin_v3_recycle_records(
  rid int unsigned auto_increment primary key not null comment "回收ID",
  uid int unsigned not null comment "用户ID",
  iid int unsigned not null comment "物品ID",
  item_count int unsigned not null comment "回收数量",
  value int unsigned not null default 0 comment "价值",
  recycle_time datetime not null comment "回收时间",
  status tinyint not null default 1 comment "状态"
)comment="回收记录",engine=InnoDB default character set utf8 collate utf8_general_ci;

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
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('cdn_prefix', 'https://checkin-static.twocola.com', 'CDN域名');

```
