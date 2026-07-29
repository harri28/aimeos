<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

					@if( config('app.debug') !== true )
			<meta http-equiv="Content-Security-Policy" content="base-uri 'self'; default-src 'self' 'nonce-{{ app( 'aimeos.context' )->get()->nonce() }}'; {{ config( 'shop.csp.frontend', 'style-src \'unsafe-inline\' \'self\'; img-src \'self\' data: https://aimeos.org; frame-src https://www.youtube.com https://player.vimeo.com' ) }}">
		@endif

		@if( in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) )
			<link type="text/css" rel="stylesheet" href="{{ asset('vendor/shop/themes/default/app.rtl.css?v=' . config( 'shop.version', 1 ) ) }}">
		@else
			<link type="text/css" rel="stylesheet" href="{{ asset('vendor/shop/themes/default/app.css?v=' . config( 'shop.version', 1 ) ) }}">
		@endif
		<link type="text/css" rel="stylesheet" href="{{ asset('vendor/shop/themes/default/aimeos.css?v=' . config( 'shop.version', 1 ) ) }}">

		@yield('aimeos_header')

		<style nonce="{{ app( 'aimeos.context' )->get()->nonce() }}">
			:root {
				@foreach( app( 'aimeos.context' )->get()->locale()->getSiteItem()->getConfigValue( 'theme/default', [] ) as $key => $value )
					{{ $key }}: {{ $value }};
				@endforeach
			}
		</style>

		<link rel="icon" href="{{ asset( app( 'aimeos.context' )->get()->config()->get( 'resource/fs-media/baseurl' ) . '/' . ( app( 'aimeos.context' )->get()->locale()->getSiteItem()->getIcon() ?: '../vendor/shop/themes/default/assets/icon.png' ) ) }}">

		<link rel="preload" href="{{ asset('vendor/shop/themes/default/assets/roboto-condensed-v19-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
		<link rel="preload" href="{{ asset('vendor/shop/themes/default/assets/roboto-condensed-v19-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
		<link rel="preload" href="{{ asset('vendor/shop/themes/default/assets/bootstrap-icons.woff2') }}" as="font" type="font/woff2" crossorigin>
	</head>
	<body class="{{ $page ?? '' }}">
		<nav class="navbar navbar-expand-md navbar-top">
			<a class="navbar-brand" href="/" title="{{ __('To the home page') }}">
				<img src="{{ asset( app( 'aimeos.context' )->get()->config()->get( 'resource/fs-media/baseurl' ) . '/' . ( app( 'aimeos.context' )->get()->locale()->getSiteItem()->getLogo() ?: '../vendor/shop/themes/default/assets/logo.png' ) ) }}" height="40" alt="{{ __('To the home page') }}">
			</a>

			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-top" aria-controls="navbar-top" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			@yield('aimeos_head_locale')

			<a class="navbar-home-link" href="/" title="Inicio">Inicio</a>

			@yield('aimeos_head_search')

			<ul class="navbar-nav">
				@if (Auth::guest() && config('app.shop_registration'))
					<li class="nav-item register"><a class="nav-link" href="{{ airoute( 'register' ) }}" title="{{ __( 'Register' ) }}"><span class="name">{{ __('Register') }}</span></a></li>
				@endif
				@if (Auth::guest())
					<li class="nav-item login"><a class="nav-link" href="{{ airoute( 'login' ) }}" title="{{ __( 'Login' ) }}"><span class="name">{{ __( 'Login' ) }}</span></a></li>
				@endif
			</ul>

			@auth
				<div class="section aimeos profile-mini">
					<div class="aimeos-overlay-offscreen"></div>

					<a class="profile-mini-main menu" href="#" title="{{ __( 'Account' ) }}"></a>

					<div class="profile-mini-panel zeynep">
						<div class="header row">
							<div class="col-2"></div>
							<div class="col-8 name">{{ __( 'Account' ) }}</div>
							<a class="col-2 close" href="#" title="{{ __( 'Close' ) }}"></a>
						</div>

						<div class="profile-mini-content">
							<div class="profile-mini-name">{{ Auth::user()->name }}</div>
							<a class="btn btn-primary" href="{{ airoute( 'aimeos_shop_account' ) }}">{{ __( 'Profile' ) }}</a>
						</div>
					</div>
				</div>
			@endauth

			@yield('aimeos_head_basket')
		</nav>

		<div class="kallpa-subnav">
			@yield('aimeos_head_nav')
		</div>

		<div class="content">
			@yield('aimeos_stage')
			<main>
				@yield('aimeos_body')
				@yield('content')
			</main>
		</div>


		<footer>
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-8">
						<div class="row">
							<div class="col-sm-6 footer-left">
								<div class="footer-block">
									<h2 class="pb-3" aria-label="{{ __('Legal information') }}">{{ __( 'LEGAL' ) }}</h2>
									<p><a href="{{ airoute(config('shop.client.html.cms.page.url.target', 'aimeos_page'), ['path' => 'terms']) }}">{{ __( 'Terms & Conditions' ) }}</a></p>
									<p><a href="{{ airoute(config('shop.client.html.cms.page.url.target', 'aimeos_page'), ['path' => 'privacy']) }}">{{ __( 'Privacy Notice' ) }}</a></p>
								</div>
							</div>
							<div class="col-sm-6 footer-center">
								<div class="footer-block">
									<h2 class="pb-3" aria-label="{{ __('About the company') }}">{{ __( 'ABOUT US' ) }}</h2>
									<p><a href="{{ airoute(config('shop.client.html.cms.page.url.target', 'aimeos_page'), ['path' => 'about']) }}#contacto">{{ __( 'Contact us' ) }}</a></p>
									<p><a href="{{ airoute(config('shop.client.html.cms.page.url.target', 'aimeos_page'), ['path' => 'about']) }}">{{ __( 'Company' ) }}</a></p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4 footer-right">
						<div class="footer-block">
							<a class="logo" href="/" title="{{ __('To the home page') }}">
								<img src="{{ asset( app( 'aimeos.context' )->get()->config()->get( 'resource/fs-media/baseurl' ) . '/' . ( app( 'aimeos.context' )->get()->locale()->getSiteItem()->getLogo() ?: '../vendor/shop/themes/default/assets/logo.png' ) ) }}" height="40" alt="{{ __('To the home page') }}">
							</a>
							@php
								$aimeosSite = app( 'aimeos.context' )->get()->locale()->getSiteItem();
								$socialLinks = [
									'facebook'  => ['url' => $aimeosSite->getConfigValue('social/facebook'),  'label' => 'Facebook'],
									'instagram' => ['url' => $aimeosSite->getConfigValue('social/instagram'), 'label' => 'Instagram'],
									'twitter'   => ['url' => $aimeosSite->getConfigValue('social/twitter'),   'label' => 'Twitter'],
									'youtube'   => ['url' => $aimeosSite->getConfigValue('social/youtube'),   'label' => 'Youtube'],
								];
							@endphp
							<div class="social" aria-label="{{ __('Social media links') }}">
								@foreach( $socialLinks as $network => $data )
									@if( !empty($data['url']) )
										<p><a href="{{ $data['url'] }}" class="sm {{ $network }}" title="{{ $data['label'] }}" target="_blank" rel="noopener">{{ $data['label'] }}</a></p>
									@endif
								@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>



		<a id="toTop" class="back-to-top" href="#" title="{{ __( 'Back to top' ) }}">
			<div class="top-icon"></div>
		</a>

		<!-- Scripts -->
		<script src="{{ asset('vendor/shop/themes/default/app.js?v=' . config( 'shop.version', 1 ) ) }}"></script>
		<script src="{{ asset('vendor/shop/themes/default/aimeos.js?v=' . config( 'shop.version', 1 ) ) }}"></script>
		@yield('aimeos_scripts')
	</body>
</html>
