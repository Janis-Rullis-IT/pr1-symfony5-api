# #57 Takes ~111s.
DROP PROCEDURE IF EXISTS generate_data;
DELIMITER $$ 
CREATE PROCEDURE generate_data()
BEGIN
	CALL generate_users();
	CALL generate_products();
	CALL generate_orders();
	CALL generate_order_products();
END$$
DELIMITER ;

TRUNCATE `user`;
TRUNCATE `product`;
TRUNCATE `order`;
TRUNCATE `order_product`;

CALL generate_data();