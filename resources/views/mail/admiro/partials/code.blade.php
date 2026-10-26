{{-- Kod w e-mailu strefy admiro: każda cyfra w osobnym polu (jak pola kodu na stronie logowania), tabela ze stylami inline. $code: string; $mailPalette: MailPalette strefy. --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 0 12px;">
    <tr>
        @foreach (mb_str_split($code) as $digit)
            @if (! $loop->first)
                <td style="width: 8px; font-size: 0; line-height: 0;">&nbsp;</td>
            @endif
            <td align="center" style="width: 44px; height: 52px; border: 1px solid {{ $mailPalette->border }}; border-radius: 10px; background-color: {{ $mailPalette->bg }}; color: {{ $mailPalette->text }}; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 24px; font-weight: 700; text-align: center;">{{ $digit }}</td>
        @endforeach
    </tr>
</table>
