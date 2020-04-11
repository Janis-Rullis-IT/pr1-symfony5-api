<?php
declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041116311 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#44 Add order INDEXes";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql("ALTER TABLE `order_product` ADD INDEX `order_id` (`order_id`);");
		$this->addSql("ALTER TABLE `order` ADD INDEX `customer_status` (`customer_id`, `status`);");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `order_product` DROP INDEX `order_id`;");
		$this->addSql("ALTER TABLE `order` DROP INDEX `customer_status`;");
	}
}
