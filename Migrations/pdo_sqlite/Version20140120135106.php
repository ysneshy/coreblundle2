<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/01/20 01:51:08
 */
class Version20140120135106 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D48CC23E7D08FA9E
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget_home_tab_config AS 
            SELECT id, 
            widget_instance_id, 
            home_tab_id, 
            workspace_id, 
            user_id, 
            widget_order, 
            type, 
            is_visible, 
            is_locked 
            FROM claro_widget_home_tab_config
        ");
        $this->addSql("
            DROP TABLE claro_widget_home_tab_config
        ");
        $this->addSql("
            CREATE TABLE claro_widget_home_tab_config (
                id INTEGER NOT NULL, 
                widget_instance_id INTEGER DEFAULT NULL, 
                home_tab_id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                widget_order INTEGER NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23E7D08FA9E FOREIGN KEY (home_tab_id) 
                REFERENCES claro_home_tab (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget_home_tab_config (
                id, widget_instance_id, home_tab_id, 
                workspace_id, user_id, widget_order, 
                type, is_visible, is_locked
            ) 
            SELECT id, 
            widget_instance_id, 
            home_tab_id, 
            workspace_id, 
            user_id, 
            widget_order, 
            type, 
            is_visible, 
            is_locked 
            FROM __temp__claro_widget_home_tab_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_home_tab_config
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E7D08FA9E ON claro_widget_home_tab_config (home_tab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EA76ED395 ON claro_widget_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E82D40A1F ON claro_widget_home_tab_config (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_D48CC23E44BF891
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E7D08FA9E
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23EA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_D48CC23E82D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_widget_home_tab_config AS 
            SELECT id, 
            widget_instance_id, 
            home_tab_id, 
            user_id, 
            workspace_id, 
            widget_order, 
            type, 
            is_visible, 
            is_locked 
            FROM claro_widget_home_tab_config
        ");
        $this->addSql("
            DROP TABLE claro_widget_home_tab_config
        ");
        $this->addSql("
            CREATE TABLE claro_widget_home_tab_config (
                id INTEGER NOT NULL, 
                widget_instance_id INTEGER DEFAULT NULL, 
                home_tab_id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                type VARCHAR(255) NOT NULL, 
                is_visible BOOLEAN NOT NULL, 
                is_locked BOOLEAN NOT NULL, 
                widget_order VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_D48CC23E44BF891 FOREIGN KEY (widget_instance_id) 
                REFERENCES claro_widget_instance (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23E7D08FA9E FOREIGN KEY (home_tab_id) 
                REFERENCES claro_home_tab (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_D48CC23E82D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_widget_home_tab_config (
                id, widget_instance_id, home_tab_id, 
                user_id, workspace_id, widget_order, 
                type, is_visible, is_locked
            ) 
            SELECT id, 
            widget_instance_id, 
            home_tab_id, 
            user_id, 
            workspace_id, 
            widget_order, 
            type, 
            is_visible, 
            is_locked 
            FROM __temp__claro_widget_home_tab_config
        ");
        $this->addSql("
            DROP TABLE __temp__claro_widget_home_tab_config
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E44BF891 ON claro_widget_home_tab_config (widget_instance_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E7D08FA9E ON claro_widget_home_tab_config (home_tab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EA76ED395 ON claro_widget_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23E82D40A1F ON claro_widget_home_tab_config (workspace_id)
        ");
    }
}