<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325131959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_vehicle_demand_vehicle (contract_vehicle_demand_id INT NOT NULL, vehicle_id INT NOT NULL, INDEX IDX_B6CECF96A5ABCE3 (contract_vehicle_demand_id), INDEX IDX_B6CECF9545317D1 (vehicle_id), PRIMARY KEY(contract_vehicle_demand_id, vehicle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_vehicle_demand_vehicle ADD CONSTRAINT FK_B6CECF96A5ABCE3 FOREIGN KEY (contract_vehicle_demand_id) REFERENCES contract_vehicle_demand (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_vehicle_demand_vehicle ADD CONSTRAINT FK_B6CECF9545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
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
        $this->addSql('ALTER TABLE contract_vehicle_demand_vehicle DROP FOREIGN KEY FK_B6CECF96A5ABCE3');
        $this->addSql('ALTER TABLE contract_vehicle_demand_vehicle DROP FOREIGN KEY FK_B6CECF9545317D1');
        $this->addSql('DROP TABLE contract_vehicle_demand_vehicle');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL');
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
