# #57 1310720 records in 9s.
DROP PROCEDURE IF EXISTS generate_order_products;
DELIMITER $$ 
CREATE PROCEDURE generate_order_products()
BEGIN
  DECLARE i INT DEFAULT 0;
  DECLARE j INT DEFAULT 0;

  WHILE i < 10 DO
  INSERT INTO `order_product` (`order_id`, `customer_id`, `seller_id`, `product_id`) VALUES (i + 1, i + 1, i + 2, i + 1);
    SET i = i + 1;
  END WHILE;
  
  WHILE j < 17  DO
  	INSERT INTO `order_product` (`order_id`, `customer_id`, `seller_id`, `product_id`) SELECT `order_id` * 2, `customer_id` * 2, `seller_id` * 2, `product_id` * 2 FROM `order_product`;
    SET j = j + 1;
  END WHILE;
END$$
DELIMITER ;

# TRUNCATE `order_product`;
# CALL generate_order_products();
# SELECT * FROM `order_product` ORDER BY `order_product`.`id` DESC;