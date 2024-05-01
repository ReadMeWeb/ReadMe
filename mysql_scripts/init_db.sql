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

drop view if exists active_loans;
create view active_loans as
select b.id as book_id, b.number_of_copies as book_copies, count(b.id) as count_loans
from Books as b inner join Loans as l on b.id = l.book_id
where l.loan_start_date <= CURRENT_DATE and CURRENT_DATE <= l.loan_expiration_date
group by b.id ;

INSERT INTO Users (username,password,status) VALUES ('admin','admin','ADMIN');
INSERT INTO Users (username,password,status) VALUES ('user','user','USER');

INSERT INTO Authors (id, name_surname) VALUES (1, 'Fayina');
INSERT INTO Authors (id, name_surname) VALUES (2, 'Demetri');
INSERT INTO Authors (id, name_surname) VALUES (3, 'Anselm');
INSERT INTO Authors (id, name_surname) VALUES (4, 'Bertine');
INSERT INTO Authors (id, name_surname) VALUES (5, 'Ernesta');
INSERT INTO Authors (id, name_surname) VALUES (6, 'Gennifer');
INSERT INTO Authors (id, name_surname) VALUES (7, 'Karole');
INSERT INTO Authors (id, name_surname) VALUES (8, 'Adi');
INSERT INTO Authors (id, name_surname) VALUES (9, 'Herby');
INSERT INTO Authors (id, name_surname) VALUES (10, 'Lacy');

INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (1, 'Butterfly (La lengua de las mariposas)', 'Proin interdum mauris non ligula pellentesque ultrices. Phasellus id sapien in sapien iaculis congue. Vivamus metus arcu, adipiscing molestie, hendrerit at, vulputate vitae, nisl.', '1.jpeg', 16, 9);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (2, '10 to Midnight', 'Nullam sit amet turpis elementum ligula vehicula consequat. Morbi a ipsum. Integer a nibh.

In quis justo. Maecenas rhoncus aliquam lacus. Morbi quis tortor id nulla ultrices aliquet.', '1.jpeg', 2, 5);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (3, 'Home Run', 'In hac habitasse platea dictumst. Morbi vestibulum, velit id pretium iaculis, diam erat fermentum justo, nec condimentum neque sapien placerat ante. Nulla justo.

Aliquam quis turpis eget elit sodales scelerisque. Mauris sit amet eros. Suspendisse accumsan tortor quis turpis.

Sed ante. Vivamus tortor. Duis mattis egestas metus.', '1.jpeg', 4, 9);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (4, 'Colt Comrades', 'Integer tincidunt ante vel ipsum. Praesent blandit lacinia erat. Vestibulum sed magna at nunc commodo placerat.', '1.jpeg', 5, 7);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (5, 'Jungle Man-Eaters', 'Maecenas leo odio, condimentum id, luctus nec, molestie sed, justo. Pellentesque viverra pede ac diam. Cras pellentesque volutpat dui.

Maecenas tristique, est et tempus semper, est quam pharetra magna, ac consequat metus sapien ut nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris viverra diam vitae quam. Suspendisse potenti.

Nullam porttitor lacus at turpis. Donec posuere metus vitae ipsum. Aliquam non mauris.', '1.jpeg', 2, 6);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (6, 'Jumbo (Billy Rose''s Jumbo)', 'In hac habitasse platea dictumst. Etiam faucibus cursus urna. Ut tellus.

Nulla ut erat id mauris vulputate elementum. Nullam varius. Nulla facilisi.

Cras non velit nec nisi vulputate nonummy. Maecenas tincidunt lacus at velit. Vivamus vel nulla eget eros elementum pellentesque.', '1.jpeg', 3, 4);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (7, 'Ride Beyond Vengeance', 'Maecenas leo odio, condimentum id, luctus nec, molestie sed, justo. Pellentesque viverra pede ac diam. Cras pellentesque volutpat dui.

Maecenas tristique, est et tempus semper, est quam pharetra magna, ac consequat metus sapien ut nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris viverra diam vitae quam. Suspendisse potenti.', '1.jpeg', 14, 6);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (8, 'Blue Like Jazz', 'Maecenas ut massa quis augue luctus tincidunt. Nulla mollis molestie lorem. Quisque ut erat.', '1.jpeg', 13, 9);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (9, 'Raisin in the Sun, A', 'Vestibulum quam sapien, varius ut, blandit non, interdum in, ante. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Duis faucibus accumsan odio. Curabitur convallis.

Duis consequat dui nec nisi volutpat eleifend. Donec ut dolor. Morbi vel lectus in quam fringilla rhoncus.

Mauris enim leo, rhoncus sed, vestibulum sit amet, cursus id, turpis. Integer aliquet, massa id lobortis convallis, tortor risus dapibus augue, vel accumsan tellus nisi eu orci. Mauris lacinia sapien quis libero.', '1.jpeg', 18, 9);
INSERT INTO Books (id, title, description, cover_file_name, number_of_copies, author_id) VALUES (10, 'Vegucated', 'Suspendisse potenti. In eleifend quam a odio. In hac habitasse platea dictumst.', '1.jpeg', 7, 5);


INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (1, 1, 'user', '2024/04/18', '2024/05/09');
INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (2, 2, 'user', '2024/04/28', '2024/06/24');
INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (3, 1, 'user', '2024/04/24', '2024/05/25');
INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (4, 5, 'user', '2024/03/05', '2024/04/30');
INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (5, 10, 'user', '2024/04/10', '2024/05/07');
INSERT INTO  Loans (id, book_id, user_username, loan_start_date, loan_expiration_date) VALUES (6, 3, 'user', '2024/03/14', '2024/06/19');
