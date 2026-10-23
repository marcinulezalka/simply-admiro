<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

return [

    'zones_title' => 'Zones and accounts',
    'owner_title' => 'Zone owner',
    'administrators_title' => 'Administrators',
    'no_sections' => 'Your account has no permissions for any section of the panel yet.',
    'zone' => 'Zone',
    'zone_slug' => 'Address',
    'zone_teams' => 'customer organizations',
    'yes' => 'yes',
    'no' => 'no',
    'accounts_title' => 'Accounts of zone :zone',
    'search_email' => 'E-mail address',
    'account_name' => 'Full name',
    'account_email' => 'E-mail address',
    'account_state' => 'State',
    'account_last_login' => 'Last sign-in',
    'account_actions' => 'Actions',
    'no_accounts' => 'No accounts.',
    'state_active' => 'active',
    'state_inactive' => 'disabled',
    'state_locked' => 'locked',
    'state_no_pin' => 'no PIN',
    'own_account' => 'Your own account is not changed in the panel; use the account settings.',
    'owner_intro' => 'The owner account is created in the target zone (or gets the role if the address already has an account). The owner sets a PIN at the first sign-in.',
    'organization' => 'Organization',
    'organization_operator' => '— operator organization (zones without customer organizations) —',
    'owner_organization_required' => 'This zone works in customer organizations: choose the organization.',
    'owner_created' => 'Owner created.',
    'no_provisioners' => 'No zone offers owner provisioning.',
    'roles' => 'Roles',
    'role' => 'Role',
    'role_assign' => 'Assign role',
    'role_revoke' => 'Revoke',
    'role_denied' => 'You cannot change this role: it grants more than you have.',
    'role_assigned' => 'Role assigned.',
    'role_revoked' => 'Role revoked; the account sessions were signed out.',
    'organizations_title' => 'Organizations',
    'organization_create' => 'New organization',
    'organization_parent' => 'Parent organization',
    'organization_enable' => 'Enable',
    'members' => 'Members',
    'members_title' => 'Members of :organization',
    'member_remove' => 'Remove from organization',
    'member_removed' => 'Member removed from the organization; their sessions were signed out.',
    'no_members' => 'No members.',
    'organization_created' => 'Organization created.',
    'organization_updated' => 'Organization saved.',
    'organization_enabled' => 'Organization enabled.',
    'organization_disabled' => 'Organization disabled: its members have no permissions in it.',
    'organization_rejected' => 'The organization cannot be changed like this (tree cycle, disabled parent or the operator organization).',
    'action' => [
        'reset-pin' => 'Reset PIN',
        'unlock' => 'Unlock',
        'deactivate' => 'Disable',
    ],
    'action_done' => [
        'reset-pin' => 'PIN removed: the owner will set a new one after the e-mailed code.',
        'unlock' => 'Account unlocked.',
        'deactivate' => 'Account disabled; its sessions were signed out.',
    ],

];
