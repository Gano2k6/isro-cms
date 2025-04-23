<!-- FOOTER -->
<div class="container">
    <footer class="row py-5 my-5 border-top">
        <div class="col-md-6 mb-3">
            <a href="{{ url('/') }}" class="d-flex align-items-center me-3 mb-2 mb-lg-0 text-white text-decoration-none" aria-label="Bootstrap">
                <img src="{{ asset(config('settings.site_logo', 'images/logo.png')) }}" alt="" width="" height="40" class="">
            </a>
            <p class="text-body-secondary mb-0 mt-2">© 2025 <a href="{{ config('settings.site_url', 'iSRO CMS v2') }}">{{ config('settings.site_title', 'http://localhost') }}</a>, Inc - All Rights Reserved.</p>
            <p class="text-body-secondary">Coded by <a class="link-default" href="https://github.com/m1xawy" target="_blank">m1xawy</a></p>
        </div>

        <div class="col-md-2 mb-3">
            <h5>{{ __('General') }}</h5>
            <ul class="nav flex-column">
                @foreach(config('global.general.footer.general') as $value)
                <li class="nav-item mb-2"><a href="{{ $value['url'] }}" target="_blank" class="nav-link p-0 text-body-secondary">{{ $value['name'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="col-md-2 mb-3">
            <h5>{{ __('Social Media') }}</h5>
            <ul class="nav flex-column">
                @foreach(config('global.general.footer.social') as $value)
                    <li class="nav-item mb-2"><a href="{{ $value['url'] }}" target="_blank" class="nav-link p-0 text-body-secondary">{{ $value['name'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="col-md-2 mb-3">
            <h5>{{ __('Backlink') }}</h5>
            <ul class="nav flex-column">
                @foreach(config('global.general.footer.backlink') as $value)
                    <li class="nav-item mb-2"><a href="{{ $value['url'] }}" target="_blank" class="nav-link p-0 text-body-secondary">{{ $value['name'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </footer>
</div>
