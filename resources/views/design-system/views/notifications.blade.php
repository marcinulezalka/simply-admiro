{{-- Gotowy widok: powiadomienia (dzwonek z licznikiem i panelem w top barze, pełna lista z zakładkami) i toasty wszystkich typów na dole ekranu (Enums\ToastType: kolor, ikona, nazwa; błąd zostaje do zamknięcia), z legendą znaczeń kolorów. $view: DesignSystemView. --}}
@php
    use Simplysmart\Admiro\Enums\DesignSystemView;
    use Simplysmart\Admiro\Enums\ToastType;
    use Simplysmart\Admiro\View\Demo\DemoNavigation;
    use Simplysmart\Admiro\View\Demo\DemoNotifications;
    use Simplysmart\Admiro\View\Toast;
    $here = route('simplysmart.design-system.view', DesignSystemView::Notifications);
    $panel = DemoNotifications::panel($here);
@endphp
<x-admiro::app.layout
    :title="text('design_system.demo.notifications.title')"
    :meta="text('design_system.demo.notifications.meta', ['count' => local_number($panel->unreadCount())])"
    :navigation="DemoNavigation::groups($view)"
    :search="DemoNavigation::search()"
    :account="DemoNavigation::account()"
    :contexts="DemoNavigation::contexts($view)"
    :notifications="$panel"
    :toasts="DemoNotifications::toasts(route('simplysmart.design-system.view', DesignSystemView::Record))"
    :stylesheets="['css/design-system.css']"
>
    <x-slot:before>
        <x-admiro::design-system.strip :current="$view->value" />
    </x-slot:before>

    <x-slot:actions>
        <button class="ss-button" type="button"><x-admiro::icon name="check" class="ss-button__icon" />{{ text('layout.mark_all_read') }}</button>
    </x-slot:actions>

    <nav aria-label="{{ text('design_system.demo.notifications.title') }}">
        <ul class="ss-tabs">
            <li><a class="ss-tabs__link" href="{{ $here }}" aria-current="page">{{ text('design_system.demo.notifications.all') }}</a></li>
            <li><a class="ss-tabs__link" href="{{ $here }}">{{ text('design_system.demo.notifications.unread') }} <span class="ss-badge">{{ local_number($panel->unreadCount()) }}</span></a></li>
        </ul>
    </nav>

    <section class="ss-card">
        <ul class="ss-notification-list">
            @foreach ($panel->items as $item)
                <li><x-admiro::notification :item="$item" /></li>
            @endforeach
        </ul>
    </section>

    <section class="ss-card ss-card--tinted">
        <h2 class="ss-card__title">{{ text('layout.toasts') }}</h2>
        <p class="ss-card__lead ss-muted">{{ text('design_system.demo.toasts.legend') }}</p>
        <ul class="ss-list">
            @foreach (ToastType::cases() as $type)
                <li class="ss-list__item">
                    <span class="ss-badge"><x-admiro::icon :name="$type->icon()" class="ss-badge__icon" />{{ $type->label() }}</span>
                    <span class="ss-muted"><code>--ss-color-{{ $type->value }}</code> · {{ text('design_system.demo.toasts.meaning.' . $type->value) }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</x-admiro::app.layout>
