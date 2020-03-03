<?php

declare(strict_types = 1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020022817532 extends AbstractMigration
{

	public function getDescription(): string
	{
		return '#24 Fix the `user` table.';
	}

	public function up(Schema $schema): void
	{
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    $this->addSql("DROP TABLE `user`");
		$this->addSql("
      CREATE TABLE `user` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
        `surname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
        `balance` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
		);
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql('DROP TABLE `user`');
	}
}
