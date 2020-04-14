<?php
/**
 * #6 Import base tables one by one (so could rollback back one-by-one).
 */
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020022717531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the `relation` table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
			CREATE TABLE IF NOT EXISTS `relation` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`owner_id` int(11) NOT NULL,
			`order_id` int(11) NOT NULL,
			`product_id` int(11) NOT NULL,
			`quantity` int(11) NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE `relation`');
    }
}
