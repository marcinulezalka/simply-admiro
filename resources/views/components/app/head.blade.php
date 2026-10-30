{{-- Arkusze i skrypty layoutów aplikacji (x-admiro::app.layout, x-admiro::app.zone-layout) w slocie head szablonu x-admiro::page: arkusze Core (View\CoreAssets::STYLESHEETS) i dodatkowe z public/assets/ (stylesheets), arkusze strefy (zoneStylesheets: ścieżki plików) na końcu i skrypty powłoki aplikacji. --}}
@props(['stylesheets' => [], 'zoneStylesheets' => []])
<x-admiro::styles :files="[...\Simplysmart\Admiro\View\CoreAssets::STYLESHEETS, ...$stylesheets]" />
<x-admiro::zone-styles :paths="$zoneStylesheets" />
<x-admiro::scripts :files="['js/app-shell.js', 'js/toasts.js', 'js/input-mask.js', 'js/pin-keypad.js', 'js/global-search.js', 'js/autosubmit.js', 'js/calendar-panel.js', 'js/tabs.js', 'js/live-search.js', 'js/tree-dnd.js', 'js/file-drop.js', 'js/keep-scroll.js', 'js/view-switch.js', 'js/post-toggle.js']" />
