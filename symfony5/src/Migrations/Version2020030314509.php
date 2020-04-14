<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020030314509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "#22 #9 `product`: Add indexes to find user's products faster.";
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        // #22 #9 `INDEX` https://github.com/janis-rullis/sql/blob/364c6b5d76e1dd3a5ad958828eb73c9d77080fee/mysql/String/Unique-texts.md
        $this->addSql('ALTER TABLE `product`
      ADD INDEX `owner_id` (`owner_id`),
      ADD UNIQUE INDEX `sku` (`sku`);
    ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `product`
      DROP INDEX `owner_id`;
    ');
        $this->addSql('ALTER TABLE `product`
    DROP INDEX `sku`;
  ');
    }
}
