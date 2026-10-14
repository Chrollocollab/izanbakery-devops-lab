@extends('layouts.app')

{{-- The card opens on a dark photograph, so the header sits over it transparently. --}}
@section('header_class', 'is-overlay')

@section('content')

  <main class="bk-auth">
    <section class="login-register container">
      <div class="bk-auth__card">
        <div class="bk-auth__head">
          <span class="bk-eyebrow is-centered">Welcome Back</span>
          <h1 class="page-title">Sign in to <em style="font-style:italic;color:var(--bk-primary);">Izan's Bakery</em></h1>
          <p class="bk-auth__sub">Good to see you again — let's get something baking.</p>
        </div>
          <div class="login-form">
            <form method="POST" action="{{route('login')}}" name="login-form" class="needs-validation" novalidate="">
                @csrf
              <div class="bk-auth-field">
                <label for="email" class="bk-auth-field__label">Email address</label>
                <div class="bk-auth-field__control">
                  <i class="bi bi-envelope bk-auth-field__icon"></i>
                  <input class="bk-auth-field__input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required="" type="email" autocomplete="email" id="email" autofocus="">
                </div>
                @error('email')
                    <span class="bk-auth-field__error" role="alert">{{ $message }}</span>
                @enderror
              </div>

              <div class="bk-auth-field">
                <label for="password" class="bk-auth-field__label">Password</label>
                <div class="bk-auth-field__control">
                  <i class="bi bi-lock bk-auth-field__icon"></i>
                  <input id="password" type="password" class="bk-auth-field__input @error('password') is-invalid @enderror" name="password" required="" autocomplete="current-password">
                  <button type="button" class="bk-auth-field__toggle" data-toggle-password="#password" aria-label="Show password">
                    <i class="bi bi-eye-slash"></i>
                  </button>
                </div>
                @error('password')
                    <span class="bk-auth-field__error" role="alert">{{ $message }}</span>
                @enderror
              </div>

              @if (Route::has('password.request'))
                <div class="text-end mb-3">
                  <a class="btn-text is-muted" style="font-size:0.85rem;" href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
              @endif

              <button class="btn btn-primary w-100 text-uppercase" type="submit">Log In</button>

              <div class="d-flex align-items-center my-4" style="gap:12px;">
                <hr style="flex:1;margin:0;border-top:1px solid var(--bk-border);">
                <span class="text-secondary" style="font-size:0.8rem;">or</span>
                <hr style="flex:1;margin:0;border-top:1px solid var(--bk-border);">
              </div>

              <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" style="gap:10px;border-color:var(--bk-border);">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/>
                  <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/>
                  <path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/>
                  <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/>
                </svg>
                Continue with Google
              </a>

              {{-- Facebook login disabled for now: requires SSM for client secret. Re-enable once SSM is set up.
              <a href="{{ route('auth.social.redirect', 'facebook') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mt-2" style="gap:10px;border-color:var(--bk-border);">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#1877F2" d="M18 9a9 9 0 1 0-10.41 8.89v-6.29H5.31V9h2.28V7.02c0-2.25 1.34-3.49 3.39-3.49.98 0 2.01.18 2.01.18v2.21h-1.13c-1.11 0-1.46.69-1.46 1.4V9h2.49l-.4 2.6h-2.09v6.29A9 9 0 0 0 18 9z"/>
                </svg>
                Continue with Facebook
              </a>
              --}}

              <div class="customer-option mt-4 text-center">
                <span class="text-secondary">No account yet?</span>
                <a href="{{route('register')}}" class="btn-text" style="color:var(--bk-primary);font-weight:600;">Create Account</a>
              </div>
            </form>
          </div>
      </div>
    </section>
  </main>

{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> --}}
@push('scripts')
<script>
$(document).ready(function() {
    $('#email').on('keydown', function(e) {
        if (e.key === "Enter") {

            e.preventDefault();
            $('#password').focus();
        }
    });

    $('[data-toggle-password]').on('click', function() {
        var $input = $($(this).data('toggle-password'));
        var isHidden = $input.attr('type') === 'password';
        $input.attr('type', isHidden ? 'text' : 'password');
        $(this).find('i').toggleClass('bi-eye-slash bi-eye');
        $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
});
</script>   
@endpush
@endsection
