<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030313557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#22 #11 `order`: Make integer field UNSIGNED because this will allow more POSITIVE values.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        // #22 #11 `CHANGE` https://github.com/janis-rullis/sql/blob/master/mysql/basics/12_managing_databases_and_tables.md#tables
        $this->addSql('ALTER TABLE `order`
      CHANGE `owner_id` `owner_id` int(11) UNSIGNED NOT NULL,
      CHANGE `express_shipping` `express_shipping` tinyint(1) UNSIGNED DEFAULT NULL,
      CHANGE `production_cost` `production_cost` int(11) UNSIGNED NOT NULL,
      CHANGE `shipping_cost` `shipping_cost` int(11) UNSIGNED NOT NULL,
      CHANGE `total_cost` `total_cost` int(11) UNSIGNED NOT NULL;    
    ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `order`
      CHANGE `owner_id` `owner_id` int(11) NOT NULL,
      CHANGE `express_shipping` `express_shipping` tinyint(1) DEFAULT NULL,
      CHANGE `production_cost` `production_cost` int(11) NOT NULL,
      CHANGE `shipping_cost` `shipping_cost` int(11) NOT NULL,
      CHANGE `total_cost` `total_cost` int(11) NOT NULL;    
    ');
    }
}
