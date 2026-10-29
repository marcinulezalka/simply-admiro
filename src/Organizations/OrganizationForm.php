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

namespace Simplysmart\Admiro\Organizations;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Rules\Nip;

/**
 * Walidacja formularza danych organizacji (widok `admiro::organizations.fields`), wspólna dla paneli stref:
 * nazwa, nazwa skrócona, NIP z sumą kontrolną, REGON (9 albo 14 cyfr), KRS (10 cyfr), kod pocztowy 00-000, adresy
 * e-mail, kraj adresu i kraje prefiksów telefonów spośród aktywnych krajów (ContactCountries), telefony z cyfr i spacji
 * (zapisywane z prefiksem kraju) i długości kolumn z Organization::DETAIL_LENGTHS. Puste pola znaczą brak wartości.
 */
final readonly class OrganizationForm
{
    private const int NAME_MAX_LENGTH = 255;

    /** Najdłuższy numer bez prefiksu (kolumna mieści prefiks, spację i numer). */
    private const int PHONE_NUMBER_MAX_LENGTH = 24;

    public function __construct(
        private ContactCountries $countries,
    ) {}

    /**
     * @throws ValidationException
     */
    public function validate(Request $request): OrganizationData
    {
        return $this->data($request->validate($this->rules()));
    }

    /**
     * Dane z wyniku walidacji z rules() (panel może dołożyć własne pola do tej samej walidacji).
     *
     * @param array<array-key, mixed> $validated
     */
    public function data(array $validated): OrganizationData
    {
        $details = [];

        foreach (array_keys(Organization::DETAIL_LENGTHS) as $column) {
            $value = self::filled($validated[$column] ?? null);

            if ($value !== null) {
                $details[$column] = match (true) {
                    $column === Organization::NIP => Nip::normalize($value),
                    in_array($column, Organization::PHONES, true) => $this->countries->compose(self::filled($validated[ContactCountries::prefixField($column)] ?? null), $value),
                    default => $value,
                };
            }
        }

        return new OrganizationData(self::filled($validated['name'] ?? null) ?? '', self::filled($validated[Organization::SHORT_NAME] ?? null), $details);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:' . self::NAME_MAX_LENGTH],
            Organization::SHORT_NAME => ['nullable', 'string', 'max:' . Organization::SHORT_NAME_MAX_LENGTH],
        ];

        $codes = $this->countries->codes();

        foreach (Organization::PHONES as $phone) {
            $rules[ContactCountries::prefixField($phone)] = ['nullable', 'string', Rule::in($codes)];
        }

        foreach (Organization::DETAIL_LENGTHS as $column => $length) {
            $rules[$column] = ['nullable', 'string', ...match ($column) {
                Organization::NIP => ['max:20', new Nip()],
                Organization::REGON => ['regex:/^(\d{9}|\d{14})$/'],
                Organization::KRS => ['regex:/^\d{10}$/'],
                Organization::POSTAL_CODE => ['regex:/^\d{2}-\d{3}$/'],
                Organization::EMAIL, Organization::BILLING_EMAIL => ['email:rfc', 'max:' . $length],
                Organization::PHONE, Organization::MOBILE_PHONE => ['regex:/^\+?[0-9][0-9 ()-]*$/', 'max:' . self::PHONE_NUMBER_MAX_LENGTH],
                Organization::COUNTRY_CODE => [Rule::in($codes)],
                default => ['max:' . $length],
            }];
        }

        return $rules;
    }

    private static function filled(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
