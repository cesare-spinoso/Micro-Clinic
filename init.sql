CREATE DATABASE MovieDB;

use MovieDB;

CREATE TABLE Movie(
  title VARCHAR(30) NOT NULL,
  year int NOT NULL,
  length int NOT NULL,
  studioName VARCHAR(30) NOT NULL,
  PRIMARY KEY(title, year)
);

CREATE TABLE StarsIn(
  title VARCHAR(30) NOT NULL,
  year int NOT NULL,
  starName VARCHAR(30) NOT NULL,
  PRIMARY KEY(title, year, starName)
);

CREATE TABLE Star(
  name VARCHAR(30) NOT NULL PRIMARY KEY,
  dob DATE
);