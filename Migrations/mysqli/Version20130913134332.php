<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2013/09/13 01:43:33
 */
class Version20130913134332 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                username VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                salt VARCHAR(255) NOT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                mail VARCHAR(255) NOT NULL,
                administrative_code VARCHAR(255) DEFAULT NULL,
                creation_date DATETIME NOT NULL,
                reset_password VARCHAR(255) DEFAULT NULL,
                hash_time INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_EB8D2852F85E0677 (username),
                UNIQUE INDEX UNIQ_EB8D28525126AC48 (mail),
                UNIQUE INDEX UNIQ_EB8D285282D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_group (
                user_id INT NOT NULL,
                group_id INT NOT NULL,
                INDEX IDX_ED8B34C7A76ED395 (user_id),
                INDEX IDX_ED8B34C7FE54D947 (group_id),
                PRIMARY KEY(user_id, group_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_role (
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                INDEX IDX_797E43FFA76ED395 (user_id),
                INDEX IDX_797E43FFD60322AC (role_id),
                PRIMARY KEY(user_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_group (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                UNIQUE INDEX group_unique_name (name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_group_role (
                group_id INT NOT NULL,
                role_id INT NOT NULL,
                INDEX IDX_1CBA5A40FE54D947 (group_id),
                INDEX IDX_1CBA5A40D60322AC (role_id),
                PRIMARY KEY(group_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_role (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                translation_key VARCHAR(255) NOT NULL,
                is_read_only TINYINT(1) NOT NULL,
                type INT NOT NULL,
                UNIQUE INDEX UNIQ_317774715E237E06 (name),
                INDEX IDX_3177747182D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT AUTO_INCREMENT NOT NULL,
                license_id INT DEFAULT NULL,
                resource_type_id INT NOT NULL,
                creator_id INT NOT NULL,
                icon_id INT DEFAULT NULL,
                parent_id INT DEFAULT NULL,
                workspace_id INT NOT NULL,
                next_id INT DEFAULT NULL,
                previous_id INT DEFAULT NULL,
                creation_date DATETIME NOT NULL,
                modification_date DATETIME NOT NULL,
                name VARCHAR(255) NOT NULL,
                lvl INT DEFAULT NULL,
                path VARCHAR(3000) DEFAULT NULL,
                mime_type VARCHAR(255) DEFAULT NULL,
                class VARCHAR(256) NOT NULL,
                INDEX IDX_A76799FF460F904B (license_id),
                INDEX IDX_A76799FF98EC6B7B (resource_type_id),
                INDEX IDX_A76799FF61220EA6 (creator_id),
                INDEX IDX_A76799FF54B9D732 (icon_id),
                INDEX IDX_A76799FF727ACA70 (parent_id),
                INDEX IDX_A76799FF82D40A1F (workspace_id),
                UNIQUE INDEX UNIQ_A76799FFAA23F6C8 (next_id),
                UNIQUE INDEX UNIQ_A76799FF2DE62210 (previous_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                parent_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                is_public TINYINT(1) DEFAULT NULL,
                displayable TINYINT(1) DEFAULT NULL,
                guid VARCHAR(255) NOT NULL,
                self_registration TINYINT(1) DEFAULT NULL,
                self_unregistration TINYINT(1) DEFAULT NULL,
                discr VARCHAR(255) NOT NULL,
                lft INT DEFAULT NULL,
                lvl INT DEFAULT NULL,
                rgt INT DEFAULT NULL,
                root INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_D902854577153098 (code),
                UNIQUE INDEX UNIQ_D90285452B6FCFB2 (guid),
                INDEX IDX_D9028545A76ED395 (user_id),
                INDEX IDX_D9028545727ACA70 (parent_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_aggregation (
                aggregator_workspace_id INT NOT NULL,
                simple_workspace_id INT NOT NULL,
                INDEX IDX_D012AF0FA08DFE7A (aggregator_workspace_id),
                INDEX IDX_D012AF0F782B5A3F (simple_workspace_id),
                PRIMARY KEY(
                    aggregator_workspace_id, simple_workspace_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_message (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                message_id INT NOT NULL,
                is_removed TINYINT(1) NOT NULL,
                is_read TINYINT(1) NOT NULL,
                is_sent TINYINT(1) NOT NULL,
                INDEX IDX_D48EA38AA76ED395 (user_id),
                INDEX IDX_D48EA38A537A1329 (message_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                tool_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                display_order INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                INDEX IDX_6CF1320E82D40A1F (workspace_id),
                INDEX IDX_6CF1320E8F7B22CC (tool_id),
                INDEX IDX_6CF1320EA76ED395 (user_id),
                UNIQUE INDEX ordered_tool_unique_tool_ws_usr (tool_id, workspace_id, user_id),
                UNIQUE INDEX ordered_tool_unique_name_by_workspace (workspace_id, name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool_role (
                orderedtool_id INT NOT NULL,
                role_id INT NOT NULL,
                INDEX IDX_9210497679732467 (orderedtool_id),
                INDEX IDX_92104976D60322AC (role_id),
                PRIMARY KEY(orderedtool_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                badge_id INT NOT NULL,
                issuer_id INT DEFAULT NULL,
                issued_at DATETIME NOT NULL,
                INDEX IDX_7EBB381FA76ED395 (user_id),
                INDEX IDX_7EBB381FF7A2C2FC (badge_id),
                INDEX IDX_7EBB381FBB9D6FEE (issuer_id),
                UNIQUE INDEX user_badge_unique (user_id, badge_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_badge_claim (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                badge_id INT NOT NULL,
                claimed_at DATETIME NOT NULL,
                INDEX IDX_487A496AA76ED395 (user_id),
                INDEX IDX_487A496AF7A2C2FC (badge_id),
                UNIQUE INDEX badge_claim_unique (user_id, badge_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_mask_decoder (
                id INT AUTO_INCREMENT NOT NULL,
                resource_type_id INT NOT NULL,
                value INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                INDEX IDX_39D93F4298EC6B7B (resource_type_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INT AUTO_INCREMENT NOT NULL,
                plugin_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                is_exportable TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_AEC626935E237E06 (name),
                INDEX IDX_AEC62693EC942BCF (plugin_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT AUTO_INCREMENT NOT NULL,
                resource_type_id INT DEFAULT NULL,
                name VARCHAR(255) DEFAULT NULL,
                async TINYINT(1) DEFAULT NULL,
                is_custom TINYINT(1) NOT NULL,
                is_form TINYINT(1) NOT NULL,
                value VARCHAR(255) DEFAULT NULL,
                INDEX IDX_1F57E52B98EC6B7B (resource_type_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INT AUTO_INCREMENT NOT NULL,
                role_id INT NOT NULL,
                mask INT NOT NULL,
                resourceNode_id INT NOT NULL,
                INDEX IDX_3848F483D60322AC (role_id),
                INDEX IDX_3848F483B87FAB32 (resourceNode_id),
                UNIQUE INDEX resource_rights_unique_resource_role (resourceNode_id, role_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_list_type_creation (
                resource_rights_id INT NOT NULL,
                resource_type_id INT NOT NULL,
                INDEX IDX_84B4BEBA195FBDF1 (resource_rights_id),
                INDEX IDX_84B4BEBA98EC6B7B (resource_type_id),
                PRIMARY KEY(
                    resource_rights_id, resource_type_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_event (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                user_id INT NOT NULL,
                title VARCHAR(50) NOT NULL,
                start_date INT DEFAULT NULL,
                end_date INT DEFAULT NULL,
                description VARCHAR(255) DEFAULT NULL,
                allday TINYINT(1) DEFAULT NULL,
                priority VARCHAR(255) DEFAULT NULL,
                INDEX IDX_B1ADDDB582D40A1F (workspace_id),
                INDEX IDX_B1ADDDB5A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_content2type (
                id INT AUTO_INCREMENT NOT NULL,
                content_id INT NOT NULL,
                type_id INT NOT NULL,
                next_id INT DEFAULT NULL,
                back_id INT DEFAULT NULL,
                size VARCHAR(30) NOT NULL,
                INDEX IDX_1A2084EF84A0A3ED (content_id),
                INDEX IDX_1A2084EFC54C8C93 (type_id),
                INDEX IDX_1A2084EFAA23F6C8 (next_id),
                INDEX IDX_1A2084EFE9583FF0 (back_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_region (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL,
                home_tab_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                type VARCHAR(255) NOT NULL,
                is_visible TINYINT(1) NOT NULL,
                is_locked TINYINT(1) NOT NULL,
                tab_order INT NOT NULL,
                INDEX IDX_F530F6BE7D08FA9E (home_tab_id),
                INDEX IDX_F530F6BEA76ED395 (user_id),
                INDEX IDX_F530F6BE82D40A1F (workspace_id),
                UNIQUE INDEX home_tab_config_unique_home_tab_user (home_tab_id, user_id),
                UNIQUE INDEX home_tab_config_unique_home_tab_workspace (home_tab_id, workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_subcontent (
                id INT AUTO_INCREMENT NOT NULL,
                father_id INT NOT NULL,
                child_id INT NOT NULL,
                next_id INT DEFAULT NULL,
                back_id INT DEFAULT NULL,
                size VARCHAR(255) DEFAULT NULL,
                INDEX IDX_D72E133C2055B9A2 (father_id),
                INDEX IDX_D72E133CDD62C21B (child_id),
                INDEX IDX_D72E133CAA23F6C8 (next_id),
                INDEX IDX_D72E133CE9583FF0 (back_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_type (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                max_content_page INT NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_content2region (
                id INT AUTO_INCREMENT NOT NULL,
                content_id INT NOT NULL,
                region_id INT NOT NULL,
                next_id INT DEFAULT NULL,
                back_id INT DEFAULT NULL,
                size VARCHAR(30) NOT NULL,
                INDEX IDX_8D18942E84A0A3ED (content_id),
                INDEX IDX_8D18942E98260155 (region_id),
                INDEX IDX_8D18942EAA23F6C8 (next_id),
                INDEX IDX_8D18942EE9583FF0 (back_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_content (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(255) DEFAULT NULL,
                content LONGTEXT DEFAULT NULL,
                generated_content LONGTEXT DEFAULT NULL,
                created DATETIME NOT NULL,
                modified DATETIME NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                INDEX IDX_A9744CCEA76ED395 (user_id),
                INDEX IDX_A9744CCE82D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_message (
                id INT AUTO_INCREMENT NOT NULL,
                sender_id INT NOT NULL,
                parent_id INT DEFAULT NULL,
                object VARCHAR(255) NOT NULL,
                content LONGTEXT NOT NULL,
                date DATETIME NOT NULL,
                is_removed TINYINT(1) NOT NULL,
                lft INT NOT NULL,
                lvl INT NOT NULL,
                rgt INT NOT NULL,
                root INT DEFAULT NULL,
                sender_username VARCHAR(255) NOT NULL,
                receiver_string VARCHAR(1023) NOT NULL,
                INDEX IDX_D6FE8DD8F624B39D (sender_id),
                INDEX IDX_D6FE8DD8727ACA70 (parent_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INT AUTO_INCREMENT NOT NULL,
                instruction VARCHAR(255) NOT NULL,
                start_date DATETIME DEFAULT NULL,
                end_date DATETIME DEFAULT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_E4A67CACB87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_activity (
                id INT AUTO_INCREMENT NOT NULL,
                activity_id INT NOT NULL,
                sequence_order INT DEFAULT NULL,
                resourceNode_id INT NOT NULL,
                INDEX IDX_DCF37C7E81C06096 (activity_id),
                INDEX IDX_DCF37C7EB87FAB32 (resourceNode_id),
                UNIQUE INDEX resource_activity_unique_combination (activity_id, resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type_custom_action (
                id INT AUTO_INCREMENT NOT NULL,
                resource_type_id INT DEFAULT NULL,
                action VARCHAR(255) DEFAULT NULL,
                async TINYINT(1) DEFAULT NULL,
                INDEX IDX_4A98967B98EC6B7B (resource_type_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INT AUTO_INCREMENT NOT NULL,
                size INT NOT NULL,
                hash_name VARCHAR(50) NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_EA81C80BE1F029B6 (hash_name),
                UNIQUE INDEX UNIQ_EA81C80BB87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_link (
                id INT AUTO_INCREMENT NOT NULL,
                url VARCHAR(255) NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_50B267EAB87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_icon (
                id INT AUTO_INCREMENT NOT NULL,
                shortcut_id INT DEFAULT NULL,
                icon_location VARCHAR(255) DEFAULT NULL,
                mimeType VARCHAR(255) NOT NULL,
                is_shortcut TINYINT(1) NOT NULL,
                relative_url VARCHAR(255) DEFAULT NULL,
                INDEX IDX_478C586179F0D498 (shortcut_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INT AUTO_INCREMENT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_12EEC186B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INT AUTO_INCREMENT NOT NULL,
                target_id INT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                INDEX IDX_5E7F4AB8158E0B66 (target_id),
                UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_text (
                id INT AUTO_INCREMENT NOT NULL,
                version INT NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                UNIQUE INDEX UNIQ_5D9559DCB87FAB32 (resourceNode_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_text_revision (
                id INT AUTO_INCREMENT NOT NULL,
                text_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                version INT NOT NULL,
                content LONGTEXT NOT NULL,
                INDEX IDX_F61948DE698D3548 (text_id),
                INDEX IDX_F61948DEA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INT AUTO_INCREMENT NOT NULL,
                doer_id INT DEFAULT NULL,
                receiver_id INT DEFAULT NULL,
                receiver_group_id INT DEFAULT NULL,
                owner_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                resource_type_id INT DEFAULT NULL,
                role_id INT DEFAULT NULL,
                action VARCHAR(255) NOT NULL,
                date_log DATETIME NOT NULL,
                short_date_log DATE NOT NULL,
                details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)',
                doer_type VARCHAR(255) NOT NULL,
                doer_ip VARCHAR(255) DEFAULT NULL,
                tool_name VARCHAR(255) DEFAULT NULL,
                is_displayed_in_admin TINYINT(1) NOT NULL,
                is_displayed_in_workspace TINYINT(1) NOT NULL,
                resourceNode_id INT DEFAULT NULL,
                INDEX IDX_97FAB91F12D3860F (doer_id),
                INDEX IDX_97FAB91FCD53EDB6 (receiver_id),
                INDEX IDX_97FAB91FC6F122B2 (receiver_group_id),
                INDEX IDX_97FAB91F7E3C61F9 (owner_id),
                INDEX IDX_97FAB91F82D40A1F (workspace_id),
                INDEX IDX_97FAB91FB87FAB32 (resourceNode_id),
                INDEX IDX_97FAB91F98EC6B7B (resource_type_id),
                INDEX IDX_97FAB91FD60322AC (role_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_platform_roles (
                log_id INT NOT NULL,
                role_id INT NOT NULL,
                INDEX IDX_706568A5EA675D86 (log_id),
                INDEX IDX_706568A5D60322AC (role_id),
                PRIMARY KEY(log_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_workspace_roles (
                log_id INT NOT NULL,
                role_id INT NOT NULL,
                INDEX IDX_8A8D2F47EA675D86 (log_id),
                INDEX IDX_8A8D2F47D60322AC (role_id),
                PRIMARY KEY(log_id, role_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log_workspace_widget_config (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                is_default TINYINT(1) NOT NULL,
                amount INT NOT NULL,
                restrictions LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)',
                INDEX IDX_D301C70782D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log_desktop_widget_config (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                is_default TINYINT(1) NOT NULL,
                amount INT NOT NULL,
                INDEX IDX_4AE48D62A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INT NOT NULL,
                user_id INT NOT NULL,
                INDEX IDX_BC83196EA76ED395 (user_id),
                PRIMARY KEY(workspace_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_theme (
                id INT AUTO_INCREMENT NOT NULL,
                plugin_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                path VARCHAR(255) NOT NULL,
                INDEX IDX_1D76301AEC942BCF (plugin_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INT AUTO_INCREMENT NOT NULL,
                plugin_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                is_configurable TINYINT(1) NOT NULL,
                icon VARCHAR(255) NOT NULL,
                is_exportable TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_76CA6C4F5E237E06 (name),
                INDEX IDX_76CA6C4FEC942BCF (plugin_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display (
                id INT AUTO_INCREMENT NOT NULL,
                parent_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                widget_id INT NOT NULL,
                is_locked TINYINT(1) NOT NULL,
                is_visible TINYINT(1) NOT NULL,
                is_desktop TINYINT(1) NOT NULL,
                INDEX IDX_2D34DB3727ACA70 (parent_id),
                INDEX IDX_2D34DB382D40A1F (workspace_id),
                INDEX IDX_2D34DB3A76ED395 (user_id),
                INDEX IDX_2D34DB3FBE885E2 (widget_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_widget_home_tab_config (
                id INT AUTO_INCREMENT NOT NULL,
                widget_id INT NOT NULL,
                home_tab_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                workspace_id INT DEFAULT NULL,
                widget_order VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                is_visible TINYINT(1) NOT NULL,
                is_locked TINYINT(1) NOT NULL,
                INDEX IDX_D48CC23EFBE885E2 (widget_id),
                INDEX IDX_D48CC23E7D08FA9E (home_tab_id),
                INDEX IDX_D48CC23EA76ED395 (user_id),
                INDEX IDX_D48CC23E82D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE simple_text_dekstop_widget_config (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                is_default TINYINT(1) NOT NULL,
                content LONGTEXT NOT NULL,
                INDEX IDX_BAB9695A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT DEFAULT NULL,
                is_default TINYINT(1) NOT NULL,
                content LONGTEXT NOT NULL,
                INDEX IDX_11925ED382D40A1F (workspace_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_plugin (
                id INT AUTO_INCREMENT NOT NULL,
                vendor_name VARCHAR(50) NOT NULL,
                short_name VARCHAR(50) NOT NULL,
                has_options TINYINT(1) NOT NULL,
                icon VARCHAR(255) NOT NULL,
                UNIQUE INDEX plugin_unique_name (vendor_name, short_name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_tools (
                id INT AUTO_INCREMENT NOT NULL,
                plugin_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                display_name VARCHAR(255) DEFAULT NULL,
                class VARCHAR(255) NOT NULL,
                is_workspace_required TINYINT(1) NOT NULL,
                is_desktop_required TINYINT(1) NOT NULL,
                is_displayable_in_workspace TINYINT(1) NOT NULL,
                is_displayable_in_desktop TINYINT(1) NOT NULL,
                is_exportable TINYINT(1) NOT NULL,
                is_configurable_in_workspace TINYINT(1) NOT NULL,
                is_configurable_in_desktop TINYINT(1) NOT NULL,
                UNIQUE INDEX UNIQ_60F909655E237E06 (name),
                INDEX IDX_60F90965EC942BCF (plugin_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_template (
                id INT AUTO_INCREMENT NOT NULL,
                hash VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_94D0CBDBD1B862B8 (hash),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                INDEX IDX_C8EFD7EFA76ED395 (user_id),
                UNIQUE INDEX tag_unique_name_and_user (user_id, name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag_hierarchy (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                tag_id INT NOT NULL,
                parent_id INT NOT NULL,
                level INT NOT NULL,
                INDEX IDX_A46B159EA76ED395 (user_id),
                INDEX IDX_A46B159EBAD26311 (tag_id),
                INDEX IDX_A46B159E727ACA70 (parent_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_rel_workspace_tag (
                id INT AUTO_INCREMENT NOT NULL,
                workspace_id INT NOT NULL,
                tag_id INT NOT NULL,
                INDEX IDX_7883931082D40A1F (workspace_id),
                INDEX IDX_78839310BAD26311 (tag_id),
                UNIQUE INDEX rel_workspace_tag_unique_combination (workspace_id, tag_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_license (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                acronym VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_badge_translation (
                id INT AUTO_INCREMENT NOT NULL,
                badge_id INT DEFAULT NULL,
                locale VARCHAR(8) NOT NULL,
                name VARCHAR(128) NOT NULL,
                description VARCHAR(128) NOT NULL,
                slug VARCHAR(128) NOT NULL,
                criteria LONGTEXT NOT NULL,
                INDEX IDX_849BC831F7A2C2FC (badge_id),
                UNIQUE INDEX badge_translation_unique_idx (locale, badge_id),
                UNIQUE INDEX badge_name_translation_unique_idx (name, locale, badge_id),
                UNIQUE INDEX badge_slug_translation_unique_idx (slug, locale, badge_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INT AUTO_INCREMENT NOT NULL,
                version SMALLINT NOT NULL,
                image VARCHAR(255) NOT NULL,
                expired_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE claro_user
            ADD CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_group
            ADD CONSTRAINT FK_ED8B34C7A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_group
            ADD CONSTRAINT FK_ED8B34C7FE54D947 FOREIGN KEY (group_id)
            REFERENCES claro_group (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            ADD CONSTRAINT FK_797E43FFA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            ADD CONSTRAINT FK_797E43FFD60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            ADD CONSTRAINT FK_1CBA5A40FE54D947 FOREIGN KEY (group_id)
            REFERENCES claro_group (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            ADD CONSTRAINT FK_1CBA5A40D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_role
            ADD CONSTRAINT FK_3177747182D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF460F904B FOREIGN KEY (license_id)
            REFERENCES claro_license (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF61220EA6 FOREIGN KEY (creator_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF54B9D732 FOREIGN KEY (icon_id)
            REFERENCES claro_resource_icon (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FFAA23F6C8 FOREIGN KEY (next_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            ADD CONSTRAINT FK_A76799FF2DE62210 FOREIGN KEY (previous_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD CONSTRAINT FK_D9028545A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_workspace (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            ADD CONSTRAINT FK_D012AF0FA08DFE7A FOREIGN KEY (aggregator_workspace_id)
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            ADD CONSTRAINT FK_D012AF0F782B5A3F FOREIGN KEY (simple_workspace_id)
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            ADD CONSTRAINT FK_D48EA38AA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            ADD CONSTRAINT FK_D48EA38A537A1329 FOREIGN KEY (message_id)
            REFERENCES claro_message (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            ADD CONSTRAINT FK_6CF1320E82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            ADD CONSTRAINT FK_6CF1320E8F7B22CC FOREIGN KEY (tool_id)
            REFERENCES claro_tools (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            ADD CONSTRAINT FK_6CF1320EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            ADD CONSTRAINT FK_9210497679732467 FOREIGN KEY (orderedtool_id)
            REFERENCES claro_ordered_tool (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            ADD CONSTRAINT FK_92104976D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            ADD CONSTRAINT FK_7EBB381FA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            ADD CONSTRAINT FK_7EBB381FF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            ADD CONSTRAINT FK_7EBB381FBB9D6FEE FOREIGN KEY (issuer_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            ADD CONSTRAINT FK_487A496AA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            ADD CONSTRAINT FK_487A496AF7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_mask_decoder
            ADD CONSTRAINT FK_39D93F4298EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            ADD CONSTRAINT FK_AEC62693EC942BCF FOREIGN KEY (plugin_id)
            REFERENCES claro_plugin (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action
            ADD CONSTRAINT FK_1F57E52B98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            ADD CONSTRAINT FK_3848F483D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            ADD CONSTRAINT FK_3848F483B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            ADD CONSTRAINT FK_84B4BEBA195FBDF1 FOREIGN KEY (resource_rights_id)
            REFERENCES claro_resource_rights (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            ADD CONSTRAINT FK_84B4BEBA98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event
            ADD CONSTRAINT FK_B1ADDDB582D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_event
            ADD CONSTRAINT FK_B1ADDDB5A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            ADD CONSTRAINT FK_1A2084EF84A0A3ED FOREIGN KEY (content_id)
            REFERENCES claro_content (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            ADD CONSTRAINT FK_1A2084EFC54C8C93 FOREIGN KEY (type_id)
            REFERENCES claro_type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            ADD CONSTRAINT FK_1A2084EFAA23F6C8 FOREIGN KEY (next_id)
            REFERENCES claro_content2type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            ADD CONSTRAINT FK_1A2084EFE9583FF0 FOREIGN KEY (back_id)
            REFERENCES claro_content2type (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            ADD CONSTRAINT FK_F530F6BE7D08FA9E FOREIGN KEY (home_tab_id)
            REFERENCES claro_home_tab (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            ADD CONSTRAINT FK_F530F6BEA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            ADD CONSTRAINT FK_F530F6BE82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            ADD CONSTRAINT FK_D72E133C2055B9A2 FOREIGN KEY (father_id)
            REFERENCES claro_content (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            ADD CONSTRAINT FK_D72E133CDD62C21B FOREIGN KEY (child_id)
            REFERENCES claro_content (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            ADD CONSTRAINT FK_D72E133CAA23F6C8 FOREIGN KEY (next_id)
            REFERENCES claro_subcontent (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            ADD CONSTRAINT FK_D72E133CE9583FF0 FOREIGN KEY (back_id)
            REFERENCES claro_subcontent (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            ADD CONSTRAINT FK_8D18942E84A0A3ED FOREIGN KEY (content_id)
            REFERENCES claro_content (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            ADD CONSTRAINT FK_8D18942E98260155 FOREIGN KEY (region_id)
            REFERENCES claro_region (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            ADD CONSTRAINT FK_8D18942EAA23F6C8 FOREIGN KEY (next_id)
            REFERENCES claro_content2region (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            ADD CONSTRAINT FK_8D18942EE9583FF0 FOREIGN KEY (back_id)
            REFERENCES claro_content2region (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            ADD CONSTRAINT FK_A9744CCEA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            ADD CONSTRAINT FK_A9744CCE82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_message
            ADD CONSTRAINT FK_D6FE8DD8F624B39D FOREIGN KEY (sender_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_message
            ADD CONSTRAINT FK_D6FE8DD8727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_message (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            ADD CONSTRAINT FK_E4A67CACB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            ADD CONSTRAINT FK_DCF37C7E81C06096 FOREIGN KEY (activity_id)
            REFERENCES claro_activity (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            ADD CONSTRAINT FK_DCF37C7EB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action
            ADD CONSTRAINT FK_4A98967B98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_file
            ADD CONSTRAINT FK_EA81C80BB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_link
            ADD CONSTRAINT FK_50B267EAB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon
            ADD CONSTRAINT FK_478C586179F0D498 FOREIGN KEY (shortcut_id)
            REFERENCES claro_resource_icon (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            ADD CONSTRAINT FK_12EEC186B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB8158E0B66 FOREIGN KEY (target_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            ADD CONSTRAINT FK_5E7F4AB8B87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text
            ADD CONSTRAINT FK_5D9559DCB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            ADD CONSTRAINT FK_F61948DE698D3548 FOREIGN KEY (text_id)
            REFERENCES claro_text (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            ADD CONSTRAINT FK_F61948DEA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91F12D3860F FOREIGN KEY (doer_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91FCD53EDB6 FOREIGN KEY (receiver_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91FC6F122B2 FOREIGN KEY (receiver_group_id)
            REFERENCES claro_group (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91F7E3C61F9 FOREIGN KEY (owner_id)
            REFERENCES claro_user (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91F82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91FB87FAB32 FOREIGN KEY (resourceNode_id)
            REFERENCES claro_resource_node (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91F98EC6B7B FOREIGN KEY (resource_type_id)
            REFERENCES claro_resource_type (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log
            ADD CONSTRAINT FK_97FAB91FD60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            ADD CONSTRAINT FK_706568A5EA675D86 FOREIGN KEY (log_id)
            REFERENCES claro_log (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            ADD CONSTRAINT FK_706568A5D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            ADD CONSTRAINT FK_8A8D2F47EA675D86 FOREIGN KEY (log_id)
            REFERENCES claro_log (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            ADD CONSTRAINT FK_8A8D2F47D60322AC FOREIGN KEY (role_id)
            REFERENCES claro_role (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            ADD CONSTRAINT FK_D301C70782D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config
            ADD CONSTRAINT FK_4AE48D62A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config
            ADD CONSTRAINT FK_BC83196EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_theme
            ADD CONSTRAINT FK_1D76301AEC942BCF FOREIGN KEY (plugin_id)
            REFERENCES claro_plugin (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            ADD CONSTRAINT FK_76CA6C4FEC942BCF FOREIGN KEY (plugin_id)
            REFERENCES claro_plugin (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB3727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_widget_display (id)
            ON DELETE SET NULL
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB382D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB3A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            ADD CONSTRAINT FK_2D34DB3FBE885E2 FOREIGN KEY (widget_id)
            REFERENCES claro_widget (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23EFBE885E2 FOREIGN KEY (widget_id)
            REFERENCES claro_widget (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23E7D08FA9E FOREIGN KEY (home_tab_id)
            REFERENCES claro_home_tab (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            ADD CONSTRAINT FK_D48CC23E82D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config
            ADD CONSTRAINT FK_BAB9695A76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config
            ADD CONSTRAINT FK_11925ED382D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_tools
            ADD CONSTRAINT FK_60F90965EC942BCF FOREIGN KEY (plugin_id)
            REFERENCES claro_plugin (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag
            ADD CONSTRAINT FK_C8EFD7EFA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            ADD CONSTRAINT FK_A46B159EA76ED395 FOREIGN KEY (user_id)
            REFERENCES claro_user (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            ADD CONSTRAINT FK_A46B159EBAD26311 FOREIGN KEY (tag_id)
            REFERENCES claro_workspace_tag (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            ADD CONSTRAINT FK_A46B159E727ACA70 FOREIGN KEY (parent_id)
            REFERENCES claro_workspace_tag (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            ADD CONSTRAINT FK_7883931082D40A1F FOREIGN KEY (workspace_id)
            REFERENCES claro_workspace (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            ADD CONSTRAINT FK_78839310BAD26311 FOREIGN KEY (tag_id)
            REFERENCES claro_workspace_tag (id)
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation
            ADD CONSTRAINT FK_849BC831F7A2C2FC FOREIGN KEY (badge_id)
            REFERENCES claro_badge (id)
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_group
            DROP FOREIGN KEY FK_ED8B34C7A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            DROP FOREIGN KEY FK_797E43FFA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF61220EA6
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            DROP FOREIGN KEY FK_D48EA38AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP FOREIGN KEY FK_6CF1320EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP FOREIGN KEY FK_7EBB381FA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP FOREIGN KEY FK_7EBB381FBB9D6FEE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            DROP FOREIGN KEY FK_487A496AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_event
            DROP FOREIGN KEY FK_B1ADDDB5A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP FOREIGN KEY FK_F530F6BEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            DROP FOREIGN KEY FK_A9744CCEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_message
            DROP FOREIGN KEY FK_D6FE8DD8F624B39D
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            DROP FOREIGN KEY FK_F61948DEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91F12D3860F
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91FCD53EDB6
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91F7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config
            DROP FOREIGN KEY FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config
            DROP FOREIGN KEY FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP FOREIGN KEY FK_2D34DB3A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP FOREIGN KEY FK_D48CC23EA76ED395
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config
            DROP FOREIGN KEY FK_BAB9695A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag
            DROP FOREIGN KEY FK_C8EFD7EFA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP FOREIGN KEY FK_A46B159EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_group
            DROP FOREIGN KEY FK_ED8B34C7FE54D947
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            DROP FOREIGN KEY FK_1CBA5A40FE54D947
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91FC6F122B2
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            DROP FOREIGN KEY FK_797E43FFD60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            DROP FOREIGN KEY FK_1CBA5A40D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            DROP FOREIGN KEY FK_92104976D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP FOREIGN KEY FK_3848F483D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91FD60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            DROP FOREIGN KEY FK_706568A5D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            DROP FOREIGN KEY FK_8A8D2F47D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP FOREIGN KEY FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            DROP FOREIGN KEY FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP FOREIGN KEY FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file
            DROP FOREIGN KEY FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link
            DROP FOREIGN KEY FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            DROP FOREIGN KEY FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP FOREIGN KEY FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP FOREIGN KEY FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text
            DROP FOREIGN KEY FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_user
            DROP FOREIGN KEY FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_role
            DROP FOREIGN KEY FK_3177747182D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP FOREIGN KEY FK_D9028545727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            DROP FOREIGN KEY FK_D012AF0FA08DFE7A
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            DROP FOREIGN KEY FK_D012AF0F782B5A3F
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP FOREIGN KEY FK_6CF1320E82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_event
            DROP FOREIGN KEY FK_B1ADDDB582D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP FOREIGN KEY FK_F530F6BE82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            DROP FOREIGN KEY FK_A9744CCE82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91F82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            DROP FOREIGN KEY FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP FOREIGN KEY FK_2D34DB382D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP FOREIGN KEY FK_D48CC23E82D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config
            DROP FOREIGN KEY FK_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            DROP FOREIGN KEY FK_7883931082D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            DROP FOREIGN KEY FK_9210497679732467
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_mask_decoder
            DROP FOREIGN KEY FK_39D93F4298EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action
            DROP FOREIGN KEY FK_1F57E52B98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            DROP FOREIGN KEY FK_84B4BEBA98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action
            DROP FOREIGN KEY FK_4A98967B98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP FOREIGN KEY FK_97FAB91F98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            DROP FOREIGN KEY FK_84B4BEBA195FBDF1
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP FOREIGN KEY FK_1A2084EFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP FOREIGN KEY FK_1A2084EFE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP FOREIGN KEY FK_8D18942E98260155
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP FOREIGN KEY FK_D72E133CAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP FOREIGN KEY FK_D72E133CE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP FOREIGN KEY FK_1A2084EFC54C8C93
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP FOREIGN KEY FK_8D18942EAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP FOREIGN KEY FK_8D18942EE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP FOREIGN KEY FK_1A2084EF84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP FOREIGN KEY FK_D72E133C2055B9A2
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP FOREIGN KEY FK_D72E133CDD62C21B
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP FOREIGN KEY FK_8D18942E84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP FOREIGN KEY FK_F530F6BE7D08FA9E
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP FOREIGN KEY FK_D48CC23E7D08FA9E
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            DROP FOREIGN KEY FK_D48EA38A537A1329
        ");
        $this->addSql("
            ALTER TABLE claro_message
            DROP FOREIGN KEY FK_D6FE8DD8727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP FOREIGN KEY FK_DCF37C7E81C06096
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon
            DROP FOREIGN KEY FK_478C586179F0D498
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            DROP FOREIGN KEY FK_F61948DE698D3548
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            DROP FOREIGN KEY FK_706568A5EA675D86
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            DROP FOREIGN KEY FK_8A8D2F47EA675D86
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP FOREIGN KEY FK_2D34DB3FBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP FOREIGN KEY FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP FOREIGN KEY FK_2D34DB3727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            DROP FOREIGN KEY FK_AEC62693EC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_theme
            DROP FOREIGN KEY FK_1D76301AEC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            DROP FOREIGN KEY FK_76CA6C4FEC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_tools
            DROP FOREIGN KEY FK_60F90965EC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP FOREIGN KEY FK_6CF1320E8F7B22CC
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP FOREIGN KEY FK_A46B159EBAD26311
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP FOREIGN KEY FK_A46B159E727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            DROP FOREIGN KEY FK_78839310BAD26311
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP FOREIGN KEY FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP FOREIGN KEY FK_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            DROP FOREIGN KEY FK_487A496AF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation
            DROP FOREIGN KEY FK_849BC831F7A2C2FC
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user_group
        ");
        $this->addSql("
            DROP TABLE claro_user_role
        ");
        $this->addSql("
            DROP TABLE claro_group
        ");
        $this->addSql("
            DROP TABLE claro_group_role
        ");
        $this->addSql("
            DROP TABLE claro_role
        ");
        $this->addSql("
            DROP TABLE claro_resource_node
        ");
        $this->addSql("
            DROP TABLE claro_workspace
        ");
        $this->addSql("
            DROP TABLE claro_workspace_aggregation
        ");
        $this->addSql("
            DROP TABLE claro_user_message
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool
        ");
        $this->addSql("
            DROP TABLE claro_ordered_tool_role
        ");
        $this->addSql("
            DROP TABLE claro_user_badge
        ");
        $this->addSql("
            DROP TABLE claro_badge_claim
        ");
        $this->addSql("
            DROP TABLE claro_resource_mask_decoder
        ");
        $this->addSql("
            DROP TABLE claro_resource_type
        ");
        $this->addSql("
            DROP TABLE claro_menu_action
        ");
        $this->addSql("
            DROP TABLE claro_resource_rights
        ");
        $this->addSql("
            DROP TABLE claro_list_type_creation
        ");
        $this->addSql("
            DROP TABLE claro_event
        ");
        $this->addSql("
            DROP TABLE claro_content2type
        ");
        $this->addSql("
            DROP TABLE claro_region
        ");
        $this->addSql("
            DROP TABLE claro_home_tab_config
        ");
        $this->addSql("
            DROP TABLE claro_subcontent
        ");
        $this->addSql("
            DROP TABLE claro_type
        ");
        $this->addSql("
            DROP TABLE claro_content2region
        ");
        $this->addSql("
            DROP TABLE claro_content
        ");
        $this->addSql("
            DROP TABLE claro_home_tab
        ");
        $this->addSql("
            DROP TABLE claro_message
        ");
        $this->addSql("
            DROP TABLE claro_activity
        ");
        $this->addSql("
            DROP TABLE claro_resource_activity
        ");
        $this->addSql("
            DROP TABLE claro_resource_type_custom_action
        ");
        $this->addSql("
            DROP TABLE claro_file
        ");
        $this->addSql("
            DROP TABLE claro_link
        ");
        $this->addSql("
            DROP TABLE claro_resource_icon
        ");
        $this->addSql("
            DROP TABLE claro_directory
        ");
        $this->addSql("
            DROP TABLE claro_resource_shortcut
        ");
        $this->addSql("
            DROP TABLE claro_text
        ");
        $this->addSql("
            DROP TABLE claro_text_revision
        ");
        $this->addSql("
            DROP TABLE claro_log
        ");
        $this->addSql("
            DROP TABLE claro_log_doer_platform_roles
        ");
        $this->addSql("
            DROP TABLE claro_log_doer_workspace_roles
        ");
        $this->addSql("
            DROP TABLE claro_log_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_desktop_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_log_hidden_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_theme
        ");
        $this->addSql("
            DROP TABLE claro_widget
        ");
        $this->addSql("
            DROP TABLE claro_widget_display
        ");
        $this->addSql("
            DROP TABLE claro_widget_home_tab_config
        ");
        $this->addSql("
            DROP TABLE simple_text_dekstop_widget_config
        ");
        $this->addSql("
            DROP TABLE simple_text_workspace_widget_config
        ");
        $this->addSql("
            DROP TABLE claro_plugin
        ");
        $this->addSql("
            DROP TABLE claro_tools
        ");
        $this->addSql("
            DROP TABLE claro_workspace_template
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_workspace_tag_hierarchy
        ");
        $this->addSql("
            DROP TABLE claro_rel_workspace_tag
        ");
        $this->addSql("
            DROP TABLE claro_license
        ");
        $this->addSql("
            DROP TABLE claro_badge_translation
        ");
        $this->addSql("
            DROP TABLE claro_badge
        ");
    }
}
