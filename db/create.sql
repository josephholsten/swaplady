CREATE TABLE images (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        name            varchar(255),
        content_type    varchar(255),
        data            blob
);

CREATE TABLE users (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        username        varchar(255),
        name            varchar(255),
        email           varchar(255),
        password        varchar(255),
        paypal          varchar(255),
        address         varchar(255),
        city            varchar(255),
        state           varchar(255),
        zip_code        int(10),
        country         varchar(255),
        balance         int(255)
);

CREATE TABLE tags (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        name            varchar(255)
);

CREATE TABLE transactions (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        date            date
);

CREATE TABLE items (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        image_id        int(255),
        owner_id        int(255),
        name            varchar(255),
        points          int(255),
        description     text,
        weight          int(255),
        sold            tinyint,
        
        FOREIGN KEY (image_id) REFERENCES images (id),
        FOREIGN KEY (owner_id) REFERENCES users (id)
);

CREATE TABLE tags_items (
        tag_id          int(255),
        item_id         int(255),
        
        FOREIGN KEY (tag_id) REFERENCES tags (id),
        FOREIGN KEY (item_id) REFERENCES items (id)
);
                         
CREATE TABLE entries (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        transaction_id  int(255),
        user_id         int(255),
        item_id         int(255),
        type            varchar(255),
        ammount         int(255),
        
        FOREIGN KEY (transaction_id) REFERENCES transactions (id),
        FOREIGN KEY (user_id) REFERENCES users (id),
        FOREIGN KEY (item_id) REFERENCES items (id)
        
);

CREATE TABLE line_items (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        user_id         int(255),
        item_id         int(255),
        shipping        int(255),
        
        FOREIGN KEY (user_id) REFERENCES users (id),
        FOREIGN KEY (item_id) REFERENCES items (id)
);

CREATE TABLE conversations (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        user_id         int(255),
        item_id         int(255),
        
        FOREIGN KEY (user_id) REFERENCES users (id),
        FOREIGN KEY (item_id) REFERENCES items (id)
);

CREATE TABLE messages (
        id              int(255) AUTO_INCREMENT PRIMARY KEY,
        conversation_id int(255),
        author_id       int(255),
        created_on      date,
        body            text,
        
        FOREIGN KEY (conversation_id) REFERENCES conversations (id),
        FOREIGN KEY (author_id) REFERENCES users (id)
);


INSERT INTO users (id,   username,       name,                   email,   password,          paypal, balance)
VALUES            ( 1, "swaplady", "Swaplady", "swaplady@swaplady.com", "swaplady", "toydrumcarlos",       0);