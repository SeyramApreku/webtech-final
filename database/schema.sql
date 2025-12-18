-- GriotShelf Complete Database Schema (Final Version)
-- Includes: Users (with username), Books (with expanded metadata), Reviews (spoilers), Shelves, Follows
-- Populated with 20 African Literature Classics

DROP DATABASE IF EXISTS griotshelf;
CREATE DATABASE griotshelf;
USE griotshelf;

-- 1. USERS TABLE
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    privacy_want_to_read TINYINT(1) DEFAULT 1,       -- 1=Public, 0=Private
    privacy_currently_reading TINYINT(1) DEFAULT 1,  -- 1=Public, 0=Private
    privacy_finished TINYINT(1) DEFAULT 1,           -- 1=Public, 0=Private
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. BOOKS TABLE (Expanded)
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    cover_url VARCHAR(500),
    publication_year INT,
    genre VARCHAR(50),
    region VARCHAR(50),      -- New: West Africa, East Africa, Southern Africa, North Africa, Diaspora
    language VARCHAR(50),    -- New: English, French, etc.
    isbn VARCHAR(20),        -- New: ISBN-13
    page_count INT,          -- New
    publisher VARCHAR(100),  -- New
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. REVIEWS TABLE
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    contains_spoilers TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- 4. READING LIST TABLE
CREATE TABLE reading_list (
    list_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('want_to_read', 'currently_reading', 'finished') DEFAULT 'want_to_read',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_book (user_id, book_id)
);

-- 5. SHELVES TABLE
CREATE TABLE shelves (
    shelf_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shelf_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_public TINYINT(1) DEFAULT 1,      -- 1=Public, 0=Private
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. SHELF_BOOKS TABLE
CREATE TABLE shelf_books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shelf_id INT NOT NULL,
    book_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shelf_id) REFERENCES shelves(shelf_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_shelf_book (shelf_id, book_id)
);

-- 7. FOLLOWS TABLE
CREATE TABLE follows (
    follow_id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    CHECK (follower_id != following_id)
);

