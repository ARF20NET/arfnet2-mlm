CREATE DATABASE arfnet2;

CREATE TABLE `arfnet2`.`lists` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `name` VARCHAR(31) NOT NULL ,
    `type` ENUM('public','hidden') NOT NULL DEFAULT 'public' ,
    `desc` TEXT NOT NULL ,
    PRIMARY KEY (`id`)
);

CREATE TABLE `arfnet2`.`subscribers` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `email` VARCHAR(31) NOT NULL ,
    `list` VARCHAR(255) NOT NULL ,
    `unsubcode` VARCHAR(127) NOT NULL ,
    `subdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' ,
    PRIMARY KEY (`id`)
);

CREATE TABLE `arfnet2`.`archive` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `list` INT NOT NULL ,
    `subject` VARCHAR(1024) NOT NULL ,
    `message` TEXT NOT NULL ,
    `author` INT NOT NULL ,
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    PRIMARY KEY (`id`)
);
