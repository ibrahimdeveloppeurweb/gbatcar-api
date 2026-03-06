<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306110817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE general_setting ADD create_by_id INT DEFAULT NULL, ADD update_by_id INT DEFAULT NULL, ADD remove_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE general_setting ADD CONSTRAINT FK_EE5415EC9E085865 FOREIGN KEY (create_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE general_setting ADD CONSTRAINT FK_EE5415ECCA83C286 FOREIGN KEY (update_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE general_setting ADD CONSTRAINT FK_EE5415EC1748B4A5 FOREIGN KEY (remove_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE5415ECD17F50A6 ON general_setting (uuid)');
        $this->addSql('CREATE INDEX IDX_EE5415EC9E085865 ON general_setting (create_by_id)');
        $this->addSql('CREATE INDEX IDX_EE5415ECCA83C286 ON general_setting (update_by_id)');
        $this->addSql('CREATE INDEX IDX_EE5415EC1748B4A5 ON general_setting (remove_by_id)');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE user CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE general_setting DROP FOREIGN KEY FK_EE5415EC9E085865');
        $this->addSql('ALTER TABLE general_setting DROP FOREIGN KEY FK_EE5415ECCA83C286');
        $this->addSql('ALTER TABLE general_setting DROP FOREIGN KEY FK_EE5415EC1748B4A5');
        $this->addSql('DROP INDEX UNIQ_EE5415ECD17F50A6 ON general_setting');
        $this->addSql('DROP INDEX IDX_EE5415EC9E085865 ON general_setting');
        $this->addSql('DROP INDEX IDX_EE5415ECCA83C286 ON general_setting');
        $this->addSql('DROP INDEX IDX_EE5415EC1748B4A5 ON general_setting');
        $this->addSql('ALTER TABLE general_setting DROP create_by_id, DROP update_by_id, DROP remove_by_id, DROP deleted_at, DROP uuid, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE uuid uuid BINARY(16) NOT NULL');
    }
}
