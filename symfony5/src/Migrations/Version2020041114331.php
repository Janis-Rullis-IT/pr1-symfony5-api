<?php
declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041114331 extends AbstractMigration
{

	public function getDescription(): string
	{
		return "#57 Create a procedure `generate_order_products()`.";
	}

	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		// #57 https://www.sgalinski.de/en/typo3-agency/technology/how-to-work-with-doctrine-migrations-in-symfony/
		$this->addSql("
			# #57 1310720 records in 9s.
			DROP PROCEDURE IF EXISTS generate_order_products;
			CREATE PROCEDURE generate_order_products()
			BEGIN
				DECLARE i INT DEFAULT 0;
				DECLARE j INT DEFAULT 0;

				WHILE i < 10 DO
				INSERT INTO `order_product` (`order_id`, `customer_id`, `seller_id`, `product_id`) VALUES (i + 1, i + 1, i + 2, i + 1);
					SET i = i + 1;
				END WHILE;

				WHILE j < 17  DO
					INSERT INTO `order_product` (`order_id`, `customer_id`, `seller_id`, `product_id`) SELECT `order_id` * 2, `customer_id` * 2, `seller_id` * 2, `product_id` * 2 FROM `order_product`;
					SET j = j + 1;
				END WHILE;
			END;
			");
	}

	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("DROP PROCEDURE IF EXISTS generate_order_products;");
	}
}
