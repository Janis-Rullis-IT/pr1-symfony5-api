<?php
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030916311 extends AbstractMigration
{

	public function Version2020030916311(): string
	{
		return "#36 #38 Allow `v2_order`.`product_cost` to be NULL.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			ALTER TABLE `v2_order`
			CHANGE `product_cost` `product_cost` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will pay the price which he was satisfied when added. This could include a case when there\'s a discount for the first 100 buyers. Also, this avoids a JOIN thus it\'s faster. #8 #9 #10 #34';
		");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			ALTER TABLE `v2_order`
			CHANGE `product_cost` `product_cost` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Registered at the moment when added. It is not read from the `product` table. This provides that the customer will pay the price which he was satisfied when added. This could include a case when there\'s a discount for the first 100 buyers. Also, this avoids a JOIN thus it\'s faster. #8 #9 #10 #34';
		");
	}
}
