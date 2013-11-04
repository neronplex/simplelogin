create database logindb;

grant all on logindb.* to logindb@localhost identified by 'logindb';

use logindb;

create table users (
	id int not null auto_increment primary key,
	email varchar(255) unique,
	password varchar(255)
	);