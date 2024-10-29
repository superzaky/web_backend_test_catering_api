SET FOREIGN_KEY_CHECKS=0; -- to disable them
DROP TABLE IF EXISTS `facilities`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `facility_tags`;
SET FOREIGN_KEY_CHECKS=1; -- to re-enable them

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(255),
    address VARCHAR(255),
    zip_code VARCHAR(6),
    country_code VARCHAR(3),
    phone_number VARCHAR(15)
);

CREATE TABLE facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    creation_date DATE,
    location_id INT,
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE
);


CREATE TABLE facility_tags (
    facility_id INT,
    tag_id INT,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (facility_id, tag_id)
);

INSERT INTO locations (city, address, zip_code, country_code, phone_number)
VALUES ('Amsterdam', 'Rozengracht 1', '1000AA', 'NL', '0201234567'),
 ('Amsterdam', 'Jan van Galenstraat 1', '1001AA', 'NL', '0201234568');


INSERT INTO facilities (name, creation_date, location_id)
VALUES ('Voetbalclub', curdate(), 1);

INSERT INTO tags (name)
VALUES ('Sport');

INSERT INTO facility_tags (facility_id, tag_id)
VALUES (1,1);
