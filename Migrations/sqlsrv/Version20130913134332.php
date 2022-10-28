<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                first_name NVARCHAR(50) NOT NULL,
                last_name NVARCHAR(50) NOT NULL,
                username NVARCHAR(255) NOT NULL,
                password NVARCHAR(255) NOT NULL,
                salt NVARCHAR(255) NOT NULL,
                phone NVARCHAR(255),
                mail NVARCHAR(255) NOT NULL,
                administrative_code NVARCHAR(255),
                creation_date DATETIME2(6) NOT NULL,
                reset_password NVARCHAR(255),
                hash_time INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
            WHERE username IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
            WHERE mail IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
            WHERE workspace_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_user_group (
                user_id INT NOT NULL,
                group_id INT NOT NULL,
                PRIMARY KEY (user_id, group_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_ED8B34C7A76ED395 ON claro_user_group (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_ED8B34C7FE54D947 ON claro_user_group (group_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_role (
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (user_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_797E43FFA76ED395 ON claro_user_role (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_797E43FFD60322AC ON claro_user_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_group (
                id INT IDENTITY NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX group_unique_name ON claro_group (name)
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_group_role (
                group_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (group_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1CBA5A40FE54D947 ON claro_group_role (group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1CBA5A40D60322AC ON claro_group_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_role (
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                name NVARCHAR(255) NOT NULL,
                translation_key NVARCHAR(255) NOT NULL,
                is_read_only BIT NOT NULL,
                type INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_317774715E237E06 ON claro_role (name)
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_3177747182D40A1F ON claro_role (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_node (
                id INT IDENTITY NOT NULL,
                license_id INT,
                resource_type_id INT NOT NULL,
                creator_id INT NOT NULL,
                icon_id INT,
                parent_id INT,
                workspace_id INT NOT NULL,
                next_id INT,
                previous_id INT,
                creation_date DATETIME2(6) NOT NULL,
                modification_date DATETIME2(6) NOT NULL,
                name NVARCHAR(255) NOT NULL,
                lvl INT,
                path NVARCHAR(3000),
                mime_type NVARCHAR(255),
                class NVARCHAR(256) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF460F904B ON claro_resource_node (license_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF98EC6B7B ON claro_resource_node (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF61220EA6 ON claro_resource_node (creator_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF54B9D732 ON claro_resource_node (icon_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF727ACA70 ON claro_resource_node (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A76799FF82D40A1F ON claro_resource_node (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FFAA23F6C8 ON claro_resource_node (next_id)
            WHERE next_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_A76799FF2DE62210 ON claro_resource_node (previous_id)
            WHERE previous_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_workspace (
                id INT IDENTITY NOT NULL,
                user_id INT,
                parent_id INT,
                name NVARCHAR(255) NOT NULL,
                code NVARCHAR(255) NOT NULL,
                is_public BIT,
                displayable BIT,
                guid NVARCHAR(255) NOT NULL,
                self_registration BIT,
                self_unregistration BIT,
                discr NVARCHAR(255) NOT NULL,
                lft INT,
                lvl INT,
                rgt INT,
                root INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D902854577153098 ON claro_workspace (code)
            WHERE code IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_D90285452B6FCFB2 ON claro_workspace (guid)
            WHERE guid IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545A76ED395 ON claro_workspace (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_aggregation (
                aggregator_workspace_id INT NOT NULL,
                simple_workspace_id INT NOT NULL,
                PRIMARY KEY (
                    aggregator_workspace_id, simple_workspace_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D012AF0FA08DFE7A ON claro_workspace_aggregation (aggregator_workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D012AF0F782B5A3F ON claro_workspace_aggregation (simple_workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_message (
                id INT IDENTITY NOT NULL,
                user_id INT NOT NULL,
                message_id INT NOT NULL,
                is_removed BIT NOT NULL,
                is_read BIT NOT NULL,
                is_sent BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D48EA38AA76ED395 ON claro_user_message (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D48EA38A537A1329 ON claro_user_message (message_id)
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool (
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                tool_id INT NOT NULL,
                user_id INT,
                display_order INT NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E82D40A1F ON claro_ordered_tool (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320E8F7B22CC ON claro_ordered_tool (tool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6CF1320EA76ED395 ON claro_ordered_tool (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_tool_ws_usr ON claro_ordered_tool (tool_id, workspace_id, user_id)
            WHERE tool_id IS NOT NULL
            AND workspace_id IS NOT NULL
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX ordered_tool_unique_name_by_workspace ON claro_ordered_tool (workspace_id, name)
            WHERE workspace_id IS NOT NULL
            AND name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_ordered_tool_role (
                orderedtool_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (orderedtool_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_9210497679732467 ON claro_ordered_tool_role (orderedtool_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_92104976D60322AC ON claro_ordered_tool_role (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_user_badge (
                id INT IDENTITY NOT NULL,
                user_id INT NOT NULL,
                badge_id INT NOT NULL,
                issuer_id INT,
                issued_at DATETIME2(6) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FA76ED395 ON claro_user_badge (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FF7A2C2FC ON claro_user_badge (badge_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7EBB381FBB9D6FEE ON claro_user_badge (issuer_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_badge_unique ON claro_user_badge (user_id, badge_id)
            WHERE user_id IS NOT NULL
            AND badge_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_badge_claim (
                id INT IDENTITY NOT NULL,
                user_id INT NOT NULL,
                badge_id INT NOT NULL,
                claimed_at DATETIME2(6) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_487A496AA76ED395 ON claro_badge_claim (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_487A496AF7A2C2FC ON claro_badge_claim (badge_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX badge_claim_unique ON claro_badge_claim (user_id, badge_id)
            WHERE user_id IS NOT NULL
            AND badge_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_resource_mask_decoder (
                id INT IDENTITY NOT NULL,
                resource_type_id INT NOT NULL,
                value INT NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_39D93F4298EC6B7B ON claro_resource_mask_decoder (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type (
                id INT IDENTITY NOT NULL,
                plugin_id INT,
                name NVARCHAR(255) NOT NULL,
                is_exportable BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_AEC626935E237E06 ON claro_resource_type (name)
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_AEC62693EC942BCF ON claro_resource_type (plugin_id)
        ");
        $this->addSql("
            CREATE TABLE claro_menu_action (
                id INT IDENTITY NOT NULL,
                resource_type_id INT,
                name NVARCHAR(255),
                async BIT,
                is_custom BIT NOT NULL,
                is_form BIT NOT NULL,
                value NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1F57E52B98EC6B7B ON claro_menu_action (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_resource_rights (
                id INT IDENTITY NOT NULL,
                role_id INT NOT NULL,
                mask INT NOT NULL,
                resourceNode_id INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483D60322AC ON claro_resource_rights (role_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_3848F483B87FAB32 ON claro_resource_rights (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_rights_unique_resource_role ON claro_resource_rights (resourceNode_id, role_id)
            WHERE resourceNode_id IS NOT NULL
            AND role_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_list_type_creation (
                resource_rights_id INT NOT NULL,
                resource_type_id INT NOT NULL,
                PRIMARY KEY (
                    resource_rights_id, resource_type_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_84B4BEBA195FBDF1 ON claro_list_type_creation (resource_rights_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_84B4BEBA98EC6B7B ON claro_list_type_creation (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_event (
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                user_id INT NOT NULL,
                title NVARCHAR(50) NOT NULL,
                start_date INT,
                end_date INT,
                description NVARCHAR(255),
                allday BIT,
                priority NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB582D40A1F ON claro_event (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_B1ADDDB5A76ED395 ON claro_event (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_content2type (
                id INT IDENTITY NOT NULL,
                content_id INT NOT NULL,
                type_id INT NOT NULL,
                next_id INT,
                back_id INT,
                size NVARCHAR(30) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EF84A0A3ED ON claro_content2type (content_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFC54C8C93 ON claro_content2type (type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFAA23F6C8 ON claro_content2type (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_1A2084EFE9583FF0 ON claro_content2type (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_region (
                id INT IDENTITY NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab_config (
                id INT IDENTITY NOT NULL,
                home_tab_id INT NOT NULL,
                user_id INT,
                workspace_id INT,
                type NVARCHAR(255) NOT NULL,
                is_visible BIT NOT NULL,
                is_locked BIT NOT NULL,
                tab_order INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE7D08FA9E ON claro_home_tab_config (home_tab_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BEA76ED395 ON claro_home_tab_config (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F530F6BE82D40A1F ON claro_home_tab_config (workspace_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_user ON claro_home_tab_config (home_tab_id, user_id)
            WHERE home_tab_id IS NOT NULL
            AND user_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX home_tab_config_unique_home_tab_workspace ON claro_home_tab_config (home_tab_id, workspace_id)
            WHERE home_tab_id IS NOT NULL
            AND workspace_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_subcontent (
                id INT IDENTITY NOT NULL,
                father_id INT NOT NULL,
                child_id INT NOT NULL,
                next_id INT,
                back_id INT,
                size NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133C2055B9A2 ON claro_subcontent (father_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CDD62C21B ON claro_subcontent (child_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CAA23F6C8 ON claro_subcontent (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D72E133CE9583FF0 ON claro_subcontent (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_type (
                id INT IDENTITY NOT NULL,
                name NVARCHAR(255) NOT NULL,
                max_content_page INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_content2region (
                id INT IDENTITY NOT NULL,
                content_id INT NOT NULL,
                region_id INT NOT NULL,
                next_id INT,
                back_id INT,
                size NVARCHAR(30) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942E84A0A3ED ON claro_content2region (content_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942E98260155 ON claro_content2region (region_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942EAA23F6C8 ON claro_content2region (next_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8D18942EE9583FF0 ON claro_content2region (back_id)
        ");
        $this->addSql("
            CREATE TABLE claro_content (
                id INT IDENTITY NOT NULL,
                title NVARCHAR(255),
                content VARCHAR(MAX),
                generated_content VARCHAR(MAX),
                created DATETIME2(6) NOT NULL,
                modified DATETIME2(6) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_home_tab (
                id INT IDENTITY NOT NULL,
                user_id INT,
                workspace_id INT,
                name NVARCHAR(255) NOT NULL,
                type NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCEA76ED395 ON claro_home_tab (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A9744CCE82D40A1F ON claro_home_tab (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_message (
                id INT IDENTITY NOT NULL,
                sender_id INT NOT NULL,
                parent_id INT,
                object NVARCHAR(255) NOT NULL,
                content VARCHAR(MAX) NOT NULL,
                date DATETIME2(6) NOT NULL,
                is_removed BIT NOT NULL,
                lft INT NOT NULL,
                lvl INT NOT NULL,
                rgt INT NOT NULL,
                root INT,
                sender_username NVARCHAR(255) NOT NULL,
                receiver_string NVARCHAR(1023) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8F624B39D ON claro_message (sender_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D6FE8DD8727ACA70 ON claro_message (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_activity (
                id INT IDENTITY NOT NULL,
                instruction NVARCHAR(255) NOT NULL,
                start_date DATETIME2(6),
                end_date DATETIME2(6),
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_E4A67CACB87FAB32 ON claro_activity (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_resource_activity (
                id INT IDENTITY NOT NULL,
                activity_id INT NOT NULL,
                sequence_order INT,
                resourceNode_id INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7E81C06096 ON claro_resource_activity (activity_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_DCF37C7EB87FAB32 ON claro_resource_activity (resourceNode_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX resource_activity_unique_combination ON claro_resource_activity (activity_id, resourceNode_id)
            WHERE activity_id IS NOT NULL
            AND resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_resource_type_custom_action (
                id INT IDENTITY NOT NULL,
                resource_type_id INT,
                action NVARCHAR(255),
                async BIT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4A98967B98EC6B7B ON claro_resource_type_custom_action (resource_type_id)
        ");
        $this->addSql("
            CREATE TABLE claro_file (
                id INT IDENTITY NOT NULL,
                size INT NOT NULL,
                hash_name NVARCHAR(50) NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BE1F029B6 ON claro_file (hash_name)
            WHERE hash_name IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EA81C80BB87FAB32 ON claro_file (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_link (
                id INT IDENTITY NOT NULL,
                url NVARCHAR(255) NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_50B267EAB87FAB32 ON claro_link (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_resource_icon (
                id INT IDENTITY NOT NULL,
                shortcut_id INT,
                icon_location NVARCHAR(255),
                mimeType NVARCHAR(255) NOT NULL,
                is_shortcut BIT NOT NULL,
                relative_url NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_478C586179F0D498 ON claro_resource_icon (shortcut_id)
        ");
        $this->addSql("
            CREATE TABLE claro_directory (
                id INT IDENTITY NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_12EEC186B87FAB32 ON claro_directory (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_resource_shortcut (
                id INT IDENTITY NOT NULL,
                target_id INT NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5E7F4AB8158E0B66 ON claro_resource_shortcut (target_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5E7F4AB8B87FAB32 ON claro_resource_shortcut (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_text (
                id INT IDENTITY NOT NULL,
                version INT NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5D9559DCB87FAB32 ON claro_text (resourceNode_id)
            WHERE resourceNode_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_text_revision (
                id INT IDENTITY NOT NULL,
                text_id INT,
                user_id INT,
                version INT NOT NULL,
                content VARCHAR(MAX) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DE698D3548 ON claro_text_revision (text_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F61948DEA76ED395 ON claro_text_revision (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log (
                id INT IDENTITY NOT NULL,
                doer_id INT,
                receiver_id INT,
                receiver_group_id INT,
                owner_id INT,
                workspace_id INT,
                resource_type_id INT,
                role_id INT,
                action NVARCHAR(255) NOT NULL,
                date_log DATETIME2(6) NOT NULL,
                short_date_log DATE NOT NULL,
                details VARCHAR(MAX),
                doer_type NVARCHAR(255) NOT NULL,
                doer_ip NVARCHAR(255),
                tool_name NVARCHAR(255),
                is_displayed_in_admin BIT NOT NULL,
                is_displayed_in_workspace BIT NOT NULL,
                resourceNode_id INT,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F12D3860F ON claro_log (doer_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FCD53EDB6 ON claro_log (receiver_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FC6F122B2 ON claro_log (receiver_group_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F7E3C61F9 ON claro_log (owner_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F82D40A1F ON claro_log (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FB87FAB32 ON claro_log (resourceNode_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91F98EC6B7B ON claro_log (resource_type_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97FAB91FD60322AC ON claro_log (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_platform_roles (
                log_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (log_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_706568A5EA675D86 ON claro_log_doer_platform_roles (log_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_706568A5D60322AC ON claro_log_doer_platform_roles (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_doer_workspace_roles (
                log_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (log_id, role_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8A8D2F47EA675D86 ON claro_log_doer_workspace_roles (log_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8A8D2F47D60322AC ON claro_log_doer_workspace_roles (role_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_workspace_widget_config (
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                is_default BIT NOT NULL,
                amount INT NOT NULL,
                restrictions VARCHAR(MAX),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D301C70782D40A1F ON claro_log_workspace_widget_config (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_desktop_widget_config (
                id INT IDENTITY NOT NULL,
                user_id INT,
                is_default BIT NOT NULL,
                amount INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_4AE48D62A76ED395 ON claro_log_desktop_widget_config (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_log_hidden_workspace_widget_config (
                workspace_id INT NOT NULL,
                user_id INT NOT NULL,
                PRIMARY KEY (workspace_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BC83196EA76ED395 ON claro_log_hidden_workspace_widget_config (user_id)
        ");
        $this->addSql("
            CREATE TABLE claro_theme (
                id INT IDENTITY NOT NULL,
                plugin_id INT,
                name NVARCHAR(255) NOT NULL,
                path NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_1D76301AEC942BCF ON claro_theme (plugin_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget (
                id INT IDENTITY NOT NULL,
                plugin_id INT,
                name NVARCHAR(255) NOT NULL,
                is_configurable BIT NOT NULL,
                icon NVARCHAR(255) NOT NULL,
                is_exportable BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_76CA6C4F5E237E06 ON claro_widget (name)
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_76CA6C4FEC942BCF ON claro_widget (plugin_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_display (
                id INT IDENTITY NOT NULL,
                parent_id INT,
                workspace_id INT,
                user_id INT,
                widget_id INT NOT NULL,
                is_locked BIT NOT NULL,
                is_visible BIT NOT NULL,
                is_desktop BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3727ACA70 ON claro_widget_display (parent_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB382D40A1F ON claro_widget_display (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3A76ED395 ON claro_widget_display (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_2D34DB3FBE885E2 ON claro_widget_display (widget_id)
        ");
        $this->addSql("
            CREATE TABLE claro_widget_home_tab_config (
                id INT IDENTITY NOT NULL,
                widget_id INT NOT NULL,
                home_tab_id INT NOT NULL,
                user_id INT,
                workspace_id INT,
                widget_order NVARCHAR(255) NOT NULL,
                type NVARCHAR(255) NOT NULL,
                is_visible BIT NOT NULL,
                is_locked BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D48CC23EFBE885E2 ON claro_widget_home_tab_config (widget_id)
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
            CREATE TABLE simple_text_dekstop_widget_config (
                id INT IDENTITY NOT NULL,
                user_id INT,
                is_default BIT NOT NULL,
                content VARCHAR(MAX) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_BAB9695A76ED395 ON simple_text_dekstop_widget_config (user_id)
        ");
        $this->addSql("
            CREATE TABLE simple_text_workspace_widget_config (
                id INT IDENTITY NOT NULL,
                workspace_id INT,
                is_default BIT NOT NULL,
                content VARCHAR(MAX) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_11925ED382D40A1F ON simple_text_workspace_widget_config (workspace_id)
        ");
        $this->addSql("
            CREATE TABLE claro_plugin (
                id INT IDENTITY NOT NULL,
                vendor_name NVARCHAR(50) NOT NULL,
                short_name NVARCHAR(50) NOT NULL,
                has_options BIT NOT NULL,
                icon NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX plugin_unique_name ON claro_plugin (vendor_name, short_name)
            WHERE vendor_name IS NOT NULL
            AND short_name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_tools (
                id INT IDENTITY NOT NULL,
                plugin_id INT,
                name NVARCHAR(255) NOT NULL,
                display_name NVARCHAR(255),
                class NVARCHAR(255) NOT NULL,
                is_workspace_required BIT NOT NULL,
                is_desktop_required BIT NOT NULL,
                is_displayable_in_workspace BIT NOT NULL,
                is_displayable_in_desktop BIT NOT NULL,
                is_exportable BIT NOT NULL,
                is_configurable_in_workspace BIT NOT NULL,
                is_configurable_in_desktop BIT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_60F909655E237E06 ON claro_tools (name)
            WHERE name IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_60F90965EC942BCF ON claro_tools (plugin_id)
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_template (
                id INT IDENTITY NOT NULL,
                hash NVARCHAR(255) NOT NULL,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_94D0CBDBD1B862B8 ON claro_workspace_template (hash)
            WHERE hash IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag (
                id INT IDENTITY NOT NULL,
                user_id INT,
                name NVARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C8EFD7EFA76ED395 ON claro_workspace_tag (user_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX tag_unique_name_and_user ON claro_workspace_tag (user_id, name)
            WHERE user_id IS NOT NULL
            AND name IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_workspace_tag_hierarchy (
                id INT IDENTITY NOT NULL,
                user_id INT,
                tag_id INT NOT NULL,
                parent_id INT NOT NULL,
                level INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159EA76ED395 ON claro_workspace_tag_hierarchy (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159EBAD26311 ON claro_workspace_tag_hierarchy (tag_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A46B159E727ACA70 ON claro_workspace_tag_hierarchy (parent_id)
        ");
        $this->addSql("
            CREATE TABLE claro_rel_workspace_tag (
                id INT IDENTITY NOT NULL,
                workspace_id INT NOT NULL,
                tag_id INT NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_7883931082D40A1F ON claro_rel_workspace_tag (workspace_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_78839310BAD26311 ON claro_rel_workspace_tag (tag_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX rel_workspace_tag_unique_combination ON claro_rel_workspace_tag (workspace_id, tag_id)
            WHERE workspace_id IS NOT NULL
            AND tag_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_license (
                id INT IDENTITY NOT NULL,
                name NVARCHAR(255) NOT NULL,
                acronym NVARCHAR(255),
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_badge_translation (
                id INT IDENTITY NOT NULL,
                badge_id INT,
                locale NVARCHAR(8) NOT NULL,
                name NVARCHAR(128) NOT NULL,
                description NVARCHAR(128) NOT NULL,
                slug NVARCHAR(128) NOT NULL,
                criteria VARCHAR(MAX) NOT NULL,
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_849BC831F7A2C2FC ON claro_badge_translation (badge_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX badge_translation_unique_idx ON claro_badge_translation (locale, badge_id)
            WHERE locale IS NOT NULL
            AND badge_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX badge_name_translation_unique_idx ON claro_badge_translation (name, locale, badge_id)
            WHERE name IS NOT NULL
            AND locale IS NOT NULL
            AND badge_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX badge_slug_translation_unique_idx ON claro_badge_translation (slug, locale, badge_id)
            WHERE slug IS NOT NULL
            AND locale IS NOT NULL
            AND badge_id IS NOT NULL
        ");
        $this->addSql("
            CREATE TABLE claro_badge (
                id INT IDENTITY NOT NULL,
                version SMALLINT NOT NULL,
                image NVARCHAR(255) NOT NULL,
                expired_at DATETIME2(6),
                PRIMARY KEY (id)
            )
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
            DROP CONSTRAINT FK_ED8B34C7A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            DROP CONSTRAINT FK_797E43FFA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF61220EA6
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP CONSTRAINT FK_D9028545A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            DROP CONSTRAINT FK_D48EA38AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP CONSTRAINT FK_6CF1320EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP CONSTRAINT FK_7EBB381FA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP CONSTRAINT FK_7EBB381FBB9D6FEE
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            DROP CONSTRAINT FK_487A496AA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_event
            DROP CONSTRAINT FK_B1ADDDB5A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP CONSTRAINT FK_F530F6BEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            DROP CONSTRAINT FK_A9744CCEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_message
            DROP CONSTRAINT FK_D6FE8DD8F624B39D
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            DROP CONSTRAINT FK_F61948DEA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91F12D3860F
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91FCD53EDB6
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91F7E3C61F9
        ");
        $this->addSql("
            ALTER TABLE claro_log_desktop_widget_config
            DROP CONSTRAINT FK_4AE48D62A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_log_hidden_workspace_widget_config
            DROP CONSTRAINT FK_BC83196EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB3A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT FK_D48CC23EA76ED395
        ");
        $this->addSql("
            ALTER TABLE simple_text_dekstop_widget_config
            DROP CONSTRAINT FK_BAB9695A76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag
            DROP CONSTRAINT FK_C8EFD7EFA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP CONSTRAINT FK_A46B159EA76ED395
        ");
        $this->addSql("
            ALTER TABLE claro_user_group
            DROP CONSTRAINT FK_ED8B34C7FE54D947
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            DROP CONSTRAINT FK_1CBA5A40FE54D947
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91FC6F122B2
        ");
        $this->addSql("
            ALTER TABLE claro_user_role
            DROP CONSTRAINT FK_797E43FFD60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_group_role
            DROP CONSTRAINT FK_1CBA5A40D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            DROP CONSTRAINT FK_92104976D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP CONSTRAINT FK_3848F483D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91FD60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            DROP CONSTRAINT FK_706568A5D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            DROP CONSTRAINT FK_8A8D2F47D60322AC
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF2DE62210
        ");
        $this->addSql("
            ALTER TABLE claro_resource_rights
            DROP CONSTRAINT FK_3848F483B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_activity
            DROP CONSTRAINT FK_E4A67CACB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP CONSTRAINT FK_DCF37C7EB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_file
            DROP CONSTRAINT FK_EA81C80BB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_link
            DROP CONSTRAINT FK_50B267EAB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_directory
            DROP CONSTRAINT FK_12EEC186B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB8158E0B66
        ");
        $this->addSql("
            ALTER TABLE claro_resource_shortcut
            DROP CONSTRAINT FK_5E7F4AB8B87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_text
            DROP CONSTRAINT FK_5D9559DCB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91FB87FAB32
        ");
        $this->addSql("
            ALTER TABLE claro_user
            DROP CONSTRAINT FK_EB8D285282D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_role
            DROP CONSTRAINT FK_3177747182D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_workspace
            DROP CONSTRAINT FK_D9028545727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            DROP CONSTRAINT FK_D012AF0FA08DFE7A
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_aggregation
            DROP CONSTRAINT FK_D012AF0F782B5A3F
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP CONSTRAINT FK_6CF1320E82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_event
            DROP CONSTRAINT FK_B1ADDDB582D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP CONSTRAINT FK_F530F6BE82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab
            DROP CONSTRAINT FK_A9744CCE82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91F82D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_log_workspace_widget_config
            DROP CONSTRAINT FK_D301C70782D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB382D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT FK_D48CC23E82D40A1F
        ");
        $this->addSql("
            ALTER TABLE simple_text_workspace_widget_config
            DROP CONSTRAINT FK_11925ED382D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            DROP CONSTRAINT FK_7883931082D40A1F
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool_role
            DROP CONSTRAINT FK_9210497679732467
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_mask_decoder
            DROP CONSTRAINT FK_39D93F4298EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_menu_action
            DROP CONSTRAINT FK_1F57E52B98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            DROP CONSTRAINT FK_84B4BEBA98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type_custom_action
            DROP CONSTRAINT FK_4A98967B98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_log
            DROP CONSTRAINT FK_97FAB91F98EC6B7B
        ");
        $this->addSql("
            ALTER TABLE claro_list_type_creation
            DROP CONSTRAINT FK_84B4BEBA195FBDF1
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP CONSTRAINT FK_1A2084EFAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP CONSTRAINT FK_1A2084EFE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP CONSTRAINT FK_8D18942E98260155
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP CONSTRAINT FK_D72E133CAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP CONSTRAINT FK_D72E133CE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP CONSTRAINT FK_1A2084EFC54C8C93
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP CONSTRAINT FK_8D18942EAA23F6C8
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP CONSTRAINT FK_8D18942EE9583FF0
        ");
        $this->addSql("
            ALTER TABLE claro_content2type
            DROP CONSTRAINT FK_1A2084EF84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP CONSTRAINT FK_D72E133C2055B9A2
        ");
        $this->addSql("
            ALTER TABLE claro_subcontent
            DROP CONSTRAINT FK_D72E133CDD62C21B
        ");
        $this->addSql("
            ALTER TABLE claro_content2region
            DROP CONSTRAINT FK_8D18942E84A0A3ED
        ");
        $this->addSql("
            ALTER TABLE claro_home_tab_config
            DROP CONSTRAINT FK_F530F6BE7D08FA9E
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT FK_D48CC23E7D08FA9E
        ");
        $this->addSql("
            ALTER TABLE claro_user_message
            DROP CONSTRAINT FK_D48EA38A537A1329
        ");
        $this->addSql("
            ALTER TABLE claro_message
            DROP CONSTRAINT FK_D6FE8DD8727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_activity
            DROP CONSTRAINT FK_DCF37C7E81C06096
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF54B9D732
        ");
        $this->addSql("
            ALTER TABLE claro_resource_icon
            DROP CONSTRAINT FK_478C586179F0D498
        ");
        $this->addSql("
            ALTER TABLE claro_text_revision
            DROP CONSTRAINT FK_F61948DE698D3548
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_platform_roles
            DROP CONSTRAINT FK_706568A5EA675D86
        ");
        $this->addSql("
            ALTER TABLE claro_log_doer_workspace_roles
            DROP CONSTRAINT FK_8A8D2F47EA675D86
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB3FBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_home_tab_config
            DROP CONSTRAINT FK_D48CC23EFBE885E2
        ");
        $this->addSql("
            ALTER TABLE claro_widget_display
            DROP CONSTRAINT FK_2D34DB3727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_resource_type
            DROP CONSTRAINT FK_AEC62693EC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_theme
            DROP CONSTRAINT FK_1D76301AEC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_widget
            DROP CONSTRAINT FK_76CA6C4FEC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_tools
            DROP CONSTRAINT FK_60F90965EC942BCF
        ");
        $this->addSql("
            ALTER TABLE claro_ordered_tool
            DROP CONSTRAINT FK_6CF1320E8F7B22CC
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP CONSTRAINT FK_A46B159EBAD26311
        ");
        $this->addSql("
            ALTER TABLE claro_workspace_tag_hierarchy
            DROP CONSTRAINT FK_A46B159E727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_rel_workspace_tag
            DROP CONSTRAINT FK_78839310BAD26311
        ");
        $this->addSql("
            ALTER TABLE claro_resource_node
            DROP CONSTRAINT FK_A76799FF460F904B
        ");
        $this->addSql("
            ALTER TABLE claro_user_badge
            DROP CONSTRAINT FK_7EBB381FF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_claim
            DROP CONSTRAINT FK_487A496AF7A2C2FC
        ");
        $this->addSql("
            ALTER TABLE claro_badge_translation
            DROP CONSTRAINT FK_849BC831F7A2C2FC
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
