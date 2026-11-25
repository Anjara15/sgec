<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102193350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE courrier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, type VARCHAR(20) NOT NULL, objet VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date_document DATE DEFAULT NULL, date_enregistrement DATETIME NOT NULL, expediteur VARCHAR(255) NOT NULL, destinataire VARCHAR(255) NOT NULL, priorite VARCHAR(20) NOT NULL, confidentialite VARCHAR(30) NOT NULL, statut VARCHAR(30) NOT NULL, date_modification DATETIME DEFAULT NULL, date_archivage DATETIME DEFAULT NULL, motif_validation CLOB DEFAULT NULL, service_origine_id INTEGER DEFAULT NULL, service_destinataire_id INTEGER DEFAULT NULL, createur_id INTEGER NOT NULL, agent_responsable_id INTEGER DEFAULT NULL, CONSTRAINT FK_BEF47CAA96D6A03A FOREIGN KEY (service_origine_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEF47CAA86F93F8A FOREIGN KEY (service_destinataire_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEF47CAA73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEF47CAA6CC7B0A FOREIGN KEY (agent_responsable_id) REFERENCES utilisateur (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_COURRIER_REF ON courrier (reference)');
        $this->addSql('CREATE INDEX IDX_BEF47CAA96D6A03A ON courrier (service_origine_id)');
        $this->addSql('CREATE INDEX IDX_BEF47CAA86F93F8A ON courrier (service_destinataire_id)');
        $this->addSql('CREATE INDEX IDX_BEF47CAA73A201E5 ON courrier (createur_id)');
        $this->addSql('CREATE INDEX IDX_BEF47CAA6CC7B0A ON courrier (agent_responsable_id)');
        $this->addSql('CREATE TABLE historique_statut (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ancien_statut VARCHAR(30) NOT NULL, nouveau_statut VARCHAR(30) NOT NULL, date_changement DATETIME NOT NULL, motif CLOB DEFAULT NULL, courrier_id INTEGER NOT NULL, auteur_id INTEGER NOT NULL, CONSTRAINT FK_2C2650E38BF41DC7 FOREIGN KEY (courrier_id) REFERENCES courrier (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2C2650E360BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2C2650E38BF41DC7 ON historique_statut (courrier_id)');
        $this->addSql('CREATE INDEX IDX_2C2650E360BB6FE6 ON historique_statut (auteur_id)');
        $this->addSql('CREATE TABLE journal_activite (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "action" VARCHAR(150) NOT NULL, type_objet VARCHAR(100) NOT NULL, objet_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, infos CLOB NOT NULL, utilisateur_id INTEGER DEFAULT NULL, CONSTRAINT FK_68C5F8ACFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_68C5F8ACFB88E14F ON journal_activite (utilisateur_id)');
        $this->addSql('CREATE TABLE notification (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type_evenement VARCHAR(100) NOT NULL, message CLOB NOT NULL, date_creation DATETIME NOT NULL, date_lecture DATETIME DEFAULT NULL, destinataire_id INTEGER NOT NULL, lien_courrier_id INTEGER DEFAULT NULL, CONSTRAINT FK_BF5476CAA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF5476CA1463196A FOREIGN KEY (lien_courrier_id) REFERENCES courrier (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA4F84F6E ON notification (destinataire_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA1463196A ON notification (lien_courrier_id)');
        $this->addSql('CREATE TABLE parametre_systeme (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cle VARCHAR(100) NOT NULL, valeur CLOB NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PARAM_CLE ON parametre_systeme (cle)');
        $this->addSql('CREATE TABLE piece_jointe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom_original VARCHAR(255) NOT NULL, nom_stockage VARCHAR(255) NOT NULL, type_mime VARCHAR(150) NOT NULL, taille INTEGER NOT NULL, date_ajout DATETIME NOT NULL, utilisateur_id INTEGER NOT NULL, courrier_id INTEGER NOT NULL, CONSTRAINT FK_AB5111D4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AB5111D48BF41DC7 FOREIGN KEY (courrier_id) REFERENCES courrier (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AB5111D4FB88E14F ON piece_jointe (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_AB5111D48BF41DC7 ON piece_jointe (courrier_id)');
        $this->addSql('CREATE TABLE role (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description CLOB DEFAULT NULL, permissions CLOB NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A6C6E55B5 ON role (nom)');
        $this->addSql('CREATE TABLE service (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, description CLOB DEFAULT NULL, statut VARCHAR(20) NOT NULL, responsable_id INTEGER DEFAULT NULL, CONSTRAINT FK_E19D9AD253C59D72 FOREIGN KEY (responsable_id) REFERENCES utilisateur (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E19D9AD253C59D72 ON service (responsable_id)');
        $this->addSql('CREATE TABLE transmission (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_transmission DATETIME NOT NULL, date_reception DATETIME DEFAULT NULL, commentaire CLOB DEFAULT NULL, etat VARCHAR(20) NOT NULL, courrier_id INTEGER NOT NULL, expediteur_id INTEGER NOT NULL, destinataire_id INTEGER NOT NULL, service_origine_id INTEGER DEFAULT NULL, service_destinataire_id INTEGER DEFAULT NULL, CONSTRAINT FK_7F87199F8BF41DC7 FOREIGN KEY (courrier_id) REFERENCES courrier (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7F87199F10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7F87199FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7F87199F96D6A03A FOREIGN KEY (service_origine_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7F87199F86F93F8A FOREIGN KEY (service_destinataire_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7F87199F8BF41DC7 ON transmission (courrier_id)');
        $this->addSql('CREATE INDEX IDX_7F87199F10335F61 ON transmission (expediteur_id)');
        $this->addSql('CREATE INDEX IDX_7F87199FA4F84F6E ON transmission (destinataire_id)');
        $this->addSql('CREATE INDEX IDX_7F87199F96D6A03A ON transmission (service_origine_id)');
        $this->addSql('CREATE INDEX IDX_7F87199F86F93F8A ON transmission (service_destinataire_id)');
        $this->addSql('CREATE TABLE utilisateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, service_id INTEGER DEFAULT NULL, CONSTRAINT FK_1D1C63B3ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_EMAIL ON utilisateur (email)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3ED5CA9E6 ON utilisateur (service_id)');
        $this->addSql('CREATE TABLE user_role (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY (user_id, role_id), CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3A76ED395 ON user_role (user_id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3D60322AC ON user_role (role_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE courrier');
        $this->addSql('DROP TABLE historique_statut');
        $this->addSql('DROP TABLE journal_activite');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE parametre_systeme');
        $this->addSql('DROP TABLE piece_jointe');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE transmission');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
