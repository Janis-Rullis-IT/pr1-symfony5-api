<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030313551 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#22 `relation`: Make integer field UNSIGNED because this will allow more POSITIVE values.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // #22 `CHANGE` https://github.com/janis-rullis/sql/blob/master/mysql/basics/12_managing_databases_and_tables.md#tables    
    $this->addSql("ALTER TABLE `relation`
      CHANGE `owner_id` `owner_id` int(11) UNSIGNED NOT NULL,
      CHANGE `order_id` `order_id` int(11) UNSIGNED NOT NULL,
      CHANGE `product_id` `product_id` int(11) UNSIGNED NOT NULL,
      CHANGE `quantity` `quantity` int(11) UNSIGNED NOT NULL
    "); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `relation`
      CHANGE `owner_id` `owner_id` int(11) NOT NULL,
      CHANGE `order_id` `order_id` int(11) NOT NULL,
      CHANGE `product_id` `product_id` int(11) NOT NULL,
      CHANGE `quantity` `quantity` int(11) NOT NULL
    "); 
	}
}
