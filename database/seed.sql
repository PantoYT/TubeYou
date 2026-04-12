use tubeyou;

-- =========================
-- PANTO (STAŁY)
-- =========================
insert into users (email, displayName, password, avatar, banner, bio, emailVerified)
values (
'panto@tubeyou.com','Panto',
'$2y$10$cfM2MzNC3QW0calwvXy8tOiD03glK9KpKdVCcFTtYfQIjUtm335h2',
'/uploads/avatars/1/avatar.png',
'/uploads/banners/1/banner.png',
'Gaming montages',
1
);

-- =========================
-- RANDOM USER COUNT (80–140)
-- =========================
set @userCount = floor(80 + rand()*60);

-- =========================
-- GENERATOR NICKÓW
-- =========================
insert into users (email, displayName, password, emailVerified)
select
    concat(
        lower(
            substring('Shadow Blaze Nova Frost Echo Vortex Pixel Ghost Alpha Neon Drift Rogue Zenith Orbit Pulse Flare Byte Storm', 
            1 + floor(rand()*120), 
            6)
        ),
        floor(rand()*999)
    ),
    concat(
        substring('Shadow Blaze Nova Frost Echo Vortex Pixel Ghost Alpha Neon Drift Rogue Zenith Orbit Pulse Flare Byte Storm', 
        1 + floor(rand()*120), 
        6),
        floor(rand()*999)
    ),
    '$2y$10$cfM2MzNC3QW0calwvXy8tOiD03glK9KpKdVCcFTtYfQIjUtm335h2',
    1
from (
    select @row:=@row+1 as n
    from (select 0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) a,
         (select 0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) b,
         (select @row:=0) r
) t
where n <= @userCount;

-- =========================
-- FILMY (PANTO ONLY)
-- =========================
insert into videos (title, src, userId, thumbnail, description, duration, views)
select 'I like the way you kiss me','/uploads/videos/1/69ce646e471917.68634994.mp4',id,'/uploads/thumbnails/1/69ce646e471917.68634994.jpg','Valorant montage',70, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'Bombs Away','/uploads/videos/1/69cf1564e75d5.mp4',id,'/uploads/thumbnails/1/69cf1564e9b65.jpg','Valorant montage',76, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'Bones','/uploads/videos/1/69cf159cc35b5.mp4',id,'/uploads/thumbnails/1/69cf159cc35b8.jpg','Valorant montage',107, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'Cupid','/uploads/videos/1/69cf15bbcf7f3.mp4',id,'/uploads/thumbnails/1/69cf15bbcf7f5.jpg','Valorant montage',146, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'Hills','/uploads/videos/1/69cf15f125ba3.mp4',id,'/uploads/thumbnails/1/69cf15f125ba6.jpg','Valorant montage',73, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'I Aint Worried','/uploads/videos/1/69cf1604d17b4.mp4',id,'/uploads/thumbnails/1/69cf1604d17b6.jpg','Valorant montage',110, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'No Lie','/uploads/videos/1/69cf164c2abcd.mp4',id,'/uploads/thumbnails/1/69cf164c2abcf.jpg','Valorant montage',58, floor(200 + rand()*2000) from users where email='panto@tubeyou.com'
union all
select 'Sunroof','/uploads/videos/1/69cf1660d60b8.mp4',id,'/uploads/thumbnails/1/69cf1660d60ba.jpg','Valorant montage',90, floor(200 + rand()*2000) from users where email='panto@tubeyou.com';

-- =========================
-- SUBY (wszyscy -> Panto)
-- =========================
insert into subscribes (subscriberId, subscribedToId)
select u.id, p.id
from users u
join users p on p.email='panto@tubeyou.com'
where u.email != 'panto@tubeyou.com';

-- =========================
-- LAJKI (random density)
-- =========================
insert into likes (userId, videoId, type)
select u.id, v.id, if(rand()>0.15,1,-1)
from users u
join videos v
where u.email != 'panto@tubeyou.com'
and rand() > 0.55;

-- =========================
-- 30 KOMENTARZY (POOL)
-- =========================
set @comments = 'Nice video|🔥|Clean edit|LOL|Insane|WTF|Best one|GG|So smooth|Respect|Clutch|Crazy|Nice aim|No way|XD|Amazing|Sick|Love this|Top tier|Well played|Pro move|Nice clip|Good vibes|Epic|Legendary|Fire|Solid|Peak|Cool edit|Perfect';

-- =========================
-- KOMENTARZE (losowe)
-- =========================
insert into comments (userId, videoId, content)
select u.id, v.id,
       substring_index(substring_index(@comments, '|', floor(1 + rand()*30)), '|', -1)
from users u
join videos v
where u.email != 'panto@tubeyou.com'
and rand() > 0.8;

-- =========================
-- COMMENT LIKES
-- =========================
insert into commentLikes (userId, commentId, type)
select u.id, c.id, 1
from users u
join comments c
where rand() > 0.7;

-- =========================
-- PLAYLISTY
-- =========================
insert into playlists (userId, title, description)
select id,
       concat('playlist_', floor(rand()*999)),
       'auto generated'
from users
where email != 'panto@tubeyou.com'
and rand() > 0.7;

-- =========================
-- PLAYLIST VIDEOS
-- =========================
insert into playlistVideos (playlistId, videoId, position)
select p.id, v.id, floor(rand()*20)
from playlists p
join videos v
where rand() > 0.5;

-- =========================
-- HISTORY
-- =========================
insert into history (userId, videoId)
select u.id, v.id
from users u
join videos v
where u.email != 'panto@tubeyou.com'
and rand() > 0.4;

-- =========================
-- WATCH LATER
-- =========================
insert into watchLater (userId, videoId)
select u.id, v.id
from users u
join videos v
where rand() > 0.85;

-- =========================
-- NOTIFICATIONS
-- =========================
insert into notifications (userId, fromUserId, type, videoId)
select p.id, u.id,
       if(rand()>0.5,'like','sub'),
       v.id
from users u
join users p on p.email='panto@tubeyou.com'
join videos v
where u.email != 'panto@tubeyou.com'
and rand() > 0.75;