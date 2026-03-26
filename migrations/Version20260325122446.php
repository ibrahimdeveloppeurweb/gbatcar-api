<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325122446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_vehicle_demand (id INT AUTO_INCREMENT NOT NULL, contract_id INT NOT NULL, brand_id INT DEFAULT NULL, vehicle_model_id INT DEFAULT NULL, quantity INT DEFAULT NULL, INDEX IDX_63DA79C72576E0FD (contract_id), INDEX IDX_63DA79C744F5D008 (brand_id), INDEX IDX_63DA79C7A467B873 (vehicle_model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_vehicle_demand ADD CONSTRAINT FK_63DA79C72576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE contract_vehicle_demand ADD CONSTRAINT FK_63DA79C744F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE contract_vehicle_demand ADD CONSTRAINT FK_63DA79C7A467B873 FOREIGN KEY (vehicle_model_id) REFERENCES vehicle_model (id)');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract CHANGE vehicle_id vehicle_id INT DEFAULT NULL, CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
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
        $this->addSql('ALTER TABLE vehicle_model CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract_vehicle_demand DROP FOREIGN KEY FK_63DA79C72576E0FD');
        $this->addSql('ALTER TABLE contract_vehicle_demand DROP FOREIGN KEY FK_63DA79C744F5D008');
        $this->addSql('ALTER TABLE contract_vehicle_demand DROP FOREIGN KEY FK_63DA79C7A467B873');
        $this->addSql('DROP TABLE contract_vehicle_demand');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract CHANGE vehicle_id vehicle_id INT NOT NULL, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract_document CHANGE uuid uuid BINARY(16) NOT NULL');
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
        $this->addSql('ALTER TABLE vehicle_model CHANGE uuid uuid BINARY(16) NOT NULL');
    }
}
