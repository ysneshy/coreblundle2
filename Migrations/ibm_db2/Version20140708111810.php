<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/08 11:18:14
 */
class Version20140708111810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_field_facet_value (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                user_id INTEGER NOT NULL, 
                stringValue VARCHAR(255) DEFAULT NULL, 
                floatValue DOUBLE PRECISION DEFAULT NULL, 
                dateValue TIMESTAMP(0) DEFAULT NULL, 
                fieldFacet_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0AA76ED395 ON claro_field_facet_value (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_35307C0A9F9239AF ON claro_field_facet_value (fieldFacet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event_event_category (
                event_id INTEGER NOT NULL, 
                eventcategory_id INTEGER NOT NULL, 
                PRIMARY KEY(event_id, eventcategory_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C71F7E88B ON claro_event_event_category (event_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_858F0D4C29E3B4B5 ON claro_event_event_category (eventcategory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_facet (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                \"position\" INTEGER NOT NULL, 
                isVisibleByOwner SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_DCBA6D3A5E237E06 ON claro_facet (name)
        ");
        $this->addSql("
            CREATE TABLE claro_facet_role (
                facet_id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                PRIMARY KEY(facet_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DFC889F24 ON claro_facet_role (facet_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CDD5845DD60322AC ON claro_facet_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet_role (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                role_id INTEGER NOT NULL, 
                canOpen SMALLINT NOT NULL, 
                canEdit SMALLINT NOT NULL, 
                fieldFacet_id INTEGER NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A52D60322AC ON claro_field_facet_role (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_12F52A529F9239AF ON claro_field_facet_role (fieldFacet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                role_id INTEGER NOT NULL, 
                baseData SMALLINT NOT NULL, 
                mail SMALLINT NOT NULL, 
                phone SMALLINT NOT NULL, 
                sendMail SMALLINT NOT NULL, 
                sendMessage SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_field_facet (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                facet_id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                \"type\" INTEGER NOT NULL, 
                \"position\" INTEGER NOT NULL, 
                isVisibleByOwner SMALLINT NOT NULL, 
                isEditableByOwner SMALLINT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F6C21DB2FC889F24 ON claro_field_facet (facet_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER DEFAULT NULL, 
                log_id INTEGER DEFAULT NULL, 
                evaluation_date TIMESTAMP(0) DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB(1M) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_parameters (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                activity_id INTEGER DEFAULT NULL, 
                max_duration INTEGER DEFAULT NULL, 
                max_attempts INTEGER DEFAULT NULL, 
                who VARCHAR(255) DEFAULT NULL, 
                activity_where VARCHAR(255) DEFAULT NULL, 
                with_tutor SMALLINT DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E2EE25E281C06096 ON claro_activity_parameters (activity_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_secondary_resources (
                activityparameters_id INTEGER NOT NULL, 
                resourcenode_id INTEGER NOT NULL, 
                PRIMARY KEY(
                    activityparameters_id, resourcenode_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A7DB5E3CF7 ON claro_activity_secondary_resources (activityparameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_713242A777C292AE ON claro_activity_secondary_resources (resourcenode_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                user_id INTEGER NOT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                log_id INTEGER DEFAULT NULL, 
                lastest_evaluation_date TIMESTAMP(0) DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB(1M) DEFAULT NULL, 
                total_duration INTEGER DEFAULT NULL, 
                attempts_count INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule_action (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                resource_type_id INTEGER DEFAULT NULL, 
                log_action VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8835D2098EC6B7B ON claro_activity_rule_action (resource_type_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX activity_rule_unique_action_resource_type ON claro_activity_rule_action (log_action, resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity_rule (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                resource_id INTEGER DEFAULT NULL, 
                badge_id INTEGER DEFAULT NULL, 
                result_visible SMALLINT DEFAULT NULL, 
                occurrence SMALLINT NOT NULL, 
                action VARCHAR(255) NOT NULL, 
                \"result\" VARCHAR(255) DEFAULT NULL, 
                resultMax VARCHAR(255) DEFAULT NULL, 
                resultComparison SMALLINT DEFAULT NULL, 
                userType SMALLINT NOT NULL, 
                active_from TIMESTAMP(0) DEFAULT NULL, 
                active_until TIMESTAMP(0) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E896F55DB ON claro_activity_rule (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65E89329D25 ON claro_activity_rule (resource_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6824A65EF7A2C2FC ON claro_activity_rule (badge_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event_category (
                id INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_408DC8C05E237E06 ON claro_event_category (name)
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0AA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            ADD CONSTRAINT FK_35307C0A9F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C71F7E88B FOREIGN KEY (event_id) 
            REFERENCES claro_event (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            ADD CONSTRAINT FK_858F0D4C29E3B4B5 FOREIGN KEY (eventcategory_id) 
            REFERENCES claro_event_category (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DFC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_facet_role 
            ADD CONSTRAINT FK_CDD5845DD60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A52D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            ADD CONSTRAINT FK_12F52A529F9239AF FOREIGN KEY (fieldFacet_id) 
            REFERENCES claro_field_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_general_facet_preference 
            ADD CONSTRAINT FK_38AACF88D60322AC FOREIGN KEY (role_id) 
            REFERENCES claro_role (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            ADD CONSTRAINT FK_F6C21DB2FC889F24 FOREIGN KEY (facet_id) 
            REFERENCES claro_facet (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_parameters 
            ADD CONSTRAINT FK_E2EE25E281C06096 FOREIGN KEY (activity_id) 
            REFERENCES claro_activity (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A7DB5E3CF7 FOREIGN KEY (activityparameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            ADD CONSTRAINT FK_713242A777C292AE FOREIGN KEY (resourcenode_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule_action 
            ADD CONSTRAINT FK_C8835D2098EC6B7B FOREIGN KEY (resource_type_id) 
            REFERENCES claro_resource_type (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E896F55DB FOREIGN KEY (activity_parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65E89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            ADD CONSTRAINT FK_6824A65EF7A2C2FC FOREIGN KEY (badge_id) 
            REFERENCES claro_badge (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            ADD COLUMN accessible_from TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN accessible_until TIMESTAMP(0) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP COLUMN parent_id 
            DROP COLUMN discr 
            DROP COLUMN lft 
            DROP COLUMN lvl 
            DROP COLUMN rgt 
            DROP COLUMN root
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD COLUMN workspace_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            ADD CONSTRAINT FK_C8EFD7EF82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EF82D40A1F ON claro_workspace_tag (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD COLUMN resultMax VARCHAR(255) DEFAULT NULL 
            ADD COLUMN active_from TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN active_until TIMESTAMP(0) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD COLUMN parameters_id INTEGER DEFAULT NULL 
            ADD COLUMN title VARCHAR(255) DEFAULT NULL 
            ADD COLUMN primaryResource_id INTEGER DEFAULT NULL 
            DROP COLUMN start_date 
            DROP COLUMN end_date RENAME instruction TO description
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC52410EEC FOREIGN KEY (primaryResource_id) 
            REFERENCES claro_resource_node (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD CONSTRAINT FK_E4A67CAC88BD9C1F FOREIGN KEY (parameters_id) 
            REFERENCES claro_activity_parameters (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_E4A67CAC52410EEC ON claro_activity (primaryResource_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CAC88BD9C1F ON claro_activity (parameters_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_facet_role 
            DROP FOREIGN KEY FK_CDD5845DFC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet 
            DROP FOREIGN KEY FK_F6C21DB2FC889F24
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_value 
            DROP FOREIGN KEY FK_35307C0A9F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_field_facet_role 
            DROP FOREIGN KEY FK_12F52A529F9239AF
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            DROP FOREIGN KEY FK_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_secondary_resources 
            DROP FOREIGN KEY FK_713242A7DB5E3CF7
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_activity_rule 
            DROP FOREIGN KEY FK_6824A65E896F55DB
        ");
        $this->addSql("
            ALTER TABLE claro_event_event_category 
            DROP FOREIGN KEY FK_858F0D4C29E3B4B5
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_value
        ");
        $this->addSql("
            DROP TABLE claro_event_event_category
        ");
        $this->addSql("
            DROP TABLE claro_facet
        ");
        $this->addSql("
            DROP TABLE claro_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_field_facet_role
        ");
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            DROP TABLE claro_field_facet
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_parameters
        ");
        $this->addSql("
            DROP TABLE claro_activity_secondary_resources
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule_action
        ");
        $this->addSql("
            DROP TABLE claro_activity_rule
        ");
        $this->addSql("
            DROP TABLE claro_event_category
        ");
        $this->addSql("
            ALTER TABLE claro_activity 
            ADD COLUMN start_date TIMESTAMP(0) DEFAULT NULL 
            ADD COLUMN end_date TIMESTAMP(0) DEFAULT NULL 
            DROP COLUMN parameters_id 
            DROP COLUMN title 
            DROP COLUMN primaryResource_id RENAME description TO instruction
        ");
        $this->addSql("
            DROP INDEX IDX_E4A67CAC52410EEC
        ");
        $this->addSql("
            DROP INDEX UNIQ_E4A67CAC88BD9C1F
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP COLUMN resultMax 
            DROP COLUMN active_from 
            DROP COLUMN active_until
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            DROP FOREIGN KEY FK_805FCB8F89329D25
        ");
        $this->addSql("
            ALTER TABLE claro_badge_rule 
            ADD CONSTRAINT FK_805FCB8F89329D25 FOREIGN KEY (resource_id) 
            REFERENCES claro_resource_node (id)
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node 
            DROP COLUMN accessible_from 
            DROP COLUMN accessible_until
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD COLUMN parent_id INTEGER DEFAULT NULL 
            ADD COLUMN discr VARCHAR(255) NOT NULL 
            ADD COLUMN lft INTEGER DEFAULT NULL 
            ADD COLUMN lvl INTEGER DEFAULT NULL 
            ADD COLUMN rgt INTEGER DEFAULT NULL 
            ADD COLUMN root INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP COLUMN workspace_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag 
            DROP FOREIGN KEY FK_C8EFD7EF82D40A1F
        ");
        $this->addSql("
            DROP INDEX IDX_C8EFD7EF82D40A1F
        ");
    }
}