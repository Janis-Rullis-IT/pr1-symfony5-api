<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030514509 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#34 Create `v2_shipping_rate` table with data.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // https://github.com/janis-rullis/pr1/issues/34#issuecomment-595221093 #33 #34 #10 #8 #9.
    $this->addSql("
      CREATE TABLE IF NOT EXISTS `v2_shipping_rate`(
        `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(250) NOT NULL COMMENT 'For table\'s readability. #10 #34',
        `product_type` ENUM('t-shirt', 'mug', 'other') NOT NULL DEFAULT 'other' COMMENT '\'other\' type is added for a graceful fallback (just in case). #9 #10 #34',
        `is_domestic` ENUM('y', 'n') NOT NULL DEFAULT 'n' COMMENT 'Currently us is considered as domestic region. TODO: Think about a more flexible solution when domestic regions can change. #10 #34',
        `is_additional` ENUM('y', 'n') NOT NULL DEFAULT 'n' COMMENT 'Cost for first or additional product. ENUM is more readable, stricter and faster than VARCHAR. #10 #34',
        `is_express` ENUM('y', 'n') NOT NULL DEFAULT 'n' COMMENT 'Standard or express shipping. ENUM is more readable, stricter and faster than VARCHAR. #10 #34',
        `cost` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Limited to smallint because user cant afford nothing more than 10000 (see users init balance). #8 #9 #10 #34',
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `deleted_at` TIMESTAMP NULL DEFAULT NULL,
        `sys_info` VARCHAR(20) DEFAULT NULL COMMENT 'In case if You need to mark/flag or just leave a comment. Like, rates used till 2018-01-01.',
        PRIMARY KEY(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Related information inhttps://github.com/janis-rullis/pr1/issues/33#issuecomment-595099120 #33 #34 #10 #8 #9';
    "); 
    // Insert defined shipping rates.
    $this->addSql("INSERT INTO `v2_shipping_rate` (`id`, `title`, `product_type`, `is_domestic`, `is_additional`, `is_express`, `cost`, `created_at`, `updated_at`, `deleted_at`, `sys_info`) VALUES
      (1, 'T-shirt / US / Standard / First', 't-shirt', 'y', 'n', 'n', 100, '2020-03-05 12:32:46', '2020-03-05 12:44:23', NULL, NULL),
      (2, 'T-shirt / US / Standard / Additional', 't-shirt', 'y', 'y', 'n', 50, '2020-03-05 12:32:46', '2020-03-05 12:46:00', NULL, NULL),
      (3, 'Mug / US / Standard / First', 'mug', 'y', 'n', 'n', 200, '2020-03-05 12:34:14', '2020-03-05 12:44:35', NULL, NULL),
      (4, 'Mug / US / Standard / Additional', 'mug', 'y', 'y', 'n', 100, '2020-03-05 12:34:14', '2020-03-05 12:46:06', NULL, NULL),
      (5, 'T-shirt / International / Standard / First', 't-shirt', 'n', 'n', 'n', 300, '2020-03-05 12:35:19', '2020-03-05 12:45:24', NULL, NULL),
      (6, 'T-shirt / International / Standard / Additional', 't-shirt', 'n', 'y', 'n', 150, '2020-03-05 12:35:19', '2020-03-05 12:47:07', NULL, NULL),
      (7, 'Mug / International / Standard / First', 'mug', 'n', 'n', 'n', 500, '2020-03-05 12:36:50', '2020-03-05 12:45:37', NULL, NULL),
      (8, 'Mug / International / Standard / Additional', 'mug', 'n', 'y', 'n', 250, '2020-03-05 12:36:50', '2020-03-05 12:46:14', NULL, NULL),
      (9, 'T-shirt / US / Express  / First', 't-shirt', 'y', 'n', 'y', 1000, '2020-03-05 12:38:31', '2020-03-05 12:44:52', NULL, NULL),
      (10, 'T-shirt / US / Express Additional', 't-shirt', 'y', 'y', 'y', 1000, '2020-03-05 12:38:31', '2020-03-05 12:46:16', NULL, NULL),
      (11, 'Mug / US / Express  / First', 'mug', 'y', 'n', 'y', 1000, '2020-03-05 12:38:31', '2020-03-05 12:44:56', NULL, NULL),
      (12, 'Mug / US / Express / Additional', 'mug', 'y', 'y', 'y', 1000, '2020-03-05 12:38:31', '2020-03-05 12:46:21', NULL, NULL);
    ");     
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DOP TABLE `v2_shipping_rate`;"); 
	}
}
