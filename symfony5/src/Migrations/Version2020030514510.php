<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030514510 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#34 Create `v2_order_product`.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860 #33 #34 #10 #8 #9'
    $this->addSql("
    CREATE TABLE IF NOT EXISTS `v2_order_product`(
      `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `order_id` INT(10) UNSIGNED NOT NULL COMMENT '`order`.`id` #34 #10 #9',
      `customer_id` INT(10) UNSIGNED NOT NULL COMMENT '`user`.`id` #34 #10 #9',
  
      `seller_id` INT(10) UNSIGNED NOT NULL COMMENT '`user`.`id` #34 #10 #9',
      `seller_title` VARCHAR(250) NOT NULL COMMENT 'Registered at the moment when added. It is not read from the `user` table. The user bought from the owner when he was still called \'Cheap Joe\' and not \'Expensive James\'. See also cost field\'s comment. #10 #34',
  
      `product_id` INT(10) UNSIGNED NOT NULL COMMENT '`product`.`id` #34 #10 #9',
      `product_title` VARCHAR(250) NOT NULL COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will buy product that was still called \'Green\' and not \'Yellow\'. #10 #34',
      `product_cost` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will pay the price which he was satisfied when added. This could include a case when there\'s a discount for the first 100 buyers. Also, this avoids a JOIN thus it\'s faster. #8 #9 #10 #34',
      `product_type` ENUM('t-shirt', 'mug', 'other') NOT NULL DEFAULT 'other' COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will buy product that was still a shirt a not a mug. Also required for matching the `shipping_rate`. \'other\' type is added for a graceful fallback (just in case). #9 #10 #34',
  
      `is_domestic` ENUM('y', 'n') NULL DEFAULT NULL COMMENT ' Required for matching the `shipping_rate`. Allow NULL because the value may not be set when creating this record. Currently us is considered as domestic region. TODO: Think about a more flexible solution when domestic regions can change. #10 #34',
      `is_additional` ENUM('y', 'n') NULL DEFAULT NULL COMMENT 'Cost for tje first or additional product may differ. Allow NULL because the value may not be set when creating this record. ENUM is more readable, stricter and faster than VARCHAR. #10 #34',
      `is_express` ENUM('y', 'n') NULL DEFAULT NULL COMMENT 'Standard or express shipping.  Allow NULL because the value may not be set when creating this record. ENUM is more readable, stricter and faster than VARCHAR. #10 #34',
  
      `shipping_cost` SMALLINT(5) UNSIGNED NULL COMMENT 'Will be set from the `shipping_rates` table. Allow NULL because the value may not be set when creating this record. Limited to smallint because user cant afford nothing more than 10000 (see users init balance). #8 #9 #10 #34',
  
      `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  
      `sys_info` VARCHAR(20) DEFAULT NULL COMMENT 'In case if You need to mark/flag or just leave a comment. Like, rates used till 2018-01-01.',
      PRIMARY KEY(`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Related information in https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860 #33 #34 #10 #8 #9';  
    "); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DROP TABLE `v2_order_product`;"); 
	}
}
