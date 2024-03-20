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
    id INT auto_increment PRIMARY KEY,
    user VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    creation_date DATE NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    description VARCHAR(500),
    file_name VARCHAR(255) NOT NULL,
    CONSTRAINT unique_user_name UNIQUE (user, name),
    FOREIGN KEY (user) REFERENCES Users(username) ON DELETE CASCADE ON UPDATE CASCADE
);
-- Gestire la clausola lato server per far si che gli admin non possano creare playlist

-- Codice SQL per creare la tabella Artist
CREATE TABLE Artist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    biography text NOT NULL
);

-- Codice SQL per creare la tabella Albums
CREATE TABLE Album (
   id INT auto_increment PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   artist_id INT NOT NULL,
   CONSTRAINT unique_fields UNIQUE (name, artist_id),
   FOREIGN KEY (artist_id) REFERENCES Artist(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Codice SQL per creare la tabella Music
CREATE TABLE Music (
    id INT auto_increment PRIMARY KEY,
    producer INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    audio_file_name VARCHAR(255) NOT NULL,
    graphic_file_name VARCHAR(255) NOT NULL,
    album INT,
    added_date DATE NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    CONSTRAINT unique_producer_song UNIQUE (producer, name),
    FOREIGN KEY (producer) REFERENCES Artist(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (album) REFERENCES Album(id) ON DELETE CASCADE
);

-- Codice SQL per creare la tabella Playlist_Music
CREATE TABLE Playlist_Music (
    id INT auto_increment PRIMARY KEY,
    playlist_id INT NOT NULL,
    music_id INT NOT NULL,
    CONSTRAINT unique_entry UNIQUE (playlist_id, music_id),
    FOREIGN KEY (playlist_id) REFERENCES Playlist(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (music_id) REFERENCES Music(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Inserimento di esempio nella tabella Users
INSERT INTO Users (password, username, status) VALUES
    ('admin', 'admin', 'ADMIN'),
    ('user1', 'user1', 'USER'),
    ('user2', 'user2', 'USER');

-- Inserimento di esempio nella tabella Playlist
INSERT INTO Playlist (id, user, name, description, file_name) VALUES
    (1, 'user1', 'Playlist1', 'Descrizione Playlist1', 'playlist1_file.mp3'),
    (2, 'user1', 'Playlist2', 'Descrizione Playlist2', 'playlist2_file.mp3'),
    (3, 'user2', 'Playlist3', 'Descrizione Playlist3', 'playlist3_file.mp3');

-- Inserimento di esempio nella tabella Artist
INSERT INTO Artist (name, biography) VALUES
    ('Artista1', 'Biografia Artista1'),
    ('Artista2', 'Biografia Artista2');

-- Inserimento di esempio nella tabella Album
INSERT INTO Album (name, artist_id) VALUES
    (1, 'Album1', 1),
    (2, 'Album2', 2);

-- Inserimento di esempio nella tabella Music
INSERT INTO Music (id, producer, name, audio_file_name, graphic_file_name,  added_date, album) VALUES
    (1, 1, 'Canzone1', 'song1.mp3','corrupted_file.png', '2023-01-22', 1),
    (2, 2, 'Canzone2', 'song2.mp3','corrupted_file.png', '2023-01-21', 2),
    (3, 2, 'Canzone3', 'song3.mp3','corrupted_file.png', '2023-01-20', null);

-- Inserimento di esempio nella tabella Playlist_Music
INSERT INTO Playlist_Music (id, playlist_id, music_id) VALUES
    (1, 1, 1),
    (2, 2, 2),
    (3, 3, 3);
