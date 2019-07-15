# tcapps-checkin-server

## 数据库设计
```
#用户信息
create table tcapps_checkin_user_accounts(
  uid int unsigned auto_increment primary key not null comment "用户ID",
  username varchar(16) unique not null comment "用户名",
  password varchar(32) not null comment "密码",
  status tinyint not null default 1 comment "状态"
)comment="用户信息",engine=InnoDB default character set utf8 collate utf8_general_ci;
#insert into tcapps_checkin_user_accounts set username='jokin',password='74e133f544b908be8e30e9cb64e8a536';

#签到Token列表v2
create table tcapps_checkin_tokens_v2(
  uid int unsigned primary key not null comment "用户ID",
  token varchar(49) default "" comment "Token",
  status tinyint not null default -1 comment "状态"
)comment="签到Token列表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#签到信息v2
#类型0:普通加值
#类型1:活动加值
#类型2:系统加值
#类型3:补偿加值
#类型4:签到补偿
#类型5:积分结算
#类型6:赞助加值
#类型7:擦灰加值
create table tcapps_checkin_lists_v2(
  cid int unsigned auto_increment primary key not null comment "签到ID",
  uid int unsigned not null comment "用户ID",
  tid tinyint unsigned not null default 0 comment "签到类型",
  worth int unsigned not null default 1 comment "价值",
  check_time datetime not null comment "签到时间",
  status tinyint not null default 1 comment "状态"
)comment="签到信息",engine=InnoDB default character set utf8 collate utf8_general_ci;

#活动设计
create table tcapps_checkin_activity(
  aid int unsigned auto_increment primary key not null comment "活动ID",
  starttime datetime not null comment "活动开始时间",
  endtime datetime not null comment "活动结束时间",
  min_worth int unsigned not null default 1 comment "最小价值",
  max_worth int unsigned not null default 1 comment "最大价值",
  status tinyint not null default 1 comment "状态"
)comment="活动设计表",engine=InnoDB default character set utf8 collate utf8_general_ci;

#商店设计
#商品类型
#1：勋章
#展示类型
#1：带图片；2：无图片
create table tcapps_checkin_shop(
  gid int unsigned auto_increment primary key not null comment "商品ID",
  gname varchar(64) not null comment "商品名称",
  cost int unsigned not null comment "商品售价",
  starttime datetime not null comment "销售开始时间",
  endtime datetime not null comment "销售结束时间",
  tid int unsigned not null default 1 comment "商品类型",
  sid tinyint unsigned not null default 1 comment "展示类型",
  all_count int unsigned not null default 0 comment "总销售数量",
  image varchar(512) not null default '' comment "图片链接",
  rebuy int unsigned not null default 1 comment "最多购买数量",
  description text not null comment "商品描述",
  status tinyint not null default 1 comment "状态"
)comment="商店",engine=InnoDB default character set utf8 collate utf8_general_ci;

#购买记录
create table tcapps_checkin_purchase_records(
  pid int unsigned auto_increment primary key not null comment "购买ID",
  uid int unsigned not null comment "用户ID",
  gid int unsigned not null comment "商品ID",
  cost int unsigned not null default 0 comment "花费",
  purchase_time datetime not null comment "购买时间",
  status tinyint not null default 1 comment "状态"
)comment="购买记录",engine=InnoDB default character set utf8 collate utf8_general_ci;

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
  rname varchar(256) not null comment "权限ID",
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
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('checkin_history_limit', 7, '签到历史记录极限查询时间');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('checkin_history_limit_unit', 'day', '签到历史记录极限查询时间单位,day/week/month');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('badges_wear_limit', 1, '用户佩戴勋章数量限制');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('newhand_support_pre_200', 3, '新用户前200次签到加成倍数');

#公告设置表
create table tcapps_checkin_notices(
  nid int unsigned auto_increment primary key not null comment "公告ID",
  place_id int unsigned not null comment "位置ID",
  title varchar(255) not null comment "标题",
  content text not null comment "内容",
  color varchar(64) not null comment "颜色class",
  starttime datetime not null comment "开始时间",
  endtime datetime not null comment "结束时间",
  priority tinyint not null default 1 comment "权重顺序",
  status tinyint not null default 1 comment "状态"
)comment="公告设置表",engine=InnoDB default character set utf8 collate utf8_general_ci;
#INSERT INTO `tcapps_checkin_notices` (`place_id`, `title`, `content`, `color`, `starttime`, `endtime`, `priority`, `status`) VALUES ('1', '净化行动公告', '为保证平台正常运行，所有用户名涉及广告的用户将收到系统提示更改用户名。不进行更改的用户将被暂停签到，暂停期间造成的签到损失不予补偿。', 'warning', '2019-04-13 00:00:00', '1970-01-01 00:00:00', 1, 1);

#勋章系统
create table tcapps_checkin_badges(
  bid int unsigned auto_increment primary key not null comment "勋章ID",
  bname varchar(255) unique not null comment "勋章名称",
  image varchar(512) not null comment "图片链接",
  bgcolor varchar(64) not null comment "背景颜色",
  fgcolor varchar(64) not null comment "前景颜色",
  gid int unsigned not null comment "商品ID",
  eid int unsigned not null default 0 comment "效果ID",
  status tinyint not null default 1 comment "状态"
)comment="勋章系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#效果系统
create table tcapps_checkin_effects(
  eid int unsigned auto_increment primary key not null comment "效果ID",
  times float unsigned not null comment "倍率",
  description text not null comment "效果描述",
  status tinyint not null default 1 comment "状态"
)comment="效果系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#勋章佩戴系统
create table tcapps_checkin_badges_wear(
  uid int unsigned primary key not null comment "用户ID",
  bid varchar(512) not null comment "勋章ID",
  update_time datetime not null comment "修改时间"
)comment="勋章佩戴系统",engine=InnoDB default character set utf8 collate utf8_general_ci;

#结算失败记录
create table tcapps_checkin_lists_v2_settle_failure(
  cid int unsigned auto_increment primary key not null comment "签到ID",
  uid int unsigned not null comment "用户ID",
  tid tinyint unsigned not null default 0 comment "签到类型",
  worth int unsigned not null default 1 comment "价值",
  check_time datetime not null comment "签到时间",
  status tinyint not null default 1 comment "状态"
)comment="结算失败记录",engine=InnoDB default character set utf8 collate utf8_general_ci;
```
