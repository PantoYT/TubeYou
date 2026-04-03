create database if not exists tubeyou;
use tubeyou;

create table if not exists users
(
id int primary key auto_increment,
email varchar(255) not null unique,
displayName varchar(255) not null,
password varchar(255) not null
);

create table if not exists videos
(
    id int primary key auto_increment,
    title varchar(255) not null,
    src varchar(255) not null,
    userId int not null,
    foreign key (userId) references users(id) on delete cascade,
    thumbnail varchar(255) not null,
    description text not null,
    duration int default 0,
    createdAt timestamp default current_timestamp,
    views int default 0
);