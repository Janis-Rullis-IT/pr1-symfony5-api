DROP PROCEDURE IF EXISTS generate_users;
DELIMITER $$
CREATE PROCEDURE generate_users()
BEGIN
  DECLARE i INT DEFAULT 0;
  DECLARE j INT DEFAULT 0;

  # #57 Generate ~3m users in ~15s.
  WHILE i < 10 DO
  	INSERT INTO user (`name`, `surname`) VALUES('John',  'Doe');
    SET i = i + 1;
  END WHILE;
  
  WHILE j < 18  DO
  	INSERT INTO `user` (`name`, `surname`, `balance`) SELECT `name`, `surname`, `balance` FROM `user`;
    SET j = j + 1;
  END WHILE;
END$$
DELIMITER ;

# TRUNCATE `user`;
# CALL generate_users();
# SELECT * FROM `user` ORDER BY `user`.`id` DESC; 