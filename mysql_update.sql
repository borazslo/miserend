/*
Dec 26, 2015 
5611fc3ef7c1053a1c9cc8cbc1ed6e4ebd6efe56
for: 94e45566ae74ee93bb67ff0c9cbc971c6f01ebd3
*/
ALTER TABLE `osm` 
DROP COLUMN `tid`,
CHANGE COLUMN `id` `osmid` VARCHAR(11) NOT NULL,
CHANGE COLUMN `type` `osmtype` VARCHAR(9) NOT NULL ,
ADD COLUMN `lon` DECIMAL(10,8) NULL DEFAULT NULL AFTER `osmtype`,
ADD COLUMN `lat` DECIMAL(11,8) NULL DEFAULT NULL AFTER `lon`,
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `lat`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`;
ALTER TABLE `osm` 
ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
ADD UNIQUE INDEX `idUNIQUE` (`osmid` ASC, `osmtype` ASC),
ADD PRIMARY KEY (`id`);

ALTER TABLE `osm_tags` 
DROP COLUMN `type`,
CHANGE COLUMN `id` `osm_id` INT(11) NOT NULL ,
ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `value`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`,
DROP INDEX `valami` ,
ADD UNIQUE INDEX `valami` (`osm_id` ASC, `name` ASC),
ADD PRIMARY KEY (`id`), RENAME TO  `osmtags` ;

/*
Dec 28, 2015 
for: 25c1a2202a7524d7745c5887f54d30ec352c7e89
*/
ALTER TABLE templomok ENGINE=InnoDB;
ALTER TABLE `templomok` 
CHANGE COLUMN `id` `id` INT(11) NOT NULL ,
ADD PRIMARY KEY (`id`);

ALTER TABLE kepek ENGINE=InnoDB;
DELETE FROM kepek WHERE `tid` NOT IN  (SELECT id FROM templomok);
ALTER TABLE `kepek` 
CHANGE COLUMN `tid` `church_id` INT(11) NOT NULL ,
CHANGE COLUMN `fajlnev` `filename` VARCHAR(100) NOT NULL DEFAULT '' ,
CHANGE COLUMN `felirat` `title` VARCHAR(250) NOT NULL DEFAULT '' ,
CHANGE COLUMN `sorszam` `order` INT(2) NOT NULL DEFAULT '0' ,
CHANGE COLUMN `kiemelt` `flag` ENUM('i','n') NOT NULL DEFAULT 'i' ,
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `width`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`, RENAME TO  `photos` ;

DELETE FROM distance WHERE `tid1` NOT IN  (SELECT id FROM templomok);
DELETE FROM distance WHERE `tid2` NOT IN  (SELECT id FROM templomok);
ALTER TABLE `distance` 
CHANGE COLUMN `tid1` `from` INT(11) NOT NULL ,
CHANGE COLUMN `tid2` `to` INT(11) NOT NULL ,
CHANGE COLUMN `updated` `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' ,
ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `toupdate`,
ADD PRIMARY KEY (`id`), RENAME TO  `distances` ;

ALTER TABLE eszrevetelek ENGINE=InnoDB;
DELETE FROM eszrevetelek WHERE hol_id NOT IN  (SELECT id FROM templomok);
ALTER TABLE `eszrevetelek` 
DROP COLUMN `hol`,
CHANGE COLUMN `datum` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `log`,
CHANGE COLUMN `hol_id` `church_id` INT(11) NOT NULL DEFAULT '0' ,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`, RENAME TO  `remarks` ;

ALTER TABLE `osmtags`
ADD CONSTRAINT `FK_osm_id` 
  FOREIGN KEY (`osm_id`) 
  REFERENCES `osm` (`id`) 
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `distances` 
ADD CONSTRAINT `FK_to`
  FOREIGN KEY (`to`)
  REFERENCES `templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_from`
  FOREIGN KEY (`from`)
  REFERENCES `templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `remarks` 
ADD CONSTRAINT `FK_church_id`
  FOREIGN KEY (`church_id`)
  REFERENCES `templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `photos` 
ADD CONSTRAINT `FKchurch`
  FOREIGN KEY (`church_id`)
  REFERENCES `templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

/* Dec 29, 2015
*/
ALTER TABLE `templomok` 
ADD PRIMARY KEY (`id`);
ALTER TABLE `templomok` 
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ;

CREATE TABLE `lookup_church_osm` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `church_id` INT(11) NOT NULL,
  `osm_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`));
ALTER TABLE `lookup_church_osm` 
ADD INDEX `FK_church_id_idx` (`church_id` ASC),
ADD INDEX `FK_osm_id_idx` (`osm_id` ASC);
ALTER TABLE `lookup_church_osm` 
ADD CONSTRAINT `FK_lookup_church_osm_osm_id`
  FOREIGN KEY (`osm_id`)
  REFERENCES `osm` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;
ALTER TABLE `lookup_church_osm` 
ADD UNIQUE INDEX `unique` (`church_id` ASC, `osm_id` ASC);

CREATE TABLE `lookup_osm_enclosed` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `osm_id` INT(11) NOT NULL,
  `enclosing_id` INT(11) NOT NULL,
  `created_at` VARCHAR(45) NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  INDEX `FK_osm_id_idx` (`osm_id` ASC),
  INDEX `FK_osm_enclosing_id_idx` (`enclosing_id` ASC),
  CONSTRAINT `FK_lookup_osm_enclosed_osm`
    FOREIGN KEY (`osm_id`)
    REFERENCES `osm` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_lookup_osm_enclosed_enclosing`
    FOREIGN KEY (`enclosing_id`)
    REFERENCES `osm` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);
ALTER TABLE `lookup_osm_enclosed` 
ADD UNIQUE INDEX `unique` (`enclosing_id` ASC, `osm_id` ASC);

/* Dec 20, 2015
*/
ALTER TABLE `templomok` 
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `eszrevetel`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`;
ALTER TABLE `photos` 
CHANGE COLUMN `order` `weight` INT(2) NOT NULL DEFAULT '0' ;
