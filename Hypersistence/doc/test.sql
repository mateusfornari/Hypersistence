SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `test` ;

-- -----------------------------------------------------
-- Table `test`.`person`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`person` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `email` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `test`.`book`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`book` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `person_id` INT NOT NULL ,
  `title` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_book_person_idx` (`person_id` ASC) ,
  CONSTRAINT `fk_book_person`
    FOREIGN KEY (`person_id` )
    REFERENCES `test`.`person` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `test`.`student`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`student` (
  `id` INT NOT NULL ,
  `number` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_student_person1`
    FOREIGN KEY (`id` )
    REFERENCES `test`.`person` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `test`.`course`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`course` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `description` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `test`.`student_course`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `test`.`student_course` (
  `student_id` INT NOT NULL ,
  `course_id` INT NOT NULL ,
  PRIMARY KEY (`student_id`, `course_id`) ,
  INDEX `fk_student_has_course_course1_idx` (`course_id` ASC) ,
  INDEX `fk_student_has_course_student1_idx` (`student_id` ASC) ,
  CONSTRAINT `fk_student_has_course_student1`
    FOREIGN KEY (`student_id` )
    REFERENCES `test`.`student` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_student_has_course_course1`
    FOREIGN KEY (`course_id` )
    REFERENCES `test`.`course` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

USE `test` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
