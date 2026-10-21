<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Database\Seeders;

use Locale;
use NumberFormatter;
use ResourceBundle;

/**
 * Dane referencyjne lokalizacji dla seederów: lista krajów z numerami kierunkowymi, członkowie UE,
 * stawki VAT państw UE oraz odczyty z ICU (rozszerzenie intl): nazwy, waluty i języki urzędowe krajów.
 */
final class LocalizationData
{
    /**
     * Waluty włączone od startu, w tej kolejności (sort_order).
     *
     * @var list<string>
     */
    public const array ACTIVE_CURRENCIES = ['PLN', 'EUR', 'USD', 'GBP', 'CHF'];

    /**
     * Języki włączone od startu obok języka aplikacji i języków interfejsu.
     *
     * @var list<string>
     */
    public const array ACTIVE_LANGUAGES = ['en'];

    /**
     * Kraje na początku list wyboru, w tej kolejności (sort_order); pozostałe po nich.
     *
     * @var list<string>
     */
    public const array LEADING_COUNTRIES = ['PL', 'DE', 'CZ', 'SK', 'UA', 'LT', 'GB', 'US', 'NL', 'FR', 'IT', 'ES', 'AT', 'IE', 'NO', 'SE'];

    /**
     * sort_order wpisów spoza list ACTIVE_CURRENCIES i LEADING_COUNTRIES.
     */
    public const int OTHER_SORT_ORDER = 1000;

    /**
     * Państwa członkowskie Unii Europejskiej.
     *
     * @var list<string>
     */
    public const array EU = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

    /**
     * Stawki startowe VAT (stan na 2025 r.): kraj => podstawowa, obniżone (0 = zerowa), data początku.
     *
     * @var array<string, array{standard: float|int, reduced: list<float|int>, from?: string}>
     */
    public const array VAT_RATES = [
        'AT' => ['standard' => 20, 'reduced' => [13, 10]],
        'BE' => ['standard' => 21, 'reduced' => [12, 6]],
        'BG' => ['standard' => 20, 'reduced' => [9]],
        'CY' => ['standard' => 19, 'reduced' => [9, 5]],
        'CZ' => ['standard' => 21, 'reduced' => [12]],
        'DE' => ['standard' => 19, 'reduced' => [7]],
        'DK' => ['standard' => 25, 'reduced' => []],
        'EE' => ['standard' => 24, 'reduced' => [13, 9, 0], 'from' => '2025-07-01'],
        'ES' => ['standard' => 21, 'reduced' => [10, 4]],
        'FI' => ['standard' => 25.5, 'reduced' => [13.5, 10]],
        'FR' => ['standard' => 20, 'reduced' => [10, 5.5, 2.1]],
        'GR' => ['standard' => 24, 'reduced' => [13, 6]],
        'HR' => ['standard' => 25, 'reduced' => [13, 5]],
        'HU' => ['standard' => 27, 'reduced' => [18, 5]],
        'IE' => ['standard' => 23, 'reduced' => [13.5, 9, 4.8, 0]],
        'IT' => ['standard' => 22, 'reduced' => [10, 5, 4]],
        'LT' => ['standard' => 21, 'reduced' => [9, 5]],
        'LU' => ['standard' => 17, 'reduced' => [14, 8, 3]],
        'LV' => ['standard' => 21, 'reduced' => [12, 5]],
        'MT' => ['standard' => 18, 'reduced' => [7, 5, 0]],
        'NL' => ['standard' => 21, 'reduced' => [9, 0]],
        'PL' => ['standard' => 23, 'reduced' => [8, 5, 0], 'from' => '2011-01-01'],
        'PT' => ['standard' => 23, 'reduced' => [13, 6]],
        'RO' => ['standard' => 21, 'reduced' => [11], 'from' => '2025-08-01'],
        'SE' => ['standard' => 25, 'reduced' => [12, 6]],
        'SI' => ['standard' => 22, 'reduced' => [9.5, 5]],
        'SK' => ['standard' => 23, 'reduced' => [19, 5]],
    ];

    public const string VAT_RATES_FROM = '2025-01-01';

    public const string VAT_RATES_NOTE = 'Stawka startowa (stan na 2025 r.) — do weryfikacji w bazie TEDB Komisji Europejskiej.';

    /**
     * Kraj, którego stawki są kompletne (z „zw”) i nie wymagają noty weryfikacyjnej.
     */
    public const string VAT_VERIFIED_COUNTRY = 'PL';

