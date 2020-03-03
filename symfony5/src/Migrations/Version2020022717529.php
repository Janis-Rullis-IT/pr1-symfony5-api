<?php
/**
 * #6 Import base tables one by one (so could rollback back one-by-one).
 */
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020022717529 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Create the `order` table.';
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			CREATE TABLE IF NOT EXISTS `order` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`owner_id` int(11) NOT NULL,
			`name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			`surname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			`street` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
			`country` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
			`phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			`state` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			`zip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			`production_cost` int(11) NOT NULL,
			`shipping_cost` int(11) NOT NULL,
			`express_shipping` tinyint(1) DEFAULT NULL,
			`total_cost` int(11) NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
		);
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql('DROP TABLE `order`');
	}
}
