<x-admiro::layout :title="text('admiro::dashboard.title')">
    <p>{{ text('admiro::dashboard.signed_in_as', ['name' => $name]) }}</p>
</x-admiro::layout>
