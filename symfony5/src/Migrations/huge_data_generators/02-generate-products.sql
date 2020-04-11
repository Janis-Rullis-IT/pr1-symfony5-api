DROP PROCEDURE IF EXISTS generate_products;
DELIMITER $$ 
CREATE PROCEDURE generate_products()
BEGIN
  DECLARE i INT DEFAULT 0;
  DECLARE j INT DEFAULT 0;

  # #57 Temporily remove indexes to avoid constraints and slowdown.
  DROP INDEX `sku` ON `product`;
  DROP INDEX `owner_id` ON `product`;
  
  WHILE i < 10 DO
	INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) VALUES (1, 'mug', 'Mug', i, '100');
	INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) VALUES (1, 't-shirt', 't-shirt', i + 100, '100');
    SET i = i + 1;
  END WHILE;
  
  WHILE j < 16  DO
  	INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) SELECT `owner_id` * 2, `type`, `title`, SHA1(CONCAT(`sku`, '-', `id`, '-',j)), `cost` FROM `product`;
    SET j = j + 1;
  END WHILE;

  # #57 Return indexes.
  ALTER TABLE `product`
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `owner_id` (`owner_id`);
END$$
DELIMITER ;

# TRUNCATE `product`;
# CALL generate_products();
# SELECT * FROM `product` ORDER BY `product`.`id` DESC;


# No index 98s NO SHA1.
# INDEX SHA 250s.
# No INDEX SHA 110s.
# No INDEX SHA + return index 160s.