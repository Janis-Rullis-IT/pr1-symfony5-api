<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030313532 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#22 `user`: Decrease the max `balance` value from INT to SMALLINT with init value 10000.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // #22 #8 `CHANGE` https://github.com/janis-rullis/sql/blob/master/mysql/basics/12_managing_databases_and_tables.md#tables
    // #22 #8 `smallint(5)` https://github.com/janis-rullis/sql/blob/master/mysql/Number/Int-max-value.md    
    $this->addSql("ALTER TABLE `user`
    CHANGE `balance` `balance` smallint(5) unsigned NOT NULL DEFAULT '10000' COMMENT 'All users upon creation have $100 in their balance. #8';"); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `user`
    CHANGE `balance` `balance` int(11) NOT NULL;");		
	}
}
