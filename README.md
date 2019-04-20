# tcapps-checkin-server

## API相关

### 错误编码设计

### 错误码一览

- `2101`: 获取Token时提交了错误的用户名或密码
- `2102`: 获取Token时提交了错误的用户名或密码（数据库中不存在）
- `2103`: 获取Token时提交了错误的用户名或密码（密码不匹配）
- `2104`: ~~获取Token时用户在Token表中不存在~~
- `2105`: 获取Token时提交Token更新失败
- `2106`: 获取Token时用户状态为-1
- `2201`: 签到时错误的用户名或Token
- `2202`: 签到时用户名不存在
- `2203`: 签到时用户状态为-1
- `2204`: 签到时Token表中不存在指定用户uid
- `2205`: 签到时Token时间不足5分钟
- `2206`: 签到时Token与用户名不匹配
- `2207`: 签到时Token无法写入签到信息
- `2301`: 登录时提交了错误的用户名或密码
- `2302`: 登录时提交了错误的用户名或密码（数据库中不存在）
- `2303`: 登录时提交了错误的用户名或密码（密码不匹配）
- `2304`: 登录时提交的信息不完整
- `2305`: 登录时提交了错误的验证码
- `2306`: 登录时检测到用户状态为-1
- `2307`: 登录时，部分数据没有被提交
- `2401`: 访问需要登录的页面时未检查到登录信息
- `2402`: 访问需要登录的页面时授权信息与数据库不匹配
- `2501`: 购买时购买的商品ID不存在或不处于销售状态
- `2502`: 购买时用户状态为-1
- `2503`: 购买时用户余额不足
- `2504`: 购买时购买商品已经不在销售时间
- `2505`: 购买时购买商品已经售罄
- `2506`: 购买次数已经达到上限
- `2507`: 购买时写入数据库失败
- `2508`: 购买时有其他商品也处于购买状态
- `2601`: 管理员状态无效
- `2602`: 管理员增加补偿时写入数据表失败
- `2603`: 管理员增加活动时日期参数不合法
- `2604`: 管理员增加活动时最小价值数不合法
- `2605`: 管理员增加活动时最大价值数小于最小价值数
- `2606`: 管理员增加活动时写入数据库失败
- `2617`: 管理员搜索活动时无法找到对应的aid
- `2618`: 管理员增加活动时， 部分数据没有被提交
- `2619`: 管理员修改活动时， 部分数据没有被提交
- `2620`: 管理员修改活动时， 日期参数不合法
- `2621`: 管理员修改活动时最小价值数不合法
- `2622`: 管理员修改活动时最大价值数小于最小价值数
- `2623`: 管理员修改活动时，写入数据库失败
- `2624`: 管理员删除活动时，部分数据没有被提交
- `2625`: 管理员删除活动时，无法找到对应活动AID
- `2626`: 管理员删除活动时，无法删除数据库记录
- `2607`: 管理员增加商品时日期参数不合法
- `2608`: 管理员增加商品时存在小于0的值
- `2609`: 管理员增加商品时写入数据库失败
- `2610`: 管理员无操作权限
- `2701`: 管理员修改密码时未使用POST方式提交数据
- `2702`: 管理员修改密码时该用户处于非正常状态或不存在
- `2703`: 管理员修改密码时3个密码长度与要求不符（8-16）或两个新密码不一致
- `2704`: 管理员修改密码时原密码与数据库不符
- `2705`: 管理员修改密码时更新数据库信息失败
- `2801`: 管理员优化操作时找不到优化方法
- `2901`: 管理员搜索公告ID时，无法从数据库找到
- `2902`: 管理员新增公告时，部分数据没有被提交
- `2903`: 管理员新增公告时，插入数据库失败
- `2904`: 管理员修改公告时，部分数据没有被提交
- `2905`: 管理员修改公告时，提交的NID无法被找到
- `2906`: 管理员修改公告时，无法更新数据库
- `2907`: 管理员删除公告时，部分数据没有被提交
- `2908`: 管理员删除公告时，提交的NID无法被找到
- `2909`: 管理员删除公告时，无法删除数据库数据
- `3001`: 管理员搜索用户时，UID不存在
- `3002`: 管理员修改用户时，UID不存在
- `3003`: 管理员修改用户时，无法更新数据库
- `3004`: 管理员修改用户时，用户权限较高无法修改
- `3101`: 给管理员提权时，部分数据没有被提交
- `3102`: 给管理员提权时，无法查询对应权限信息
- `3103`: 给管理员提权时，提权者权限不足
- `3104`: 给管理员提权时，数据库写入失败

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
#类型4:氪金加值
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

#INSERT INTO `tcapps_checkin_shop` (`gid`, `gname`, `cost`, `starttime`, `endtime`, `tid`, `sid`, `all_count`, `image`, `rebuy`, `description`, `status`) VALUES (NULL, '内测勋章', '2000', '2019-04-08 12:00:00', '1970-01-01 00:00:00', '1', '2', '10', '', '1', '内测勋章没有预览图（因为没来急做），共发行10枚。勋章后期会随更新外观，目前就先凑合一下。', '1');

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
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('site_owner', 255, '站长权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('site_optmize', 255, '系统管理权限', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('upgrade_user_to_admin', 255, '允许此管理提升其他用户为管理', 1);
INSERT INTO `tcapps_checkin_admin_rights_list` (`rname`, `level_need`, `description`, `status`) VALUES ('upgrade_admin_to_activity_manage', 255, '允许赋予其他用户管理活动有关的所有内容的权限', 1);
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

#系统设置表
create table tcapps_checkin_system(
  skey varchar(255) primary key not null comment "设置键",
  svalue varchar(2048) not null comment "设置值",
  description varchar(2048) not null comment "解释"
)comment="系统设置表",engine=InnoDB default character set utf8 collate utf8_general_ci;
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('register_available', 'true', '是否开通注册通道');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('checkin_history_limit', 7, '签到历史记录极限查询时间');
INSERT INTO `tcapps_checkin_system` (`skey`, `svalue`, `description`) VALUES ('checkin_history_limit_unit', 'day', '签到历史记录极限查询时间单位,day/week/month');

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
INSERT INTO `tcapps_checkin_notices` (`place_id`, `title`, `content`, `color`, `starttime`, `endtime`, `priority`, `status`) VALUES ('1', '净化行动公告', '为保证平台正常运行，所有用户名涉及广告的用户将收到系统提示更改用户名。不进行更改的用户将被暂停签到，暂停期间造成的签到损失不予补偿。', 'warning', '2019-04-13 00:00:00', '1970-01-01 00:00:00', 1, 1);
```
