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
    banner varchar(255),
    bio text,
    createdAt timestamp default current_timestamp,
    emailVerified TINYINT(1) default 0,
    verifyToken varchar(64) default null,
    resetToken    VARCHAR(64) DEFAULT NULL,
    resetExpiry   DATETIME    DEFAULT NULL
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
    views bigint default 0,
    index idx_videos_userId (userId),
    fulltext idx_fulltext_title_desc (title, description)
);

create table if not exists likes
(
    userId int not null,
    videoId int not null,
    primary key (userId, videoId),
    foreign key (userId) references users(id) on delete cascade,
    foreign key (videoId) references videos(id) on delete cascade,
    type TINYINT not null,
    check (type in (1, -1))
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
    id        int primary key auto_increment,
    userId    int not null,
    videoId   int not null,
    parentId  int default null,
    pinned    tinyint(1) default 0,
    content   text not null,
    createdAt timestamp default current_timestamp,
    foreign key (userId)   references users(id)    on delete cascade,
    foreign key (videoId)  references videos(id)   on delete cascade,
    foreign key (parentId) references comments(id) on delete cascade
);

create table if not exists commentLikes
(
    userId    int not null,
    commentId int not null,
    type      tinyint not null check (type in (1, -1)),
    primary key (userId, commentId),
    foreign key (userId)    references users(id)    on delete cascade,
    foreign key (commentId) references comments(id) on delete cascade
);

CREATE TABLE tags (
    id   INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE videoTags (
    videoId INT NOT NULL,
    tagId   INT NOT NULL,
    PRIMARY KEY (videoId, tagId),
    FOREIGN KEY (videoId) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (tagId)   REFERENCES tags(id)   ON DELETE CASCADE
);

CREATE TABLE history (
    userId    INT NOT NULL,
    videoId   INT NOT NULL,
    watchedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, videoId),
    FOREIGN KEY (userId)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (videoId) REFERENCES videos(id) ON DELETE CASCADE
);