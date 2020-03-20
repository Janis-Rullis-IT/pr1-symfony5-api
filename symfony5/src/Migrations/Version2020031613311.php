<?php
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020031613311 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#40 Add address fields to `v2_order`.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
				ALTER TABLE `v2_order`
				ADD `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
				ADD `surname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
				ADD `street` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
				ADD `country` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
				ADD `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
				ADD `state` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				ADD `zip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL;
		");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			ALTER TABLE `v2_order`
			DROP `name`, 
			DROP `surname`, 
			DROP `street`,
			DROP `country`,
			DROP `phone`,
			DROP `state`,
			DROP `zip`;
		");
	}
}
