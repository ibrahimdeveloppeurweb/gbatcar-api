<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325120811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, create_by_id INT DEFAULT NULL, update_by_id INT DEFAULT NULL, remove_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1C52F958D17F50A6 (uuid), INDEX IDX_1C52F9589E085865 (create_by_id), INDEX IDX_1C52F958CA83C286 (update_by_id), INDEX IDX_1C52F9581748B4A5 (remove_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_model (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, create_by_id INT DEFAULT NULL, update_by_id INT DEFAULT NULL, remove_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\', created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B53AF235D17F50A6 (uuid), INDEX IDX_B53AF23544F5D008 (brand_id), INDEX IDX_B53AF2359E085865 (create_by_id), INDEX IDX_B53AF235CA83C286 (update_by_id), INDEX IDX_B53AF2351748B4A5 (remove_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F9589E085865 FOREIGN KEY (create_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958CA83C286 FOREIGN KEY (update_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F9581748B4A5 FOREIGN KEY (remove_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vehicle_model ADD CONSTRAINT FK_B53AF23544F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE vehicle_model ADD CONSTRAINT FK_B53AF2359E085865 FOREIGN KEY (create_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vehicle_model ADD CONSTRAINT FK_B53AF235CA83C286 FOREIGN KEY (update_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vehicle_model ADD CONSTRAINT FK_B53AF2351748B4A5 FOREIGN KEY (remove_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE contract DROP paid_amount, CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
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
        $this->addSql('ALTER TABLE vehicle ADD brand_id INT DEFAULT NULL, ADD vehicle_model_id INT DEFAULT NULL, CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E48644F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486A467B873 FOREIGN KEY (vehicle_model_id) REFERENCES vehicle_model (id)');
        $this->addSql('CREATE INDEX IDX_1B80E48644F5D008 ON vehicle (brand_id)');
        $this->addSql('CREATE INDEX IDX_1B80E486A467B873 ON vehicle (vehicle_model_id)');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E48644F5D008');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486A467B873');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F9589E085865');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958CA83C286');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F9581748B4A5');
        $this->addSql('ALTER TABLE vehicle_model DROP FOREIGN KEY FK_B53AF23544F5D008');
        $this->addSql('ALTER TABLE vehicle_model DROP FOREIGN KEY FK_B53AF2359E085865');
        $this->addSql('ALTER TABLE vehicle_model DROP FOREIGN KEY FK_B53AF235CA83C286');
        $this->addSql('ALTER TABLE vehicle_model DROP FOREIGN KEY FK_B53AF2351748B4A5');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE vehicle_model');
        $this->addSql('ALTER TABLE `admin` CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE client CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE contract ADD paid_amount DOUBLE PRECISION DEFAULT NULL, CHANGE uuid uuid BINARY(16) NOT NULL');
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
        $this->addSql('DROP INDEX IDX_1B80E48644F5D008 ON vehicle');
        $this->addSql('DROP INDEX IDX_1B80E486A467B873 ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP brand_id, DROP vehicle_model_id, CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance CHANGE uuid uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE vehicle_compliance_document CHANGE uuid uuid BINARY(16) NOT NULL');
    }
}
