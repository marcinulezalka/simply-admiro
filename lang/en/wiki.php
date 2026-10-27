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

/*
 * Glossary (Wiki\Wiki, x-admiro::wiki-tip): <key>.term — keyword, .definition — the system's definition,
 * .example — an example (optional). A package or the application may override a term with its own definition.
 */
return [
    'label' => 'Wiki',
    'example' => 'Example',
    'title' => 'Wiki — glossary',
    'lead' => 'The system’s terms in one place: definitions, examples and answers to common questions. Every entry has a wiki-… tag you can link to.',
    'search_label' => 'Search the glossary',
    'search_placeholder' => 'Term, definition or question',
    'none' => 'No terms match “:term”.',
    'organizacja' => [
        'term' => 'Organization',
        'definition' => 'A company or one of its units where zone accounts work. An organization has its own data, members and roles, and organizations form a tree: company → branches → departments. A disabled organization stays in the system with its data; only its members cannot work in it.',
        'example' => 'Nowak Medical Group (company) → North Branch (a unit with its own data) → Cardiology clinic (a department of the branch).',
    ],
    'organizacja-operatora' => [
        'term' => 'Operator organization',
        'question' => 'Can the operator organization be disabled or moved?',
        'definition' => 'The organization that runs the application. Always active and at the top of the tree; administrators and zones without customer organizations work in it.',
        'example' => 'A company deploying the system for its customers — its administrator accounts belong to no customer organization.',
    ],
    'departament' => [
        'term' => 'Department',
        'question' => 'How does a department differ from a branch?',
        'definition' => 'A division of a unit: a separate work context with its own members and roles, but without its own data — the data belongs to the unit. It always sits directly under the unit and has no organizations under it.',
        'example' => 'The cardiology clinic in the North Branch: its doctor works in its context, and the patients are the branch’s patients.',
    ],
    'strefa' => [
        'term' => 'Zone',
        'question' => 'Can one person work in several zones?',
        'definition' => 'A separate part of the application with its own accounts, sign-in, session and permissions. One organization may have members from several zones, but zones never share accounts.',
        'example' => 'The administrators’ zone and the staff zone: the same person has two independent accounts and signs in to each separately.',
    ],
    'wlasciciel' => [
        'term' => 'Owner',
        'definition' => 'A zone account with the owner role in an organization: it manages the organization in its zone and adds further members with roles. An administrator creates it.',
        'example' => 'An administrator creates the owner for the North Branch, and the owner adds doctors and reception.',
    ],
];
