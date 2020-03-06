<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030611510 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#34 Change `order_product`.`id` type from TINYINT to INT.";
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    // https://github.com/janis-rullis/pr1/issues/33#issuecomment-595102860 #33 #34 #10 #8 #9'
    $this->addSql("
      ALTER TABLE `v2_order_product`
      CHANGE `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
    "); 
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("
      ALTER TABLE `v2_order_product`
      CHANGE `id` `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
    "); 
	}
}
