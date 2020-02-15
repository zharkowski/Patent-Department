create database patent_department_db;

create table if not exists patent_department_db.groups
(
  id   int auto_increment,
  name varchar(15) not null,
  constraint groups_id_group_uindex
  unique (id),
  constraint groups_name_uindex
  unique (name)
);

alter table patent_department_db.groups
  add primary key (id);

create table if not exists patent_department_db.group_rights
(
  id       int auto_increment,
  group_id int         not null,
  type     varchar(16) not null,
  name     varchar(32) not null,
  constraint group_rights_id_uindex
  unique (id),
  constraint group_rights_groups_id_fk
  foreign key (group_id) references patent_department_db.groups (id)
);

alter table patent_department_db.group_rights
  add primary key (id);

create table if not exists patent_department_db.users
(
  id            int auto_increment,
  password_hash varchar(255) not null,
  login         varchar(25)  not null,
  name          varchar(255) not null,
  email         varchar(255) not null,
  group_id      int          not null,
  constraint users_id_uindex
  unique (id),
  constraint users_login_uindex
  unique (login),
  constraint users_groups_id_fk
  foreign key (group_id) references patent_department_db.groups (id)
);

alter table patent_department_db.users
  add primary key (id);

create table if not exists patent_department_db.patents
(
  id        int auto_increment,
  title     varchar(128) default '' not null,
  user_id   int                     not null,
  init_date datetime                null,
  constraint requests_id_uindex
  unique (id),
  constraint patents_users_id_fk
  foreign key (user_id) references patent_department_db.users (id)
);

create table if not exists patent_department_db.patent_description_files
(
  id        int auto_increment,
  patent_id int          not null,
  file_name varchar(255) null,
  constraint patent_description_files_id_uindex
  unique (id),
  constraint patent_description_files_patents_id_fk
  foreign key (patent_id) references patent_department_db.patents (id)
);

alter table patent_department_db.patent_description_files
  add primary key (id);

create table if not exists patent_department_db.patent_logs
(
  id        int auto_increment,
  patent_id int         not null,
  user_id   int         not null,
  timestamp datetime    null,
  status    varchar(20) null,
  constraint patent_log_id_uindex
  unique (id),
  constraint patent_log_patents_id_fk
  foreign key (patent_id) references patent_department_db.patents (id),
  constraint patent_logs_users_id_fk
  foreign key (user_id) references patent_department_db.users (id)
);

alter table patent_department_db.patent_logs
  add primary key (id);

create trigger before_patent_log_insert
  before INSERT
  on patent_department_db.patent_logs
  for each row
  BEGIN
    IF ((SELECT COUNT(*)
         FROM patent_department_db.patent_logs
         WHERE patent_department_db.patent_logs.patent_id = NEW.patent_id
           AND patent_department_db.patent_logs.status IN ('closed', 'canceled')) > 0)
    THEN
      SIGNAL SQLSTATE '45000'
      SET message_text = 'Not able to add records after final record added';
    END IF;
  END;

create table if not exists patent_department_db.patent_request_files
(
  id        int auto_increment,
  patent_id int          not null,
  file_name varchar(255) null,
  constraint patent_request_files_id_uindex
  unique (id),
  constraint patent_request_files_patents_id_fk
  foreign key (patent_id) references patent_department_db.patents (id)
);

alter table patent_department_db.patent_request_files
  add primary key (id);

create table if not exists patent_department_db.patent_roles
(
  id        int auto_increment,
  patent_id int         not null,
  user_id   int         not null,
  role      varchar(10) not null,
  constraint patent_roles_id_uindex
  unique (id),
  constraint patent_roles_patents_id_fk
  foreign key (patent_id) references patent_department_db.patents (id),
  constraint patent_roles_users_id_fk
  foreign key (user_id) references patent_department_db.users (id)
);

alter table patent_department_db.patent_roles
  add primary key (id);

create table if not exists patent_department_db.user_sessions
(
  id          varchar(64) not null,
  user_id     int         not null,
  valid_until datetime    not null,
  constraint user_sessions_id_uindex
  unique (id),
  constraint user_sessions_users_id_fk
  foreign key (user_id) references patent_department_db.users (id)
);

