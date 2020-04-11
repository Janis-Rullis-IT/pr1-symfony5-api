# #57 Generate 2618646 orders in 20s.
DROP PROCEDURE IF EXISTS generate_orders;
DELIMITER $$
CREATE PROCEDURE generate_orders()
BEGIN
  DECLARE i INT DEFAULT 0;
  DECLARE j INT DEFAULT 0;

  WHILE i < 10 DO
  	INSERT INTO `order` (`status`, `customer_id`) VALUES('completed',  i + 1);
    SET i = i + 1;
  END WHILE;
  
  WHILE j < 18  DO
  	INSERT INTO `order` (`status`, `customer_id`) SELECT `status`, `customer_id` * 2 FROM `order`;
    SET j = j + 1;
  END WHILE;
END$$
DELIMITER ;

# TRUNCATE `order`;
# CALL generate_orders();
# SELECT * FROM `order` ORDER BY `order`.`id` DESC; 