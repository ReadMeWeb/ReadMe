DROP TABLE IF EXISTS Loans;
DROP TABLE IF EXISTS Books;
DROP TABLE IF EXISTS Authors;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users
(
    username VARCHAR(255) PRIMARY KEY NOT NULL,
    password VARCHAR(255)             NOT NULL,
    status   ENUM ('ADMIN', 'USER')   NOT NULL
);

CREATE TABLE Authors
(
    id           INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    name_surname varchar(255)                   NOT NULL
);

CREATE TABLE Books
(
    id               INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    title            varchar(255)                   NOT NULL,
    author_id        INT                            NOT NULL,
    description      varchar(6000)                  NOT NULL,
    cover_file_name  varchar(255)                   NOT NULL,
    number_of_copies INT                            NOT NULL,
    FOREIGN KEY (author_id) REFERENCES Authors (id),
    CONSTRAINT non_zero_copies CHECK (number_of_copies >= 0)
);

CREATE TABLE Loans
(
    id                   INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    book_id              INT                            NOT NULL,
    user_username        VARCHAR(255)                   NOT NULL,
    loan_start_date      DATE                           NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    loan_expiration_date DATE                           NOT NULL,
    FOREIGN KEY (book_id) REFERENCES Books (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_username) REFERENCES Users (username) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT check_dates CHECK (loan_expiration_date > loan_start_date)
);