-- 8. CONTACT MESSAGES TABLE
CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEED DATA: BOOKS
INSERT INTO books (title, author, description, cover_url, publication_year, genre, region, language, isbn, page_count, publisher) VALUES
('Things Fall Apart', 'Chinua Achebe', 'A masterpiece that tells the story of Okonkwo, a strong man whose life is dominated by fear and anger, set in Nigeria during colonization.', 'https://books.google.com/books/content?id=6IjrCgAAQBAJ&printsec=frontcover&img=1&zoom=1', 1958, 'Classic Fiction', 'West Africa', 'English', '978-0385474542', 209, 'Anchor Books'),
('Half of a Yellow Sun', 'Chimamanda Ngozi Adichie', 'Epic tale of the Nigerian Civil War through the eyes of three characters whose lives are forever changed.', 'https://books.google.com/books/content?id=5_egk5B6jBIC&printsec=frontcover&img=1&zoom=1', 2006, 'Historical Fiction', 'West Africa', 'English', '978-1400095209', 433, 'Knopf'),
('Americanah', 'Chimamanda Ngozi Adichie', 'A powerful story about love, race, and identity, following a young Nigerian woman who emigrates to America.', 'https://books.google.com/books/content?id=qFzWMVvL_bkC&printsec=frontcover&img=1&zoom=1', 2013, 'Contemporary Fiction', 'Diaspora', 'English', '978-0307455925', 477, 'Knopf'),
('Purple Hibiscus', 'Chimamanda Ngozi Adichie', 'Coming-of-age story set in Nigeria, exploring family, freedom, and finding one\'s voice.', 'https://books.google.com/books/content?id=UVBfMGIqI_IC&printsec=frontcover&img=1&zoom=1', 2003, 'Contemporary Fiction', 'West Africa', 'English', '978-1616202415', 307, 'Algonquin Books'),
('Homegoing', 'Yaa Gyasi', 'An ambitious multigenerational story tracing two half-sisters and their descendants from 18th century Ghana to modern America.', 'https://books.google.com/books/content?id=ePhIDQAAQBAJ&printsec=frontcover&img=1&zoom=1', 2016, 'Historical Fiction', 'West Africa', 'English', '978-1101947135', 305, 'Knopf'),
('The Joys of Motherhood', 'Buchi Emecheta', 'A compelling story of a Nigerian woman navigating tradition and modernity in colonial Lagos.', 'https://books.google.com/books/content?id=KfwaCgAAQBAJ&printsec=frontcover&img=1&zoom=1', 1979, 'Classic Fiction', 'West Africa', 'English', '978-0807609503', 224, 'George Braziller'),
('Nervous Conditions', 'Tsitsi Dangarembga', 'A young Zimbabwean girl\'s struggle for independence against patriarchal traditions.', 'https://books.google.com/books/content?id=0cxFHCQv2QcC&printsec=frontcover&img=1&zoom=1', 1988, 'Classic Fiction', 'Southern Africa', 'English', '978-0954702335', 204, 'Ayebia Clarke'),
('The Beautyful Ones Are Not Yet Born', 'Ayi Kwei Armah', 'A powerful critique of corruption in post-independence Ghana.', 'https://books.google.com/books/content?id=3KuaAAAAIAAJ&printsec=frontcover&img=1&zoom=1', 1968, 'Classic Fiction', 'West Africa', 'English', '978-0435900438', 183, 'Houghton Mifflin'),
('We Need New Names', 'NoViolet Bulawayo', 'A girl\'s journey from Zimbabwe to America, exploring displacement and belonging.', 'https://books.google.com/books/content?id=HTMqnFZGVNMC&printsec=frontcover&img=1&zoom=1', 2013, 'Contemporary Fiction', 'Southern Africa', 'English', '978-0316230810', 298, 'Reagan Arthur Books'),
('The Thing Around Your Neck', 'Chimamanda Ngozi Adichie', 'Collection of short stories exploring Nigerian life and the immigrant experience.', 'https://books.google.com/books/content?id=TcVKI7lYHOwC&printsec=frontcover&img=1&zoom=1', 2009, 'Short Stories', 'West Africa', 'English', '978-0307375230', 218, 'Knopf'),
('Binti', 'Nnedi Okorafor', 'Groundbreaking Afrofuturist novella about a young Himba girl who becomes the first of her people to attend the prestigious Oomza University.', 'https://books.google.com/books/content?id=J6_cCgAAQBAJ&printsec=frontcover&img=1&zoom=1', 2015, 'Science Fiction', 'Southern Africa', 'English', '978-0765385253', 96, 'Tor.com'),
('Who Fears Death', 'Nnedi Okorafor', 'Epic post-apocalyptic science fantasy set in a future Sudan, following a girl born with magical powers.', 'https://books.google.com/books/content?id=PfDEAgAAQBAJ&printsec=frontcover&img=1&zoom=1', 2010, 'Science Fiction', 'East Africa', 'English', '978-0756406691', 416, 'DAW'),
('The River Between', 'Ngugi wa Thiong\'o', 'Set in colonial Kenya, exploring the clash between tradition and Christianity.', 'https://books.google.com/books/content?id=8FYOPAAACAAJ&printsec=frontcover&img=1&zoom=1', 1965, 'Classic Fiction', 'East Africa', 'English', '978-0435900544', 152, 'Heinemann'),
('So Long a Letter', 'Mariama Bâ', 'A Senegalese woman\'s letter to her friend about marriage, tradition, and women\'s place in society.', 'https://books.google.com/books/content?id=3jYdAAAACAAJ&printsec=frontcover&img=1&zoom=1', 1979, 'Classic Fiction', 'West Africa', 'French', '978-0435913520', 90, 'Heinemann'),
('Faceless', 'Amma Darko', 'A gripping novel about street children in Accra, confronting social issues head-on.', 'https://books.google.com/books/content?id=gN5sPQAACAAJ&printsec=frontcover&img=1&zoom=1', 2003, 'Contemporary Fiction', 'West Africa', 'English', '978-9988550509', 232, 'Sub-Saharan Publishers'),
('The Famished Road', 'Ben Okri', 'Magical realist tale following a spirit-child in Nigeria, winner of the Booker Prize.', 'https://books.google.com/books/content?id=TiJ8PgAACAAJ&printsec=frontcover&img=1&zoom=1', 1991, 'Magical Realism', 'West Africa', 'English', '978-0385424769', 500, 'Jonathan Cape'),
('Ghana Must Go', 'Taiye Selasi', 'A family saga spanning continents, exploring identity, belonging, and what it means to be home.', 'https://books.google.com/books/content?id=kKpVMXk_3YYC&printsec=frontcover&img=1&zoom=1', 2013, 'Contemporary Fiction', 'West Africa', 'English', '978-1594204494', 318, 'Penguin Press'),
('Children of Blood and Bone', 'Tomi Adeyemi', 'Epic fantasy inspired by West African mythology, following a girl fighting to restore magic to her land.', 'https://books.google.com/books/content?id=JR8-DwAAQBAJ&printsec=frontcover&img=1&zoom=1', 2018, 'Fantasy', 'West Africa', 'English', '978-1250170972', 525, 'Henry Holt'),
('The Secret Lives of Baba Segi\'s Wives', 'Lola Shoneyin', 'A polygamous household in Nigeria unravels through secrets and lies.', 'https://books.google.com/books/content?id=PgpDAwAAQBAJ&printsec=frontcover&img=1&zoom=1', 2010, 'Contemporary Fiction', 'West Africa', 'English', '978-1846687495', 281, 'Serpent\'s Tail'),
('Born a Crime', 'Trevor Noah', 'Memoir of growing up in South Africa during and after apartheid, filled with humor and heart.', 'https://books.google.com/books/content?id=4xJLCwAAQBAJ&printsec=frontcover&img=1&zoom=1', 2016, 'Memoir', 'Southern Africa', 'English', '978-0399588174', 288, 'Spiegel & Grau');

