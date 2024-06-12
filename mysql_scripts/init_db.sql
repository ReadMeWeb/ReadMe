SET NAMES 'utf8mb4';
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

INSERT INTO Users (username,password,status) VALUES ('admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','ADMIN');
# pwd: admin
INSERT INTO Users (username,password,status) VALUES ('user','04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb','USER');
# pwd: user
INSERT INTO Users (username,password,status) VALUES ('user1','0a041b9462caa4a31bac3567e0b6e6fd9100787db2ab433d96f6d178cabfce90','USER');
# pwd: user1
INSERT INTO Users (username,password,status) VALUES ('user2','6025d18fe48abd45168528f18a82e265dd98d421a7084aa09f61b341703901a3','USER');
# pwd: user2

INSERT INTO Authors (id, name_surname) VALUES (1, 'Jane Austen');
INSERT INTO Authors (id, name_surname) VALUES (2, 'Charles Dickens');
INSERT INTO Authors (id, name_surname) VALUES (3, 'Leo Tolstoy');
INSERT INTO Authors (id, name_surname) VALUES (4, 'Fyodor Dostoevsky');
INSERT INTO Authors (id, name_surname) VALUES (5, 'Mark Twain');
INSERT INTO Authors (id, name_surname) VALUES (6, 'William Shakespeare');
INSERT INTO Authors (id, name_surname) VALUES (7, 'Herman Melville');
INSERT INTO Authors (id, name_surname) VALUES (8, 'Mary Shelley');
INSERT INTO Authors (id, name_surname) VALUES (9, 'Oscar Wilde');
INSERT INTO Authors (id, name_surname) VALUES (10, 'Nathaniel Hawthorne');
INSERT INTO Authors (id, name_surname) VALUES (11, 'Miguel de Cervantes');
INSERT INTO Authors (id, name_surname) VALUES (12, 'Franz Kafka');
INSERT INTO Authors (id, name_surname) VALUES (13, 'Edgar Allan Poe');
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Orgoglio e pregiudizio', 'Orgoglio e pregiudizio è un romanzo che esplora lo sviluppo emotivo di Elizabeth Bennet, la protagonista, che impara l\'errore di fare giudizi affrettati e comincia ad apprezzare la differenza tra la bontà superficiale e quella autentica.', 'Orgoglio_e_pregiudizio.jpg', 12, 1);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Emma', 'Emma racconta la storia di Emma Woodhouse, una giovane donna di buona famiglia che si diletta a fare da appaiatrice amorosa per i suoi amici, con risultati spesso imprevisti e complicati.', 'Emma.jpg', 9, 1);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Ragione e sentimento', 'Ragione e sentimento segue le sorelle Dashwood, Elinor e Marianne, le cui prospettive e reazioni agli amori perduti contrastano tra di loro in un mondo governato da rigide regole sociali.', 'Ragione_e_sentimento.jpg', 15, 1);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Oliver Twist', 'Oliver Twist è un romanzo che ritrae le condizioni difficili e gli ambienti sfruttatori dei poveri e degli orfani nel sottobosco londinese del XIX secolo.', 'Oliver_Twist.jpg', 9, 2);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Canto di Natale', 'Canto di Natale racconta la storia di Ebenezer Scrooge, un vecchio avaro che cambia il suo atteggiamento egoista dopo essere stato visitato da una serie di fantasmi la vigilia di Natale.', 'Canto_di_Natale.jpg', 13, 2);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Grandi speranze', 'Grandi speranze segue la vita di Pip, un orfano elevato da uno stato di povertà a quello di ricchezza grazie a un benefattore misterioso, esplorando temi come la crescita personale e le disillusioni.', 'Grandi_speranze.jpg', 9, 2);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Guerra e pace', 'Guerra e pace dettaglia gli eventi dell\'invasione francese della Russia e le vite di cinque famiglie aristocratiche.', 'Guerra_e_pace.jpg', 10, 3);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Anna Karenina', 'Anna Karenina è un\'analisi profonda delle complessità dell\'amore e della vita attraverso la storia di Anna, una donna sposata che inizia una tormentata relazione extraconiugale.', 'Anna_Karenina.jpg', 8, 3);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Resurrezione', 'Resurrezione narra la storia di un nobile che cerca di redimersi dopo aver causato la rovina di una donna innocente, esplorando tematiche di giustizia sociale e redenzione personale.', 'Resurrezione.jpg', 10, 3);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Delitto e castigo', 'Delitto e castigo segue la storia di Raskolnikov, un ex studente che pianifica e realizza l\'omicidio di una vecchia usuraia per provare una teoria personale, ma poi è tormentato dal rimorso e dall\'angoscia.', 'Delitto_e_castigo.jpg', 9, 4);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('I fratelli Karamazov', 'I fratelli Karamazov esplora temi di fede, dubbio e moralità attraverso la storia di una famiglia russa e il complesso rapporto tra il padre e i suoi figli.', 'I_fratelli_Karamazov.jpg', 9, 4);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('L\'idiota', 'L\'idiota racconta la storia del principe Myskin, un uomo di straordinaria innocenza e bontà, la cui purezza mette in luce la corruzione e l\'ipocrisia della società russa del XIX secolo.', 'L\'idiota.jpg', 12, 4);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Le avventure di Tom Sawyer', 'Le avventure di Tom Sawyer narra le avventure di un ragazzo ribelle e fantasioso che vive sulle rive del Mississippi, evidenziando l\'innocenza e la vivacità dell\'infanzia.', 'Le_avventure_di_Tom_Sawyer.jpg', 8, 5);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Le avventure di Huckleberry Finn', 'Le avventure di Huckleberry Finn segue il viaggio di Huck e Jim, uno schiavo in fuga, lungo il fiume Mississippi, esplorando temi di razzismo e libertà.', 'Le_avventure_di_Huckleberry_Finn.jpg', 9, 5);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il principe e il povero', 'Il principe e il povero racconta la storia di due ragazzi identici per aspetto ma di condizioni sociali opposte che si scambiano le vite, portando a una serie di eventi che rivelano le ingiustizie sociali.', 'Il_principe_e_il_povero.jpg', 11, 5);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Amleto', 'Amleto è una tragedia che segue il principe Amleto mentre cerca di vendicare l\'omicidio di suo padre, esplorando temi di vendetta, corruzione e follia.', 'Amleto.jpg', 11, 6);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Romeo e Giulietta', 'Romeo e Giulietta è una tragedia romantica che racconta la storia di due giovani amanti la cui morte riconcilia le loro famiglie rivali.', 'Romeo_e_Giulietta.jpg', 10, 6);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Macbeth', 'Macbeth è una tragedia che esplora la brama di potere e le sue conseguenze distruttive attraverso la storia del generale scozzese Macbeth e della sua ambiziosa moglie.', 'Macbeth.jpg', 10, 6);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Moby Dick', 'Moby Dick racconta la caccia ossessiva del capitano Achab alla balena bianca Moby Dick, esplorando temi di vendetta, destino e la natura umana.', 'Moby_Dick.jpg', 9, 7);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Benito Cereno', 'Benito Cereno è un racconto che esplora temi di schiavitù, inganno e paura attraverso la storia di una nave spagnola di schiavi che viene incontrata da un capitano americano.', 'Benito_Cereno.jpg', 11, 7);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Bartleby lo scrivano', 'Bartleby lo scrivano è un racconto che narra la storia di un misterioso scrivano che lavora per un avvocato a Wall Street e che lentamente si rifiuta di svolgere qualsiasi lavoro, dicendo solo \'Preferirei di no\'.', 'Bartleby_lo_scrivano.jpg', 9, 7);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Frankenstein', 'Frankenstein narra la storia di Victor Frankenstein, uno scienziato che crea una creatura vivente assemblando parti di cadaveri, esplorando temi di creazione, responsabilità e isolamento.', 'Frankenstein.jpg', 10, 8);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('L\'ultimo uomo', 'L\'ultimo uomo è un romanzo apocalittico che segue la storia di Lionel Verney in un mondo devastato da una peste mortale, esplorando temi di sopravvivenza e umanità.', 'L\'ultimo_uomo.jpg', 14, 8);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Mathilda', 'Mathilda è un racconto breve che esplora i temi dell\'amore incestuoso e della perdita attraverso la storia di una giovane donna che è perseguitata dal segreto di suo padre.', 'Mathilda.jpg', 15, 8);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il ritratto di Dorian Gray', 'Il ritratto di Dorian Gray segue la storia di un giovane uomo che desidera restare eternamente giovane, mentre un suo ritratto invecchia al suo posto, esplorando temi di moralità, bellezza e decadenza.', 'Il_ritratto_di_Dorian_Gray.jpg', 10, 9);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('L\'importanza di chiamarsi Ernesto', 'L\'importanza di chiamarsi Ernesto è una commedia che gioca sull\'uso di false identità e giochi di parole per creare situazioni comiche, esplorando temi di verità e inganno.', 'L\'importanza_di_chiamarsi_Ernesto.jpg', 12, 9);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('De Profundis', 'De Profundis è una lunga lettera scritta da Oscar Wilde durante la sua prigionia, riflettendo sulla sua vita, il suo processo e il suo amante, esplorando temi di sofferenza e redenzione.', 'De_Profundis.jpg', 15, 9);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('La lettera scarlatta', 'La lettera scarlatta segue la storia di Hester Prynne, una donna che viene condannata all\'ostracismo nella società puritana del New England dopo aver avuto un figlio fuori dal matrimonio, esplorando temi di peccato, colpa e redenzione.', 'La_lettera_scarlatta.jpg', 8, 10);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('La casa dei sette abbaini', 'La casa dei sette abbaini è un romanzo gotico che racconta la storia della famiglia Pyncheon e della loro maledetta dimora, esplorando temi di colpa ereditaria e redenzione.', 'La_casa_dei_sette_abbaini.jpg', 9, 10);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il fauno di marmo', 'Il fauno di marmo segue le vicende di quattro viaggiatori americani a Roma e le loro interazioni con una statua antica, esplorando temi di arte, peccato e redenzione.', 'Il_fauno_di_marmo.jpg', 13, 10);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Don Chisciotte della Mancia', 'Don Chisciotte della Mancia segue le avventure di un nobile che, influenzato dai romanzi cavallereschi, decide di diventare un cavaliere errante per riportare la giustizia nel mondo, esplorando temi di idealismo e realtà.', 'Don_Chisciotte_della_Mancia.jpg', 9, 11);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Novelle esemplari', 'Novelle esemplari è una raccolta di racconti brevi che esplorano una vasta gamma di temi e personaggi, riflettendo la società spagnola del XVII secolo.', 'Novelle_esemplari.jpg', 10, 11);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('La Galatea', 'La Galatea è un romanzo pastorale che racconta le storie intrecciate di pastori e pastorelle, esplorando temi di amore e natura.', 'La_Galatea.jpg', 13, 11);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il processo', 'Il processo racconta la storia di Josef K., un uomo arrestato e perseguitato da un sistema giudiziario misterioso e opprimente senza mai essere informato del crimine di cui è accusato, esplorando temi di burocrazia e alienazione.', 'Il_processo.jpg', 12, 12);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('La metamorfosi', 'La metamorfosi narra la storia di Gregor Samsa, un commesso viaggiatore che si sveglia una mattina trasformato in un gigantesco insetto, esplorando temi di identità, alienazione e famiglia.', 'La_metamorfosi.jpg', 9, 12);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il castello', 'Il castello segue la storia di K., un agrimensore che cerca di ottenere accesso al misterioso e inaccessibile castello che domina il villaggio, esplorando temi di burocrazia e isolamento.', 'Il_castello.jpg', 10, 12);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il corvo', 'Il corvo è una poesia che narra la visita di un corvo parlante a un uomo in lutto per la perdita della sua amata, esplorando temi di amore, morte e disperazione.', 'Il_corvo.jpg', 8, 13);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('I racconti del terrore', 'I racconti del terrore è una raccolta di racconti brevi che esplorano temi di paura, colpa e follia attraverso una serie di storie macabre e inquietanti.', 'I_racconti_del_terrore.jpg', 11, 13);
INSERT INTO Books (title, description, cover_file_name, number_of_copies, author_id) VALUES ('Il cuore rivelatore', 'Il cuore rivelatore è un racconto breve che narra la storia di un uomo che uccide un anziano e cerca di nascondere il corpo, ma è tormentato dal battito del cuore della vittima, esplorando temi di colpa e paranoia.', 'Il_cuore_rivelatore.jpg', 8, 13);

INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (15, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (20, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (7, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (2, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (8, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (9, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (23, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (18, 'user1', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (1, 'user1', NOW(), NOW() + INTERVAL 30 DAY);

INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (12, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (25, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (3, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (19, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (26, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (13, 'user2', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (10, 'user2', NOW(), NOW() + INTERVAL 30 DAY);

INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (1, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (2, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (3, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (4, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (5, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (6, 'user', NOW(), NOW() + INTERVAL 30 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (7, 'user', NOW(), NOW() + INTERVAL 7 DAY);
INSERT INTO Loans (book_id, user_username, loan_start_date, loan_expiration_date) VALUES (8, 'user', NOW(), NOW() + INTERVAL 7 DAY);

