{{-- Ulubione (trasa `favorites`, skrót z sercem w stopce Workspace): strony oznaczone przez administratora jako ulubione; na razie pusta lista z informacją. --}}
<x-admiro::zone.layout :title="text('admiro::navigation.favorites')">
    <p class="ss-notice">{{ text('admiro::admin.favorites_empty') }}</p>
</x-admiro::zone.layout>
