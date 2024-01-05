DROP TABLE IF EXISTS Playlist_Music;
DROP TABLE IF EXISTS Music;
DROP TABLE IF EXISTS Album;
DROP TABLE IF EXISTS Playlist;
DROP TABLE IF EXISTS Artist;
DROP TABLE IF EXISTS Users;

-- Codice SQL per creare la tabella Users
CREATE TABLE Users (
    username VARCHAR(255) PRIMARY KEY NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('ADMIN', 'USER') NOT NULL
);

-- Codice SQL per creare la tabella Playlist
CREATE TABLE Playlist (
    user VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    creation_date DATE NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    description VARCHAR(500),
    file_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (user, name),
    FOREIGN KEY (user) REFERENCES Users(username)
);
-- Gestire la clausola lato server per far si che gli admin non possano creare playlist

-- Codice SQL per creare la tabella Artist
CREATE TABLE Artist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    biography VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL
);

-- Codice SQL per creare la tabella Albums
CREATE TABLE Album (
   id INT auto_increment PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   artist_id INT NOT NULL,
   file_name VARCHAR(255) NOT NULL,
   CONSTRAINT unique_fields UNIQUE (name, artist_id),
   FOREIGN KEY (artist_id) REFERENCES Artist(id)
);

-- Codice SQL per creare la tabella Music
CREATE TABLE Music (
    producer INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    audio_file_name VARCHAR(255) NOT NULL,
    graphic_file_name VARCHAR(255) NOT NULL,
    album INT,
    added_date DATE NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    PRIMARY KEY (producer, name),
    FOREIGN KEY (producer) REFERENCES Artist(id),
    FOREIGN KEY (album) REFERENCES Album(id)
);

-- Codice SQL per creare la tabella Playlist_Music
CREATE TABLE Playlist_Music (
    playlist_user VARCHAR(255) NOT NULL,
    playlist_name VARCHAR(255) NOT NULL,
    music_producer INT NOT NULL,
    music_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (playlist_user, playlist_name, music_producer, music_name),
    FOREIGN KEY (playlist_user, playlist_name) REFERENCES Playlist(user, name),
    FOREIGN KEY (music_producer, music_name) REFERENCES Music(producer, name)
);

-- Inserimento di esempio nella tabella Users
INSERT INTO Users (password, username, status) VALUES
    ('admin', 'admin', 'ADMIN'),
    ('user1', 'user1', 'USER'),
    ('user2', 'user2', 'USER');

-- Inserimento di esempio nella tabella Playlist
INSERT INTO Playlist (user, name, description, file_name) VALUES
    ('user1', 'Playlist1', 'Descrizione Playlist1', 'playlist1_file.mp3'),
    ('user1', 'Playlist2', 'Descrizione Playlist2', 'playlist2_file.mp3'),
    ('user2', 'Playlist3', 'Descrizione Playlist3', 'playlist3_file.mp3');

-- Inserimento di esempio nella tabella Artist
INSERT INTO Artist (name, biography, file_name) VALUES
    ('Artista1', 'Biografia Artista1', 'artist1_photo.jpg'),
    ('Artista2', 'Biografia Artista2', 'artist2_photo.jpg');

-- Inserimento di esempio nella tabella Album
INSERT INTO Album (name, artist_id, file_name) VALUES
    ('Album1', 1, 'album1_file.mp3'),
    ('Album2', 2, 'album1_file.mp3');

-- Inserimento di esempio nella tabella Music
INSERT INTO Music (producer, name, audio_file_name, graphic_file_name,  added_date, album) VALUES
    (1, 'Canzone1', 'song1.mp3','corrupted_file.png', '2023-01-22', 1),
    (2, 'Canzone2', 'song2.mp3','corrupted_file.png', '2023-01-21', 2),
    (2, 'Canzone3', 'song3.mp3','corrupted_file.png', '2023-01-20', null);

-- Inserimento di esempio nella tabella Playlist_Music
INSERT INTO Playlist_Music (playlist_user, playlist_name, music_producer, music_name) VALUES
    ('user1', 'Playlist1', 1, 'Canzone1'),
    ('user1', 'Playlist1', 2, 'Canzone2'),
    ('user2', 'Playlist3', 2, 'Canzone3');
