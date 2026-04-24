<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424102740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE calendar_event CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract_duration CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE general_setting CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE general_setting_history CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_alert CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_budget CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_provider CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE maintenance_type CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE payment CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE payment_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE payment_schedule CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE penalty CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE promise_to_pay CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE subscription ADD rejection_reason LONGTEXT DEFAULT NULL, CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE user CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_model CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE brand CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE calendar_event CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract_document CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract_duration CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE file CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE folder CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE general_setting CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE general_setting_history CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_alert CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_budget CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_document CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_provider CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE maintenance_type CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE notification_setting CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE path CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE payment_document CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE payment_schedule CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE penalty CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE promise_to_pay CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE refresh_token CHANGE id id BINARY(16) NOT NULL, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE role CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE subscription DROP rejection_reason, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_model CHANGE uuid uuid BINARY(16) NOT NULL');
    }
}
