<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020031713311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#40 Allow null address fields to `v2_order`.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
				ALTER TABLE `v2_order`
				CHANGE `name` `name` varchar(30) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
				CHANGE `surname` `surname` varchar(30) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
				CHANGE `street` `street` varchar(50) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
				CHANGE `country` `country` varchar(40) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
				CHANGE `phone` `phone` varchar(30) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;				
		');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
			ALTER TABLE `v2_order`
			CHANGE `name` `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			CHANGE `surname` `surname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
			CHANGE `street` `street` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
			CHANGE `country` `country` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
			CHANGE `phone` `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL;				
		');
    }
}