-- SEED DATA: USERS
-- Password is 'Password123' for all demo users (hashed)
INSERT INTO users (username, first_name, last_name, email, password_hash, is_admin) VALUES
('kwame_reads', 'Kwame', 'Mensah', 'kwame@example.com', '$2y$10$ZgAXEPfieiPcTvJBIYasnerMTJbO5JXJ4qcFKByA8tvVZTpzZloNC', 0),
('bookworm_sarah', 'Sarah', 'Johnson', 'sarah@example.com', '$2y$10$ZgAXEPfieiPcTvJBIYasnerMTJbO5JXJ4qcFKByA8tvVZTpzZloNC', 0),
('zainab_lit', 'Zainab', 'Diallo', 'zainab@example.com', '$2y$10$ZgAXEPfieiPcTvJBIYasnerMTJbO5JXJ4qcFKByA8tvVZTpzZloNC', 0),
('admin', 'System', 'Admin', 'admin@griotshelf.com', '$2y$10$ZgAXEPfieiPcTvJBIYasnerMTJbO5JXJ4qcFKByA8tvVZTpzZloNC', 1);

-- SEED DATA: REVIEWS
INSERT INTO reviews (user_id, book_id, rating, review_text, contains_spoilers) VALUES
(1, 1, 5, 'An absolute classic. Okonkwo\'s struggle is so vividly portrayed. The ending left me speechless.', 0),
(1, 18, 4, 'Incredible world-building! I loved the magic system. Just a bit slow in the middle.', 0),
(2, 1, 5, 'Required reading for everyone. It changed my perspective on African literature.', 0),
(3, 3, 5, 'Ifemelu is one of the most real characters I have ever read. Adichie is a genius.', 0),
(2, 5, 4, 'Heartbreaking and beautiful. The way it spans generations is masterful.', 0),
(1, 5, 5, 'Warning: The ending of the fire chapter is intense! But overall a masterpiece.', 1); -- Spoiler example

-- SEED DATA: SHELVES
INSERT INTO shelves (user_id, shelf_name, description) VALUES
(1, 'Afrofuturism', 'The best of African Sci-Fi and Fantasy'),
(3, 'Modern Classics', 'Contemporary books that will stand the test of time');

-- SEED DATA: SHELF_BOOKS
INSERT INTO shelf_books (shelf_id, book_id) VALUES
(1, 11), -- Binti
(1, 12), -- Who Fears Death
(1, 18), -- Children of Blood and Bone
(2, 2),  -- Half of a Yellow Sun
(2, 3),  -- Americanah
(2, 9);  -- We Need New Names