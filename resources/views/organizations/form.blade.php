{{-- Nowa organizacja albo zmiana organizacji. $organization: ?Organization (null = nowa), $parents: Collection<Organization> (aktywne, bez edytowanej). --}}
<x-mainboard::zone.layout :title="$organization === null ? text('admiro::admin.organization_create') : text_action('edit') . ': ' . $organization->name">
    @error('organization') <p role="alert">{{ $message }}</p> @enderror

    <form method="POST" action="{{ $organization === null ? route('admiro.organizations.store') : route('admiro.organizations.update', ['organization' => $organization->token]) }}">
        @csrf
        @if ($organization !== null)
            @method('PUT')
        @endif

        <label for="name">{{ text('admiro::admin.organization') }}</label>
        <input id="name" name="name" value="{{ old('name', $organization?->name) }}" required maxlength="255">
        @error('name') <p role="alert">{{ $message }}</p> @enderror

        <label for="parent">{{ text('admiro::admin.organization_parent') }}</label>
        <select id="parent" name="parent">
            <option value="">—</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->token }}" @selected(old('parent', $organization?->parent_id === $parent->id ? $parent->token : null) === $parent->token)>{{ $parent->name }}</option>
            @endforeach
        </select>

        <button type="submit">{{ text_action('save') }}</button>
    </form>
</x-mainboard::zone.layout>