    /**
     * Kraje (ISO 3166-1 alpha-2) z numerami kierunkowymi.
     *
     * @var array<string, string>
     */
    private const array DIAL_CODES = [
        'AD' => '+376', 'AE' => '+971', 'AF' => '+93', 'AG' => '+1268', 'AI' => '+1264', 'AL' => '+355', 'AM' => '+374',
        'AO' => '+244', 'AR' => '+54', 'AS' => '+1684', 'AT' => '+43', 'AU' => '+61', 'AW' => '+297', 'AX' => '+358',
        'AZ' => '+994', 'BA' => '+387', 'BB' => '+1246', 'BD' => '+880', 'BE' => '+32', 'BF' => '+226', 'BG' => '+359',
        'BH' => '+973', 'BI' => '+257', 'BJ' => '+229', 'BL' => '+590', 'BM' => '+1441', 'BN' => '+673', 'BO' => '+591',
        'BQ' => '+599', 'BR' => '+55', 'BS' => '+1242', 'BT' => '+975', 'BW' => '+267', 'BY' => '+375', 'BZ' => '+501',
        'CA' => '+1', 'CC' => '+61', 'CD' => '+243', 'CF' => '+236', 'CG' => '+242', 'CH' => '+41', 'CI' => '+225',
        'CK' => '+682', 'CL' => '+56', 'CM' => '+237', 'CN' => '+86', 'CO' => '+57', 'CR' => '+506', 'CU' => '+53',
        'CV' => '+238', 'CW' => '+599', 'CX' => '+61', 'CY' => '+357', 'CZ' => '+420', 'DE' => '+49', 'DJ' => '+253',
        'DK' => '+45', 'DM' => '+1767', 'DO' => '+1809', 'DZ' => '+213', 'EC' => '+593', 'EE' => '+372', 'EG' => '+20',
        'EH' => '+212', 'ER' => '+291', 'ES' => '+34', 'ET' => '+251', 'FI' => '+358', 'FJ' => '+679', 'FK' => '+500',
        'FM' => '+691', 'FO' => '+298', 'FR' => '+33', 'GA' => '+241', 'GB' => '+44', 'GD' => '+1473', 'GE' => '+995',
        'GF' => '+594', 'GG' => '+44', 'GH' => '+233', 'GI' => '+350', 'GL' => '+299', 'GM' => '+220', 'GN' => '+224',
        'GP' => '+590', 'GQ' => '+240', 'GR' => '+30', 'GT' => '+502', 'GU' => '+1671', 'GW' => '+245', 'GY' => '+592',
        'HK' => '+852', 'HN' => '+504', 'HR' => '+385', 'HT' => '+509', 'HU' => '+36', 'ID' => '+62', 'IE' => '+353',
        'IL' => '+972', 'IM' => '+44', 'IN' => '+91', 'IO' => '+246', 'IQ' => '+964', 'IR' => '+98', 'IS' => '+354',
        'IT' => '+39', 'JE' => '+44', 'JM' => '+1876', 'JO' => '+962', 'JP' => '+81', 'KE' => '+254', 'KG' => '+996',
        'KH' => '+855', 'KI' => '+686', 'KM' => '+269', 'KN' => '+1869', 'KP' => '+850', 'KR' => '+82', 'KW' => '+965',
        'KY' => '+1345', 'KZ' => '+7', 'LA' => '+856', 'LB' => '+961', 'LC' => '+1758', 'LI' => '+423', 'LK' => '+94',
        'LR' => '+231', 'LS' => '+266', 'LT' => '+370', 'LU' => '+352', 'LV' => '+371', 'LY' => '+218', 'MA' => '+212',
        'MC' => '+377', 'MD' => '+373', 'ME' => '+382', 'MF' => '+590', 'MG' => '+261', 'MH' => '+692', 'MK' => '+389',
        'ML' => '+223', 'MM' => '+95', 'MN' => '+976', 'MO' => '+853', 'MP' => '+1670', 'MQ' => '+596', 'MR' => '+222',
        'MS' => '+1664', 'MT' => '+356', 'MU' => '+230', 'MV' => '+960', 'MW' => '+265', 'MX' => '+52', 'MY' => '+60',
        'MZ' => '+258', 'NA' => '+264', 'NC' => '+687', 'NE' => '+227', 'NF' => '+672', 'NG' => '+234', 'NI' => '+505',
        'NL' => '+31', 'NO' => '+47', 'NP' => '+977', 'NR' => '+674', 'NU' => '+683', 'NZ' => '+64', 'OM' => '+968',
        'PA' => '+507', 'PE' => '+51', 'PF' => '+689', 'PG' => '+675', 'PH' => '+63', 'PK' => '+92', 'PL' => '+48',
        'PM' => '+508', 'PR' => '+1787', 'PS' => '+970', 'PT' => '+351', 'PW' => '+680', 'PY' => '+595', 'QA' => '+974',
        'RE' => '+262', 'RO' => '+40', 'RS' => '+381', 'RU' => '+7', 'RW' => '+250', 'SA' => '+966', 'SB' => '+677',
        'SC' => '+248', 'SD' => '+249', 'SE' => '+46', 'SG' => '+65', 'SH' => '+290', 'SI' => '+386', 'SJ' => '+47',
        'SK' => '+421', 'SL' => '+232', 'SM' => '+378', 'SN' => '+221', 'SO' => '+252', 'SR' => '+597', 'SS' => '+211',
        'ST' => '+239', 'SV' => '+503', 'SX' => '+1721', 'SY' => '+963', 'SZ' => '+268', 'TC' => '+1649', 'TD' => '+235',
        'TG' => '+228', 'TH' => '+66', 'TJ' => '+992', 'TK' => '+690', 'TL' => '+670', 'TM' => '+993', 'TN' => '+216',
        'TO' => '+676', 'TR' => '+90', 'TT' => '+1868', 'TV' => '+688', 'TW' => '+886', 'TZ' => '+255', 'UA' => '+380',
        'UG' => '+256', 'US' => '+1', 'UY' => '+598', 'UZ' => '+998', 'VA' => '+379', 'VC' => '+1784', 'VE' => '+58',
        'VG' => '+1284', 'VI' => '+1340', 'VN' => '+84', 'VU' => '+678', 'WF' => '+681', 'WS' => '+685', 'XK' => '+383',
        'YE' => '+967', 'YT' => '+262', 'ZA' => '+27', 'ZM' => '+260', 'ZW' => '+263',
    ];

