<?php
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030916310 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#36 #38 Create `v2_order` table.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
		CREATE TABLE IF NOT EXISTS `v2_order`(
		  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `status` ENUM('draft', 'completed', 'other') NULL DEFAULT 'draft' COMMENT '#33 #36 #38.',
		  
		  `customer_id` INT(10) UNSIGNED NOT NULL COMMENT '`user`.`id` #34 #10 #9',
		  `product_cost` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will pay the price which he was satisfied when added. This could include a case when there\'s a discount for the first 100 buyers. Also, this avoids a JOIN thus it\'s faster. #8 #9 #10 #34',

		  `is_domestic` ENUM('y', 'n') NULL DEFAULT NULL COMMENT 'Required for matching the `shipping_rate`. Allow NULL because the value may not be set when creating this record. Currently us is considered as domestic region. TODO: Think about a more flexible solution when domestic regions can change. #10 #34',
		  `is_express` ENUM('y', 'n') NULL DEFAULT NULL COMMENT 'Standard or express shipping.  Allow NULL because the value may not be set when creating this record. ENUM is more readable, stricter and faster than VARCHAR. #10 #34',

		  `shipping_cost` SMALLINT(5) UNSIGNED NULL COMMENT 'Will be set from the `shipping_rates` table. Allow NULL because the value may not be set when creating this record. Limited to smallint because user cant afford nothing more than 10000 (see users init balance). #8 #9 #10 #34',
		  `total_cost` SMALLINT(5) UNSIGNED NULL COMMENT 'SUM(`product_cost`, `shipping_cost`). Allow NULL because the value may not be set when creating this record. Limited to smallint because user cant afford nothing more than 10000 (see users init balance). #8 #9 #10 #34',

		  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
		  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  `deleted_at` TIMESTAMP NULL DEFAULT NULL,

		  `sys_info` VARCHAR(20) DEFAULT NULL COMMENT 'In case if You need to mark/flag or just leave a comment. Like, rates used till 2018-01-01.',
		  PRIMARY KEY(`id`)
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Related information in #33 #36 #38.'
    ");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DROP TABLE `v2_order`;");
	}
}
