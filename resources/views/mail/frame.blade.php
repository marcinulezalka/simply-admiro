{{-- Rama HTML e-maili stref (ZoneMailable): tabele i style inline, bo klienci poczty nie czytają arkuszy ani tokenów --ss-*. Layout (Core `admiro::mail.layout` albo `<prefiks>.layout` strefy) dołącza ją; kolory z $mailPalette (MailPalette strefy, `mail.palette`). Logo $mailLogo (BrandFile) osadzone w wiadomości (CID), w podglądzie bez wiadomości jako data URI. $mailZone: ZoneDefinition, $mailOrganization: ?string, $mailLegalLinks: list<array{url: string, label: string}>; treść w sekcji `content`. --}}
@php
    $mailLogoSrc = isset($message) ? $message->embedData($mailLogo->contents, $mailLogo->name, $mailLogo->mimeType) : $mailLogo->dataUri();
    $mailBrandName = $mailOrganization ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $mailBrandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $mailPalette->bg }}; color: {{ $mailPalette->text }}; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; font-size: 16px; line-height: 1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: {{ $mailPalette->bg }};">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px;">
                    <tr>
                        <td style="padding: 0 8px 24px;">
                            <img src="{{ $mailLogoSrc }}" alt="{{ $mailBrandName }}" height="40" style="display: block; height: 40px; width: auto; border: 0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: {{ $mailPalette->surface }}; border: 1px solid {{ $mailPalette->border }}; border-radius: 12px; padding: 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 8px 0; color: {{ $mailPalette->muted }}; font-size: 13px; line-height: 1.5;">
                            <p style="margin: 0 0 8px;">{{ text_email($mailZone->textKey('layout.automatic')) }}</p>
                            @if ($mailLegalLinks !== [])
                                <p style="margin: 0 0 8px;">
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
