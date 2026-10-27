{{-- Layout HTML e-maili strefy administratorów admiro (`mail.views` = admiro::mail.admiro, kolory `mail.palette` strefy) w motywie sygnetu: karta z zaokrągleniem, u góry pasek w grafitowym gradiencie z koroną wyciętą z lewego dolnego rogu i białym logotypem admiro po prawej, wyśrodkowanym w pionie (jeden obraz PanelServiceProvider::MAIL_HEADER, źródło brand/mail/mail-header.svg — logo w obrazie, bo tło CSS pod obrazem zalałoby wycięcie korony, a Outlook go nie pokazuje), pod nim treść, a na dole karty — na tle strony, oddzielona linią — grupa logotypów ($mailBrandGroup z MailBrandGroups, np. simplysmart · produkt · admiro) z etykietą mono; pod kartą wyśrodkowana stopka (wiadomość automatyczna, odnośniki prawne, prawa autorskie). Tabele i style inline (klienci poczty nie czytają arkuszy); obrazy osadzone w wiadomości (CID), w podglądzie bez wiadomości jako data URI. Szablon treści o nazwie maila obok (np. login-code.blade.php) zastępuje szablon Core. --}}
@php
    $mailImage = static fn (\Simplysmart\Admiro\Mail\BrandFile $file): string => isset($message) ? $message->embedData($file->contents, $file->name, $file->mimeType) : $file->dataUri();
    $mailHeader = \Simplysmart\Admiro\Mail\BrandFile::fromPath(\Simplysmart\Admiro\Panel\PanelServiceProvider::MAIL_HEADER);
    $mailBrandName = $mailOrganization ?? config('app.name');
    $mailMono = "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace";
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $mailBrandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $mailPalette->bg }}; color: {{ $mailPalette->text }}; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; font-size: 15px; line-height: 1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: {{ $mailPalette->bg }};">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px;">
                    <tr>
                        <td style="background-color: {{ $mailPalette->surface }}; border: 1px solid {{ $mailPalette->border }}; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(24, 24, 27, 0.04), 0 12px 32px rgba(24, 24, 27, 0.06);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 0; font-size: 0; line-height: 0;">
                                        <img src="{{ $mailImage($mailHeader) }}" alt="{{ $mailZone->name }}" width="560" style="display: block; width: 100%; max-width: 560px; height: auto; border: 0;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 32px 36px 36px;">
                                        @yield('content')
                                    </td>
                                </tr>
                                @if (($mailBrandGroup ?? []) !== [])
                                    <tr>
                                        <td align="center" style="padding: 20px 36px 22px; border-top: 1px solid {{ $mailPalette->border }}; background-color: {{ $mailPalette->bg }};">
                                            <p style="margin: 0 0 12px; color: {{ $mailPalette->muted }}; font-family: {{ $mailMono }}; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase;">{{ text_email($mailZone->textKey('layout.brand_group')) }}</p>
                                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                                <tr>
                                                    @foreach ($mailBrandGroup as $brand)
                                                        @if (! $loop->first)
                                                            <td style="padding: 0 16px;"><div style="width: 1px; height: 18px; background-color: {{ $mailPalette->border }};"></div></td>
                                                        @endif
                                                        <td style="vertical-align: middle;"><img src="{{ $mailImage($brand->logo) }}" alt="{{ $brand->name }}" height="18" style="display: block; height: 18px; width: auto; border: 0;"></td>
                                                    @endforeach
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 24px 16px 0; color: {{ $mailPalette->muted }}; font-size: 12px; line-height: 1.6; text-align: center;">
                            <p style="margin: 0 0 6px;">{{ text_email($mailZone->textKey('layout.automatic')) }}</p>
                            @if ($mailLegalLinks !== [])
                                <p style="margin: 0 0 6px;">
                                    @foreach ($mailLegalLinks as $link)
                                        <a href="{{ $link['url'] }}" style="color: {{ $mailPalette->muted }}; text-decoration: underline;">{{ $link['label'] }}</a>@if (! $loop->last) &nbsp;·&nbsp; @endif
                                    @endforeach
                                </p>
                            @endif
                            <p style="margin: 0;">{{ text('layout.copyright', ['year' => (string) now()->year]) }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
