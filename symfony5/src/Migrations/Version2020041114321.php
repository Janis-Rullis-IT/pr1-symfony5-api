<?php
declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041114321 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#57 Create a procedure `generate_orders()`.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		// #57 https://www.sgalinski.de/en/typo3-agency/technology/how-to-work-with-doctrine-migrations-in-symfony/
		$this->addSql("
			# #57 Generate 2618646 orders in 20s.
			DROP PROCEDURE IF EXISTS generate_orders;
			CREATE PROCEDURE generate_orders()
			BEGIN
				DECLARE i INT DEFAULT 0;
				DECLARE j INT DEFAULT 0;

				WHILE i < 10 DO
					INSERT INTO `order` (`status`, `customer_id`) VALUES('completed',  i + 1);
					SET i = i + 1;
				END WHILE;

				WHILE j < 18  DO
					INSERT INTO `order` (`status`, `customer_id`) SELECT `status`, `customer_id` * 2 FROM `order`;
					SET j = j + 1;
				END WHILE;
			END;
			");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DROP PROCEDURE IF EXISTS generate_orders;");
	}
}
