<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041114311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#57 Create a procedure `generate_products()`.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // #57 https://www.sgalinski.de/en/typo3-agency/technology/how-to-work-with-doctrine-migrations-in-symfony/
        $this->addSql("
			DROP PROCEDURE IF EXISTS generate_products;
			CREATE PROCEDURE generate_products()
			BEGIN
				DECLARE i INT DEFAULT 0;
				DECLARE j INT DEFAULT 0;

				# #57 Temporily remove indexes to avoid constraints and slowdown.
				DROP INDEX `sku` ON `product`;
				DROP INDEX `owner_id` ON `product`;

				WHILE i < 10 DO
				INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) VALUES (1, 'mug', 'Mug', i, '100');
				INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) VALUES (1, 't-shirt', 't-shirt', i + 100, '100');
					SET i = i + 1;
				END WHILE;

				WHILE j < 16  DO
					INSERT INTO `product` (`owner_id`, `type`, `title`, `sku`, `cost`) SELECT `owner_id` * 2, `type`, `title`, SHA1(CONCAT(`sku`, '-', `id`, '-',j)), `cost` FROM `product`;
					SET j = j + 1;
				END WHILE;

				# #57 Return indexes.
				ALTER TABLE `product`
				ADD UNIQUE KEY `sku` (`sku`),
				ADD KEY `owner_id` (`owner_id`);
			END;
			");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP PROCEDURE IF EXISTS generate_products;');
    }
}
