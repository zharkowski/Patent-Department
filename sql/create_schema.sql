create table groups
(
  id   int auto_increment,
  name varchar(15) not null,
  constraint groups_id_group_uindex
  unique (id),
  constraint groups_name_uindex
  unique (name)
);

alter table groups
  add primary key (id);

create table patent_files
(
  id                 int auto_increment,
  download_time      datetime     not null,
  description_format varchar(5)   not null,
  description_link   varchar(255) not null,
  request_format     varchar(5)   not null,
  request_link       varchar(255) not null,
  constraint patent_description_files_id_uindex
  unique (id)
);

alter table patent_files
  add primary key (id);

create table users
(
  id       int auto_increment,
  password varchar(255) not null,
  login    varchar(25)  not null,
  name     varchar(255) not null,
  email    varchar(255) not null,
  `group`  int          not null,
  constraint users_id_uindex
  unique (id),
  constraint users_login_uindex
  unique (login),
  constraint users_groups_id_fk
  foreign key (`group`) references groups (id)
);

alter table users
  add primary key (id);

create table patents
(
  id      int auto_increment,
  title   varchar(255) not null,
  file    int          null,
  user_id int          not null,
  constraint requests_id_uindex
  unique (id),
  constraint patents_patent_files_id_fk
  foreign key (file) references patent_files (id),
  constraint patents_users_id_fk
  foreign key (user_id) references users (id)
);