    /**
     * Nazwy, których ICU nie zna (kod XK nie jest jeszcze w ISO 3166-1).
     *
     * @var array<string, array<string, string>>
     */
    private const array COUNTRY_NAMES = ['XK' => ['pl' => 'Kosowo', 'en' => 'Kosovo']];

    /**
     * @return list<string>
     */
    public static function countryCodes(): array
    {
        return array_keys(self::DIAL_CODES);
    }

    public static function dialCode(string $country): ?string
    {
        return self::DIAL_CODES[$country] ?? null;
    }

    public static function countryName(string $country, string $locale): string
    {
        return self::COUNTRY_NAMES[$country][$locale] ?? (Locale::getDisplayRegion('und_' . $country, $locale) ?: $country);
    }

    public static function languageName(string $language, string $locale): string
    {
        return Locale::getDisplayLanguage($language, $locale) ?: $language;
    }

    public static function currencyName(string $currency, string $locale): string
    {
        $names = ResourceBundle::create($locale, 'ICUDATA-curr')?->get('Currencies');
        $entry = $names instanceof ResourceBundle ? $names->get($currency) : null;
        $name = $entry instanceof ResourceBundle ? $entry->get(1) : null;

        return is_string($name) ? $name : $currency;
    }

    /**
     * Liczba cyfr jednostki podrzędnej waluty (2 dla PLN, 0 dla JPY).
     */
    public static function currencyDecimals(string $currency): int
    {
        return (new NumberFormatter('en@currency=' . $currency, NumberFormatter::CURRENCY))->getAttribute(NumberFormatter::FRACTION_DIGITS);
    }

    /**
     * Waluta używana dziś w kraju (mapa walut ICU: wpis bez daty końca, będący prawnym środkiem płatniczym).
     */
    public static function currencyOf(string $country): ?string
    {
        $map = ResourceBundle::create('supplementalData', 'ICUDATA-curr', false)?->get('CurrencyMap');
        $entries = $map instanceof ResourceBundle ? $map->get($country) : null;

        if (! $entries instanceof ResourceBundle) {
            return null;
        }

        foreach ($entries as $entry) {
            if ($entry instanceof ResourceBundle && $entry->get('to') === null && $entry->get('tender') !== 'false') {
                $code = $entry->get('id');

                return is_string($code) ? $code : null;
            }
        }

        return null;
    }

    /**
     * Kody walut używanych dziś przez kraje z listy, główne (ACTIVE_CURRENCIES) najpierw.
     *
     * @return list<string>
     */
    public static function currencyCodes(): array
    {
        $codes = self::ACTIVE_CURRENCIES;

        foreach (self::countryCodes() as $country) {
            $codes[] = self::currencyOf($country);
        }

        return array_values(array_unique(array_filter($codes, is_string(...))));
    }

    /**
     * Języki urzędowe kraju (urzędowe lub de facto w danych ICU), od najczęściej używanego;
     * warianty pisma („sr_Latn”) liczą się jako jeden język.
     *
     * @return list<string>
     */
    public static function officialLanguagesOf(string $country): array
    {
        $territories = ResourceBundle::create('supplementalData', 'ICUDATA', false)?->get('territoryInfo');
        $info = $territories instanceof ResourceBundle ? $territories->get($country) : null;
        $languages = [];

        if (! $info instanceof ResourceBundle) {
            return [];
        }

        foreach ($info as $code => $entry) {
            if (! $entry instanceof ResourceBundle || ! in_array($entry->get('officialStatus'), ['official', 'de_facto_official'], true)) {
                continue;
            }

            $language = strtolower(explode('_', is_scalar($code) ? (string) $code : '')[0]);
            $share = $entry->get('populationShareF');
            $languages[$language] = max($languages[$language] ?? 0.0, is_numeric($share) ? (float) $share : 0.0);
        }

        arsort($languages);

        return array_map(strval(...), array_keys($languages));
    }

    /**
     * Kody języków urzędowych wszystkich krajów z listy.
     *
     * @return list<string>
     */
    public static function languageCodes(): array
    {
        $codes = [];

        foreach (self::countryCodes() as $country) {
            array_push($codes, ...self::officialLanguagesOf($country));
        }

        return array_values(array_unique($codes));
    }

    /**
     * Procent w zapisie danego języka: „5,5%” (pl), „5.5%” (en).
     */
    public static function percentLabel(float|int $rate, string $locale): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        return $formatter->format($rate) . '%';
    }
}
