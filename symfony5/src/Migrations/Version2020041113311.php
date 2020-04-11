<?php
declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041113311 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#57 Create a procedure `generate_users()`.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		// #57 https://www.sgalinski.de/en/typo3-agency/technology/how-to-work-with-doctrine-migrations-in-symfony/
		$this->addSql("
			DROP PROCEDURE IF EXISTS generate_users;
			CREATE PROCEDURE generate_users()
			BEGIN
				DECLARE i INT DEFAULT 0;
				DECLARE j INT DEFAULT 0;

				# #57 Generate ~3m users in ~15s.
				WHILE i < 10 DO
					INSERT INTO user (`name`, `surname`) VALUES('John',  'Doe');
					SET i = i + 1;
				END WHILE;

				WHILE j < 18  DO
					INSERT INTO `user` (`name`, `surname`, `balance`) SELECT `name`, `surname`, `balance` FROM `user`;
					SET j = j + 1; 
				END WHILE;
			END;
			");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DROP PROCEDURE IF EXISTS generate_users;");
	}
}
