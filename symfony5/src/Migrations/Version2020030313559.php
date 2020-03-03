<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030313559 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#22 #9 `product`: Make integer field UNSIGNED because this will allow more POSITIVE values, also decrease the cost to SMALLINT.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // #22 #9 `CHANGE` https://github.com/janis-rullis/sql/blob/master/mysql/basics/12_managing_databases_and_tables.md#tables    
    $this->addSql("ALTER TABLE `product`
      CHANGE `owner_id` `owner_id` int(11) UNSIGNED NOT NULL,
      CHANGE `cost` `cost` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Limited to smallint because user cant afford nothing more than 10000 (see users init balance) #8 #9 ';
    "); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `product`
      CHANGE `owner_id` `owner_id` int(11) NOT NULL,
      CHANGE `cost` `cost` int(11) NOT NULL;
    "); 
	}
}
