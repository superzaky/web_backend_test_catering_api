CREATE TABLE Facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

INSERT INTO Facilities (name)
VALUES ('Test');

SELECT * FROM Facilities where id= '1';