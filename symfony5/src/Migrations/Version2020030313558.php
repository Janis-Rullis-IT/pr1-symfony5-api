<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030313558 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#22 #11 `order`: Add indexes to find items by owner faster.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // #22 #11`INDEX` https://github.com/janis-rullis/sql/blob/364c6b5d76e1dd3a5ad958828eb73c9d77080fee/mysql/String/Unique-texts.md
    $this->addSql("ALTER TABLE `order`
      ADD INDEX `owner_id` (`owner_id`);
    "); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `order`
      DROP INDEX `owner_id`;
    "); 
	}
}
