<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020041114341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#57 Create a procedure `generate_data()`.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // #57 https://www.sgalinski.de/en/typo3-agency/technology/how-to-work-with-doctrine-migrations-in-symfony/
        $this->addSql('
			# #57 Takes ~111s.
			DROP PROCEDURE IF EXISTS generate_data;
			CREATE PROCEDURE generate_data()
			BEGIN
				CALL generate_users();
				CALL generate_products();
				CALL generate_orders();
				CALL generate_order_products();
			END;
			');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP PROCEDURE IF EXISTS generate_data;');
    }
}
