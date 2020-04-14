<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020040713311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#49 Move v2 tables outside.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        // #49 https://github.com/Janis-Rullis-IT/sql/blob/master/mysql/Rename-table.md
        $this->addSql('RENAME TABLE `v2_shipping_rate` TO `shipping_rate`;');
        $this->addSql('RENAME TABLE `v2_order_product` TO `order_product`;');
        $this->addSql('RENAME TABLE `v2_order` TO `order`;');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('RENAME TABLE `shipping_rate` TO `v2_shipping_rate`;');
        $this->addSql('RENAME TABLE `order_product` TO `v2_order_product`;');
        $this->addSql('RENAME TABLE `order` TO `v2_order`;');
    }
}
