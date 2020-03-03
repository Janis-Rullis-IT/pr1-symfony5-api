<?php
/**
 * #6 Import base tables one by one (so could rollback back one-by-one).
 */
declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020022717530 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Create the `product` table.';
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
			CREATE TABLE IF NOT EXISTS `product` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`owner_id` int(11) NOT NULL,
			`type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			`title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
			`sku` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
			`cost` int(11) NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
		);
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql('DROP TABLE `product`');
	}
}
