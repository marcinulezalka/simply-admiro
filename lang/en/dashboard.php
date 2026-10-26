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

    'title' => 'Application status',

    'onboarding_title' => 'Onboarding',
    'onboarding_lead' => 'Go through the setup of your business processes in the recommended order. Each stage lays the foundations for the next features of your system.',
    'onboarding_progress' => ':done of :total done',
    'onboarding_step' => 'Step :number',
    'onboarding_steps' => 'Onboarding steps',
    'onboarding_back' => 'Back',
    'onboarding_next' => 'Step :number',
    'onboarding_confirm' => 'Confirm and continue',
    'onboarding_confirm_last' => 'Confirm and finish',
    'onboarding_open' => 'Go to: :name',
    'onboarding_states' => [
        'done' => 'Done',
        'in_progress' => 'In progress',
        'not_started' => 'Not started',
    ],
    'onboarding_confirmed' => 'Step “:name” confirmed.',
    'onboarding_complete' => 'All setup steps are done.',
    'onboarding_review' => 'Review the steps',
    'onboarding_languages_enabled' => 'Enabled languages',
    'onboarding_languages_enabled_empty' => 'No language is enabled yet.',
    'onboarding_languages_search_title' => 'Find a language',
    'onboarding_languages_search_label' => 'Search for a language',
    'onboarding_languages_search_placeholder' => 'Language or country name, e.g. German, Brazil',
    'onboarding_languages_results' => 'Languages matching “:term”',
    'onboarding_languages_none' => 'No languages match “:term”.',
    'onboarding_languages_hint' => 'Type a language or country name — suggestions appear as you type, and the results show in panels with switches.',
    'onboarding_language_switch' => ':name enabled',
    'onboarding_language_locked' => 'Application or interface language — always enabled',
    'onboarding_language_save' => 'Save',
    'onboarding_installation_title' => 'System installation and hero account',
    'onboarding_installation_lead' => 'Download the system, configure the environment and create the first administrator account (hero). On this server the deployment did this step — the guide helps with further installations.',
    'onboarding_installation_guide_title' => 'Installation guide',
    'onboarding_installation_guide' => [
        'download' => [
            'title' => 'Download the system',
            'text' => 'Clone the application repository, install the PHP dependencies and build the interface assets (PHP 8.4, Composer, Node.js).',
        ],
        'environment' => [
            'title' => 'Configure the environment',
            'text' => 'Create the .env file from the template and generate the application key. Fill in the application address (APP_URL), the database connection (DB_*), the sending mailbox (MAIL_*, with MAIL_PASSWORD) and the languages (APP_LOCALES).',
        ],
        'database' => [
            'title' => 'Prepare the database',
            'text' => 'Create the tables and load the starting data: dictionaries, the operator organization, roles and permissions, and the first policy versions. The commands can be repeated.',
        ],
        'hero' => [
            'title' => 'Create the hero account',
            'text' => 'The first administrator account with full permissions. The administrator sets the PIN at the first sign-in with the e-mailed code.',
        ],
        'deploy' => [
            'title' => 'Deploy to the server',
            'text' => 'One command from the developer machine sends the application to the server, runs the migrations and creates the accounts from infrastructure/deploy.onboarding.env. Check the plan with --dry-run first.',
        ],
    ],
    'onboarding_languages_title' => 'Languages',
    'onboarding_languages_lead' => 'Turn on the languages your company works in — only they appear in choices. By default only the application language and the interface languages are on.',
    'onboarding_settings_title' => 'Regional settings',
    'onboarding_settings_lead' => 'Choose the default country, currency and language, and the interface languages.',
    'onboarding_audit_title' => 'Audit log',
    'onboarding_audit_lead' => 'Look into the log: sign-ins, lockouts, role changes and administrator actions in one place.',
    'onboarding_legal_title' => 'Legal documents',
    'onboarding_legal_lead' => 'Finally, publish the cookie policy, the privacy policy and the GDPR notice before you invite the first users.',
    'onboarding_organizations_title' => 'Organizations',
    'onboarding_organizations_lead' => 'Create client organizations with their company details, contact and address.',
    'onboarding_owners_title' => 'Zone owners',
    'onboarding_owners_lead' => 'Create the zone owner accounts — the owners take their teams from there.',
    'onboarding_administrators_title' => 'Profiles and permissions',
    'onboarding_administrators_lead' => 'Define user roles and the views they can access, then give roles to the administrators.',
    'shortcuts_title' => 'Panel sections',

];
