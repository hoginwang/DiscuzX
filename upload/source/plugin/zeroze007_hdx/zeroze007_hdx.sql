DROP TABLE IF EXISTS pre_hdx_guard;
CREATE TABLE IF NOT EXISTS pre_hdx_guard (
  uid mediumint(8) unsigned NOT NULL,
  price int(11) NOT NULL,
  rate varchar(11) NOT NULL,
  protect_time int(10) unsigned NOT NULL,
  description varchar(255) NOT NULL,
  available tinyint(1) NOT NULL,
  employer_uid int(11) NOT NULL,
  expired_time int(11) NOT NULL,
  PRIMARY KEY (uid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_log;
CREATE TABLE IF NOT EXISTS pre_hdx_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  who_uid int(11) NOT NULL,
  to_uid int(11) NOT NULL,
  msg varchar(255) NOT NULL,
  created_at int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_msg;
CREATE TABLE IF NOT EXISTS pre_hdx_msg (
  id int(11) NOT NULL AUTO_INCREMENT,
  from_uid int(11) NOT NULL,
  to_uid int(11) NOT NULL,
  content text NOT NULL,
  created_at int(11) NOT NULL,
  `read` tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_player;
CREATE TABLE IF NOT EXISTS pre_hdx_player (
  uid int(11) NOT NULL,
  sw int(11) NOT NULL,
  sta int(11) NOT NULL,
  exp int(11) NOT NULL,
  `level` mediumint(8) unsigned NOT NULL,
  title varchar(20) NOT NULL,
  weapon_id smallint(6) NOT NULL,
  armor_id smallint(6) NOT NULL,
  join_time int(11) NOT NULL,
  rob_day_count int(11) NOT NULL,
  rob_times int(11) NOT NULL,
  rob_money_amount int(11) NOT NULL,
  rob_success_times int(11) NOT NULL,
  robbed_times int(11) NOT NULL,
  last_rob_time int(11) NOT NULL,
  last_robbed_time int(11) NOT NULL,
  in_jail_time int(11) NOT NULL,
  out_jail_time int(11) NOT NULL,
  jail_times int(11) NOT NULL,
  last_escape_time int(11) NOT NULL,
  last_active_time int(11) NOT NULL,
  available tinyint(1) NOT NULL,
  PRIMARY KEY (uid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_player_activity;
CREATE TABLE IF NOT EXISTS pre_hdx_player_activity (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  created_at int(11) NOT NULL,
  expired_time int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_player_item;
CREATE TABLE IF NOT EXISTS pre_hdx_player_item (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL,
  item_id smallint(6) NOT NULL,
  durability int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_player_setting;
CREATE TABLE IF NOT EXISTS pre_hdx_player_setting (
  uid int(11) NOT NULL,
  skey varchar(50) NOT NULL,
  svalue text NOT NULL,
  PRIMARY KEY (uid,skey)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_setting;
CREATE TABLE IF NOT EXISTS pre_hdx_setting (
  skey varchar(50) NOT NULL,
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_shop_item;
CREATE TABLE IF NOT EXISTS pre_hdx_shop_item (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` enum('weapon','armor','food') NOT NULL,
  price int(11) NOT NULL,
  rate varchar(10) NOT NULL,
  description varchar(255) NOT NULL,
  img_file varchar(255) NOT NULL,
  disp_order int(11) NOT NULL,
  available tinyint(1) NOT NULL,
  durability int(11) NOT NULL,
  d_loss_rate varchar(10) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_title;
CREATE TABLE IF NOT EXISTS pre_hdx_title (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  high int(10) NOT NULL DEFAULT '0',
  low int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY creditsrange (high,low)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_victim;
CREATE TABLE IF NOT EXISTS pre_hdx_victim (
  uid int(11) NOT NULL,
  robbed_day_count int(11) NOT NULL,
  robbed_times int(11) NOT NULL,
  robbed_money_amount int(11) NOT NULL,
  last_robbed_time int(11) NOT NULL,
  PRIMARY KEY (uid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_hdx_yule;
CREATE TABLE IF NOT EXISTS pre_hdx_yule (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  price int(11) NOT NULL,
  add_sta int(11) NOT NULL,
  description varchar(255) NOT NULL,
  img_file varchar(255) NOT NULL,
  disp_order int(11) NOT NULL,
  available tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;