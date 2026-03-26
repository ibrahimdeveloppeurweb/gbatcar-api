<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323120341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_document (id INT AUTO_INCREMENT NOT NULL, contract_id INT NOT NULL, create_by_id INT DEFAULT NULL, update_by_id INT DEFAULT NULL, remove_by_id INT DEFAULT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, size INT DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4DDE9189D17F50A6 (uuid), INDEX IDX_4DDE91892576E0FD (contract_id), INDEX IDX_4DDE91899E085865 (create_by_id), INDEX IDX_4DDE9189CA83C286 (update_by_id), INDEX IDX_4DDE91891748B4A5 (remove_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_document ADD CONSTRAINT FK_4DDE91892576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_document ADD CONSTRAINT FK_4DDE91899E085865 FOREIGN KEY (create_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE contract_document ADD CONSTRAINT FK_4DDE9189CA83C286 FOREIGN KEY (update_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE contract_document ADD CONSTRAINT FK_4DDE91891748B4A5 FOREIGN KEY (remove_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE general_setting CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE general_setting_history CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_alert CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE payment CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE penalty CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE user CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract_document DROP FOREIGN KEY FK_4DDE91892576E0FD');
        $this->addSql('ALTER TABLE contract_document DROP FOREIGN KEY FK_4DDE91899E085865');
        $this->addSql('ALTER TABLE contract_document DROP FOREIGN KEY FK_4DDE9189CA83C286');
        $this->addSql('ALTER TABLE contract_document DROP FOREIGN KEY FK_4DDE91891748B4A5');
        $this->addSql('DROP TABLE contract_document');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE general_setting CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE general_setting_history CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_alert CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_document CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE penalty CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL');
    }
}
