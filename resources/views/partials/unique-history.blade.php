@isset($uniqueHistoryConfig['enabled'])
    <div class="card mb-4">
        <div class="card-header">
            {{ __('Unique History') }}
        </div>
        <div class="card-body">
            <ul class="list-unstyled">
                @forelse($uniqueHistory as $value)
                    <li class="mb-3">
                        <p class="mb-0">{{ $uniquesList[$value->Value]['name'] }}</p>
                        <small>
                            {{ __('Killed by:') }}
                            @if(!empty($value->CharName16))
                                <a href="{{ route('ranking.character.view', ['name' => $value->CharName16]) }}" class="text-decoration-none">{{ $value->CharName16 }}</a>
                            @else
                                <span>{{ __('None') }}</span>
                            @endif
                            {{ \Carbon\Carbon::make($value->EventTime)->diffForHumans() }}
                        </small>
                    </li>
                @empty
                    <p class="text-center">{{ __('No Records Found!') }}</p>
                @endforelse
            </ul>
        </div>
    </div>
@endisset
