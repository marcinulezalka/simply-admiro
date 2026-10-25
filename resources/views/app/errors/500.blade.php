{{-- Strona błędu 500 (Core; szkielet nadpisuje plikiem resources/views/errors/500.blade.php): akcja „Spróbuj ponownie” zamiast powrotu. --}}
<x-mainboard::error.layout code="500" :retry="true" />
