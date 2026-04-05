drop database if exists tubeyou;

create database if not exists tubeyou;
use tubeyou;

create table if not exists users
(
id int primary key auto_increment,
email varchar(255) not null unique,
displayName varchar(255) not null,
password varchar(255) not null,
avatar varchar(255),
bio text,
createdAt timestamp default current_timestamp,
emailVerified TINYINT(1) default 0,
verifyToken varchar(64) default null
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
    views int default 0,
    index idx_videos_userId (userId)
);

create table if not exists likes
(
    userId int not null,
    videoId int not null,
    primary key (userId, videoId),
    foreign key (userId) references users(id) on delete cascade,
    foreign key (videoId) references videos(id) on delete cascade,
    type TINYINT not null
);

create table if not exists subscribes
(
    subscriberId int not null,
    subscribedToId int not null,
    primary key (subscriberId, subscribedToId),
    foreign key (subscriberId) references users(id) on delete cascade,
    foreign key (subscribedToId) references users(id) on delete cascade,
    check (subscriberId != subscribedToId)
);

create table if not exists comments
(
    id int primary key auto_increment,
    userId int not null,
    videoId int not null,
    foreign key (userId) references users(id) on delete cascade,
    foreign key (videoId) references videos(id) on delete cascade,
    content text not null,
    createdAt timestamp default current_timestamp
)
