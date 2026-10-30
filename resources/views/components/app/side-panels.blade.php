{{-- Data w stopce layoutu stref (x-admiro::app.zone-layout) z dwoma panelami bocznymi jak harmonijka (<details data-ss-dropdown data-ss-side-panels>, js/calendar-panel.js): przycisk-zegar (x-admiro::app.clock) wysuwa od prawej krawędzi okna najpierw kalendarz (przy krawędzi, na wierzchu: miesiąc x-admiro::app.month, na dole ikony wyglądu w siatce bez podpisów — x-admiro::app.appearance-tools grid), a potem z jego lewej krawędzi powiadomienia (notifications: ?View\Notifications\NotificationPanel — zakładki x-admiro::app.notification-tabs z komunikatami systemowymi toasts: ?list<View\Toast>; z tasks: ?View\Tasks\TaskPanel panel dzieli się w pionie — powiadomienia ok. 60% wysokości, pod nimi lista zadań TODO konta x-admiro::app.tasks ok. 40%); panel powiadomień jest pod kalendarzem i wsunięty pod jego krawędź, więc między panelami nie ma przerwy. Każdy panel zamyka się osobno (×): po zamknięciu kalendarza powiadomienia przesuwają się do krawędzi okna (z marginesem), po zamknięciu obu — albo ponownym kliknięciu daty — zamyka się całość. Bez skryptu oba panele są widoczne obok siebie. calendar: View\Calendar\CalendarMonth; settingsUrl: ?string (koło zębate w narzędziach). --}}
@props(['calendar', 'notifications' => null, 'toasts' => null, 'tasks' => null, 'settingsUrl' => null])
<details class="ss-calendar-panel ss-side-panels" data-ss-dropdown data-ss-side-panels>
    <summary class="ss-calendar-panel__toggle" data-ss-clock>
        <x-admiro::app.clock :unread="$notifications?->unreadCount() ?? 0" />
    </summary>
    <aside class="ss-side-panels__panel ss-side-panels__panel--calendar ss-side-panel" aria-label="{{ text('layout.calendar') }}" data-ss-calendar data-ss-side-panel="calendar">
        <div class="ss-side-panels__header">
            <x-admiro::icon name="calendar" class="ss-icon--tile" />
            <h2 class="ss-side-panels__title">{{ ucfirst(local_month($calendar->month, 'MMMM')) }} {{ $calendar->month->year }}</h2>
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-side-panel-close hidden>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <div class="ss-side-panel__body">
            <x-admiro::app.month :calendar="$calendar" />
        </div>
        <x-admiro::app.appearance-tools :settings-url="$settingsUrl" grid />
    </aside>
    @if ($notifications !== null)
        <aside class="ss-side-panels__panel ss-side-panels__panel--notifications ss-side-panel" aria-label="{{ text('layout.notifications') }}" data-ss-side-panel="notifications">
            <div class="ss-side-panels__header">
                <x-admiro::icon name="bell" class="ss-icon--tile" />
                <h2 class="ss-side-panels__title">{{ text('layout.notifications') }}</h2>
                <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-side-panel-close hidden>
                    <x-admiro::icon name="close" class="ss-icon-button__icon" />
                    <span class="ss-sr-only">{{ text('layout.close') }}</span>
                </button>
            </div>
            @if ($tasks !== null)
                <div class="ss-side-panels__split">
                    <div class="ss-side-panels__split-top">
                        <x-admiro::app.notification-tabs :notifications="$notifications" :toasts="$toasts" />
                    </div>
                    <div class="ss-side-panels__split-bottom">
                        <x-admiro::app.tasks :tasks="$tasks" />
                    </div>
                </div>
            @else
                <x-admiro::app.notification-tabs :notifications="$notifications" :toasts="$toasts" />
            @endif
        </aside>
    @endif
</details>
