{{-- Lista zadań TODO konta pod powiadomieniami w panelu bocznym layoutu stref (x-admiro::app.side-panels): nagłówek z liczbą niezrealizowanych zadań, pod nim przełącznik dwóch obszarów (.ss-tabs--slider, js/tabs.js; bez skryptu oba jeden pod drugim): „Niezrealizowane” (otwarte i w toku, View\Tasks\TaskPanel::pending()) i „Do sprawdzenia i wdrożone” (do sprawdzenia z linkami do commitów i ostatnio wykonane, delivered()), a w nich zadania zapisane komendą `todo:` z menu błyskawicy jak zgłoszenia — pole wyboru wykonania (formularz PATCH wysyłany od razu po zmianie, js/autosubmit.js; bez skryptu przycisk), numer zgłoszenia krojem mono, nazwa i plakietka stanu, pod nimi tytuł, opis, linki do commitów (zadanie do sprawdzenia; podpis repozytorium@skrót, nowa karta) oraz projekt, klient, termin i opóźnienie, gdy są; wykonane przekreślone w drugim obszarze. Bez zadań — komunikat z podpowiedzią komendy. Lista stoi też w sidebarze wiki (x-admiro::app.sidebar-wiki), więc identyfikatory nagłówka i pól zawierają zakres (scope), a js/command-box.js podmienia każdą listę [data-ss-tasks] na odświeżoną w jej zakresie. tasks: View\Tasks\TaskPanel; scope: string (domyślnie panel). --}}
@props(['tasks', 'scope' => 'panel'])
@php
    $areas = [
        'pending' => ['items' => $tasks->pending(), 'icon' => 'list', 'label' => text('layout.tasks_pending'), 'empty' => text('layout.tasks_empty')],
        'delivered' => ['items' => $tasks->delivered(), 'icon' => 'check-circle', 'label' => text('layout.tasks_delivered'), 'empty' => text('layout.tasks_delivered_empty')],
    ];
@endphp
<section class="ss-tasks" aria-labelledby="ss-tasks-{{ $scope }}-heading" data-ss-tasks="{{ $scope }}">
    <h3 class="ss-notifications__heading ss-tasks__heading" id="ss-tasks-{{ $scope }}-heading">
        <x-admiro::icon name="check" class="ss-tasks__heading-icon" />{{ text('layout.tasks') }}
        <span class="ss-tasks__count">{{ $tasks->openCount() }}</span>
    </h3>
    <div class="ss-tasks__areas" data-ss-tabs>
        <div class="ss-tabs ss-tabs--slider ss-tasks__tabs" role="tablist" aria-label="{{ text('layout.tasks') }}" hidden data-ss-tabs-list>
            @foreach ($areas as $key => $area)
                <button class="ss-tabs__link" type="button" role="tab" id="ss-tasks-{{ $scope }}-tab-{{ $key }}" aria-controls="ss-tasks-{{ $scope }}-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-ss-tab>
                    <x-admiro::icon :name="$area['icon']" class="ss-tabs__icon" /><span class="ss-tasks__tab-label">{{ $area['label'] }}</span> <span class="ss-tasks__tab-count">{{ count($area['items']) }}</span>
                </button>
            @endforeach
        </div>
        @foreach ($areas as $key => $area)
            <div class="ss-tasks__area" id="ss-tasks-{{ $scope }}-{{ $key }}" role="tabpanel" aria-labelledby="ss-tasks-{{ $scope }}-tab-{{ $key }}" data-ss-tab-panel>
                @if ($area['items'] === [])
                    <p class="ss-notifications__empty">{{ $area['empty'] }}</p>
                @else
                    <ul class="ss-tasks__list">
                        @foreach ($area['items'] as $item)
                            @php($task = $item->task)
                            @php($done = $task->status->isDone())
                        <li @class(['ss-task', 'ss-task--done' => $done])>
                            <form class="ss-task__toggle" method="POST" action="{{ $item->toggleUrl }}" data-ss-autosubmit>
                                @csrf
                                @method('PATCH')
                                <input class="ss-task__check" id="ss-task-{{ $scope }}-{{ $task->token }}" type="checkbox" @checked($done)>
                                <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ text($done ? 'layout.task_reopen' : 'layout.task_done') }}</button>
                            </form>
                            <label class="ss-task__body" for="ss-task-{{ $scope }}-{{ $task->token }}">
                                <span class="ss-task__head">
                                    <span class="ss-task__issue">#{{ $task->issue }}</span>
                                    <span class="ss-task__name">{{ $task->name }}</span>
                                    <span class="ss-badge ss-badge--outline ss-badge--{{ $task->status->tone() }} ss-task__status">{{ $task->status->label() }}</span>
                                </span>
                                @if (filled($task->title))
                                    <span class="ss-task__title">{{ $task->title }}</span>
                                @endif
                                @if (filled($task->description))
                                    <span class="ss-task__description">{{ $task->description }}</span>
                                @endif
                                @if ($item->commits() !== [])
                                    <span class="ss-task__commits">
                                        @foreach ($item->commits() as $commit)
                                            <a class="ss-task__commit" href="{{ $commit['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ text_data($task, \Simplysmart\Admiro\Models\AccountTask::COMMITS) }}"><x-admiro::icon name="external-link" class="ss-task__meta-icon" />{{ $commit['label'] }}</a>
                                        @endforeach
                                    </span>
                                @endif
                                @if (filled($task->project) || filled($task->customer) || $task->due_on !== null || $task->delay_days > 0)
                                    <span class="ss-task__meta">
                                        @if (filled($task->project))
                                            <span title="{{ text_data($task, 'project') }}"><x-admiro::icon name="list" class="ss-task__meta-icon" />{{ $task->project }}</span>
                                        @endif
                                        @if (filled($task->customer))
                                            <span title="{{ text_data($task, 'customer') }}"><x-admiro::icon name="user" class="ss-task__meta-icon" />{{ $task->customer }}</span>
                                        @endif
                                        @if ($task->due_on !== null)
                                            <span title="{{ text_data($task, 'due_on') }}"><x-admiro::icon name="calendar" class="ss-task__meta-icon" />{{ local_date($task->due_on) }}</span>
                                        @endif
                                        @if ($task->delay_days > 0)
                                            <span class="ss-task__delay" title="{{ text_data($task, 'delay_days') }}">{{ text('layout.task_delay', ['days' => (string) $task->delay_days]) }}</span>
                                        @endif
                                    </span>
                                @endif
                            </label>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
</section>
