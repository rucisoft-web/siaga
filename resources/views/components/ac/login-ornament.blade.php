@if (request()->routeIs('filament.admin.auth.*'))
    <div class="ac-login-strip" aria-hidden="true">
        <span>{{ \App\Support\Branding::hotelName() }}</span>
        <span>{{ \App\Support\Branding::appName() }} · {{ \App\Support\Branding::code() }}</span>
    </div>
@endif
