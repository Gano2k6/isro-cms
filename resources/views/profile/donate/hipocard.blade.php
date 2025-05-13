@section('title', __('HipoCard'))

<div class="card p-3 mb-4">
    <div class="card-body">
        <p class="text-center">Enter your e-pin card</p>
        <form method="post" action="{{ route('profile.donate.process', ['method' => $data['route']]) }}">
            @csrf

            <div class="row mb-3">
                <label for="code" class="col-md-12 col-form-label text-md-left">{{ __('E-Pin Code') }}</label>

                <div class="col-md-12">
                    <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" required>

                    @error('code')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label for="password" class="col-md-12 col-form-label text-md-left">{{ __('E-Pin Password') }}</label>

                <div class="col-md-12">
                    <input id="password" type="text" class="form-control @error('password') is-invalid @enderror" name="password" required>

                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@forelse($data['package'] as $value)
    <div class="card mb-2" data-name="{{ $value['name'] }}" data-price="{{ $value['price'] }}" data-currency="{{ $data['currency'] }}">
        <div class="card-body d-flex justify-content-between align-items-center">
            <strong>{{ $value['name'] }}</strong>
            <span>{{ $data['currency'] }} {{ $value['price'] }}</span>
        </div>
    </div>
@empty
    <p class="text-muted">{{ __('No Packages Available!') }}</p>
@endforelse
