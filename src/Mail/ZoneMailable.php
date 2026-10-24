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

namespace Simplysmart\Mainboard\Mail;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\SentMessage;
use LogicException;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Localization\LocaleRouting;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * E-mail strefy: HTML w layoucie strefy i wersja tekstowa, z tekstami z kaskady strefy (paczka strefy → Core).
 *
 * Mail niesie nazwę strefy, id organizacji i język, a wysyłka i podgląd działają wewnątrz strefy (CurrentZone::within),
 * więc wynik jest ten sam w żądaniu i w workerze kolejki. Widoki: strefa z `mail.views` (`<prefiks>`) dostaje
 * `<prefiks>.layout` (wymagany), `<prefiks>.<szablon>` (HTML) i `<prefiks>.text.<szablon>`, gdy istnieją; brakujące
 * biorą się z Core (`mainboard::mail.layout`, `mainboard::mail.html.<szablon>`, `mainboard::mail.<szablon>`).
 * Widok HTML rozszerza `$mailLayout`; widoki dostają `$mailZone`, `$mailPalette` (MailPalette strefy, `mail.palette`), `$mailLogo` (BrandFile, osadzany przez
 * `$message->embedData`), `$mailOrganization` (nazwa albo null) i `$mailLegalLinks` (list<array{url, label}>).
 */
abstract class ZoneMailable extends Mailable
{
    public const string CORE_LAYOUT = 'mainboard::mail.layout';

    private const string CORE_HTML = 'mainboard::mail.html.';

    private const string CORE_TEXT = 'mainboard::mail.';

    private const string ZONE_LAYOUT = '.layout';

    private const string ZONE_TEXT = '.text.';

    /**
     * @param string $zoneName Strefa, z której idzie mail (jej layout, teksty i odnośniki).
     * @param int|null $organizationId Organizacja, której logo trafia do layoutu ({@see MailOrganization}); null = logo strefy.
     */
    public function __construct(
        public readonly string $zoneName,
        public readonly ?int $organizationId = null,
    ) {
        $this->locale(Container::getInstance()->make(Translator::class)->getLocale());
    }

    /**
     * Nazwa szablonu (`login-code` → `mainboard::mail.html.login-code`).
     */
    abstract protected function template(): string;

    /**
     * Klucz tematu w grupie e-maili, bez namespace (`login_code.subject`).
     */
    abstract protected function subjectKey(): string;

    public function envelope(): Envelope
    {
        return new Envelope(subject: text_email($this->zone()->textKey($this->subjectKey())));
    }

    public function content(): Content
    {
        $zone = $this->zone();
        $container = Container::getInstance();
        $organization = $this->organizationId === null ? null : Organization::query()->find($this->organizationId);

        return new Content(
            html: $this->viewOrCore($zone->mailViews === null ? null : "{$zone->mailViews}.{$this->template()}", self::CORE_HTML . $this->template()),
            text: $this->viewOrCore($zone->mailViews === null ? null : $zone->mailViews . self::ZONE_TEXT . $this->template(), self::CORE_TEXT . $this->template()),
            with: [
                'mailZone' => $zone,
                'mailPalette' => $zone->mailPalette,
                'mailLayout' => $this->layout($zone),
                'mailLogo' => $container->make(MailBrandResolver::class)->logo($zone, $organization),
                'mailOrganization' => $organization?->name,
                'mailLegalLinks' => $this->legalLinks($zone),
            ],
        );
    }

    /**
     * @param \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer $mailer
     */
    public function send($mailer)
    {
        return $this->withinZone(fn(): ?SentMessage => parent::send($mailer));
    }

    public function render()
    {
        return $this->withinZone(fn(): string => parent::render());
    }

    protected function zone(): ZoneDefinition
    {
        return Container::getInstance()->make(ZoneRegistry::class)->get($this->zoneName);
    }

    /**
     * @throws LogicException Gdy strefa ma `mail.views`, a nie ma layoutu `<prefiks>.layout`.
     */
    private function layout(ZoneDefinition $zone): string
    {
        if ($zone->mailViews === null) {
            return self::CORE_LAYOUT;
        }

        $layout = $zone->mailViews . self::ZONE_LAYOUT;

        if (! $this->views()->exists($layout)) {
            throw new LogicException("Zone [{$zone->name}] mail.views needs the layout view [{$layout}].");
        }

        return $layout;
    }

    private function viewOrCore(?string $zoneView, string $coreView): string
    {
        return $zoneView !== null && $this->views()->exists($zoneView) ? $zoneView : $coreView;
    }

    /**
     * @return list<array{url: string, label: string}>
     */
    private function legalLinks(ZoneDefinition $zone): array
    {
        $container = Container::getInstance();
        $url = $container->make(UrlGenerator::class);
        $parameters = $container->make(LocaleRouting::class)->isPrefixed() ? [LocaleRouting::PARAMETER => $this->locale] : [];

        return array_map(static fn(array $link): array => [
            'url' => $url->route($zone->routeName($link['route']), $parameters),
            'label' => text($link['label']),
        ], $zone->legalLinks);
    }

    /**
     * @template TResult
     *
     * @param Closure(): TResult $callback
     * @return TResult
     */
    private function withinZone(Closure $callback): mixed
    {
        return Container::getInstance()->make(CurrentZone::class)->within($this->zone(), static fn(ZoneDefinition $zone): mixed => $callback());
    }

    private function views(): ViewFactory
    {
        return Container::getInstance()->make(ViewFactory::class);
    }
}
