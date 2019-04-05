# tcapps-checkin-server

## API相关

### 错误编码设计

#### 第一位

- `1`：web
- `2`：api

#### 第二位

- `0`: 账户
- `1`: Token
- `2`: Check-in

### 错误码一览

- `2101`: 获取Token时提交了错误的用户名或密码
- `2102`: 获取Token时提交了错误的用户名或密码（数据库中不存在）
- `2103`: 获取Token时提交了错误的用户名或密码（密码不匹配）
- `2104`: 获取Token时用户在Token表中不存在
- `2105`: 获取Token时提交Token更新失败
- `2106`: 获取Token时用户状态为-1
- `2201`: 签到时错误的用户名或Token
- `2202`: 签到时用户名不存在
- `2203`: 签到时用户状态为-1
- `2204`: 签到时Token表中不存在指定用户uid
- `2205`: 签到时Token时间不足5分钟
- `2206`: 签到时Token与用户名不匹配
- `2207`: 签到时Token无法写入签到信息

## 数据库设计
```
#用户信息
create table tcapps_checkin_user_accounts(
  uid int unsigned auto_increment primary key not null comment "用户ID",
  username varchar(16) unique not null comment "用户名",
  password varchar(32) not null comment "密码",
  status tinyint not null default 1 comment "状态"
)comment="用户信息",engine=MyISAM default character set utf8 collate utf8_general_ci;
#insert into tcapps_checkin_user_accounts set username='jokin',password='74e133f544b908be8e30e9cb64e8a536';

#签到Token列表v2
create table tcapps_checkin_tokens_v2(
  uid int unsigned primary key not null comment "用户ID",
  token varchar(49) default "" comment "Token",
  status tinyint not null default -1 comment "状态"
)comment="签到Token列表",engine=MyISAM default character set utf8 collate utf8_general_ci;

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
  worth tinyint unsigned not null default 1 comment "价值",
  cost tinyint unsigned not null default 0 comment "消耗",
  check_time datetime not null comment "签到时间",
  status tinyint not null default 1 comment "状态"
)comment="签到信息",engine=MyISAM default character set utf8 collate utf8_general_ci;

#活动设计
create table tcapps_checkin_activity(
  aid int unsigned auto_increment primary key not null comment "活动ID",
  starttime datetime not null comment "活动开始时间",
  endtime datetime not null comment "活动开始时间",
  min_worth int unsigned not null default 1 comment "最小价值",
  max_worth int unsigned not null default 1 comment "最大价值",
  status tinyint not null default 1 comment "状态"
)comment="活动设计表",engine=MyISAM default character set utf8 collate utf8_general_ci;
```

## 抛弃设计
```
#签到信息
create table tcapps_checkin_lists(
  cid int unsigned auto_increment primary key not null comment "签到ID",
  username varchar(16) not null comment "用户名",
  check_time datetime not null comment "签到时间"
)comment="签到信息",engine=MyISAM default character set utf8 collate utf8_general_ci;

#签到Token列表
create table tcapps_checkin_tokens(
  username varchar(16) primary key not null comment "用户名",
  token varchar(49) default "" comment "Token",
  status tinyint not null default -1 comment "状态"
)comment="签到Token列表",engine=MyISAM default character set utf8 collate utf8_general_ci;
#insert into tcapps_checkin_tokens set username='jokin';
```
