@extends('shop::base')

@section('aimeos_header')
	<?= $aiheader['locale/select'] ?? '' ?>
	<?= $aiheader['basket/mini'] ?? '' ?>
	<?= $aiheader['catalog/search'] ?? '' ?>
	<?= $aiheader['catalog/tree'] ?? '' ?>
	<?= $aiheader['catalog/home'] ?? '' ?>
	<?= $aiheader['catalog/lists'] ?? '' ?>
	<?= $aiheader['cms/page'] ?? '' ?>
@stop

@section('aimeos_head_basket')
	<?= $aibody['basket/mini'] ?? '' ?>
@stop

@section('aimeos_head_nav')
	<?= $aibody['catalog/tree'] ?? '' ?>
@stop

@section('aimeos_head_locale')
	<?= $aibody['locale/select'] ?? '' ?>
@stop

@section('aimeos_head_search')
	<?= $aibody['catalog/search'] ?? '' ?>
@stop

@php
	$aimeosCtx  = app( 'aimeos.context' )->get();
	$aimeosSite = $aimeosCtx->locale()->getSiteItem();
	$mediaBase  = $aimeosCtx->config()->get( 'resource/fs-media/baseurl' );

	$homePhrases = array_values( array_filter(
		(array) $aimeosSite->getConfigValue( 'home/typing', [] ),
		fn( $v ) => is_string( $v ) && trim( $v ) !== ''
	) );

	$homeEvents = [];
	$eventCatId = \Illuminate\Support\Facades\DB::table( 'mshop_catalog' )->where( 'code', 'eventos' )->value( 'id' );
	if( $eventCatId ) {
		try {
			$homeEvents = \Aimeos\Controller\Frontend::create( $aimeosCtx, 'product' )
				->category( $eventCatId )
				->uses( ['text', 'media'] )
				->sort( '-ctime' )
				->slice( 0, 12 )
				->search();
		} catch( \Throwable $e ) {
			$homeEvents = [];
		}
	}

	// Calendario de proximos acontecimientos: cada entrada "fecha | texto"
	$homeCalendar = [];
	foreach( (array) $aimeosSite->getConfigValue( 'home/calendar', [] ) as $entry ) {
		if( !is_string( $entry ) || trim( $entry ) === '' ) { continue; }
		$parts = explode( '|', $entry, 2 );
		$homeCalendar[] = [
			'date' => trim( $parts[0] ),
			'text' => trim( $parts[1] ?? '' ),
		];
	}

	// Paquete turistico promocionado, mostrado junto a los eventos.
	$homePromo = null;
	$promoCfg = (array) $aimeosSite->getConfigValue( 'home/promo', [] );

	if( !empty( $promoCfg['product'] ) ) {
		try {
			$promoProduct = \Aimeos\MShop::create( $aimeosCtx, 'product' )->find( $promoCfg['product'], ['text', 'media', 'price'] );
			$priceItem = $promoProduct->getRefItems( 'price', 'default', 'default' )->first();
			$discount = max( 0, min( 90, (int) ( $promoCfg['discount'] ?? 0 ) ) );
			$price = $priceItem ? (float) $priceItem->getValue() : null;
			$offerPrice = ( $price !== null && $discount > 0 ) ? round( $price * ( 1 - $discount / 100 ), 2 ) : null;

			$homePromo = [
				'item' => $promoProduct,
				'img' => $promoProduct->getRefItems( 'media', 'default', 'default' )->first(),
				'badge' => $promoCfg['badge'] ?? null,
				'hook' => $promoCfg['hook'] ?? null,
				'price' => $price,
				'offerPrice' => $offerPrice,
				'url' => route( 'aimeos_shop_detail', ['d_name' => $promoProduct->getUrl(), 'd_prodid' => $promoProduct->getId()] ),
			];

			$waMsg = 'Hola, quiero reservar el paquete "'.$promoProduct->getLabel().'"'.( $offerPrice ? ' con la oferta' : '' ).'.';
			$homePromo['whatsapp'] = 'https://wa.me/51910964688?text='.rawurlencode( $waMsg );
		} catch( \Throwable $e ) {
			$homePromo = null;
		}
	}
@endphp

@section('aimeos_body')
	<?= $aibody['catalog/home'] ?? '' ?>

	<div class="home-layout">
		<div class="home-main">

			<div class="home-portada">
				<img src="{{ asset('vendor/shop/themes/default/assets/home-portada.webp') }}?v={{ config('shop.version', 1) }}"
					alt="Kallpa Room - Tu puerta a la Amazonia peruana">
			</div>

			@if( !empty($homePhrases) )
				<div class="home-typing" data-phrases='@json($homePhrases, JSON_UNESCAPED_UNICODE)'>
					<span class="home-typing-text"></span>
				</div>
			@endif

			@if( count($homeEvents) )
				<section class="home-events">
					<h2 class="home-events-title">Eventos en la regi&oacute;n</h2>
					<div class="home-events-grid">
						@foreach( $homeEvents as $ev )
							@php $evImg = $ev->getRefItems( 'media', 'default', 'default' )->first(); @endphp
							<article class="event-card">
								@if( $evImg )
									<div class="event-img">
										<img loading="lazy" src="{{ asset( $mediaBase . '/' . $evImg->getPreview() ) }}" alt="{{ $ev->getName() }}">
									</div>
								@endif
								<div class="event-body">
									<h3 class="event-title">{{ $ev->getName() }}</h3>
									@foreach( $ev->getRefItems( 'text', 'short', 'default' ) as $evTxt )
										<div class="event-text">{!! $evTxt->getContent() !!}</div>
									@endforeach
								</div>
							</article>
						@endforeach

						@if( $homePromo )
							<article class="event-card promo-card">
								@if( $homePromo['badge'] )
									<span class="promo-badge">{{ $homePromo['badge'] }}</span>
								@endif
								@if( $homePromo['img'] )
									<div class="event-img">
										<img loading="lazy" src="{{ asset( $mediaBase . '/' . $homePromo['img']->getPreview() ) }}" alt="{{ $homePromo['item']->getLabel() }}">
									</div>
								@endif
								<div class="event-body">
									<h3 class="event-title">{{ $homePromo['item']->getLabel() }}</h3>
									@if( $homePromo['hook'] )
										<div class="promo-hook">{{ $homePromo['hook'] }}</div>
									@endif
									@if( $homePromo['price'] !== null )
										<div class="promo-price">
											@if( $homePromo['offerPrice'] )
												<span class="promo-price-old">S/ {{ number_format( $homePromo['price'], 2 ) }}</span>
												<span class="promo-price-new">S/ {{ number_format( $homePromo['offerPrice'], 2 ) }}</span>
											@else
												<span class="promo-price-new">S/ {{ number_format( $homePromo['price'], 2 ) }}</span>
											@endif
										</div>
									@endif
									<div class="promo-cta">
										<a class="promo-btn promo-btn-wa" href="{{ $homePromo['whatsapp'] }}" target="_blank" rel="noopener">Reservar por WhatsApp</a>
										<a class="promo-btn promo-btn-outline" href="{{ $homePromo['url'] }}">Ver paquete</a>
									</div>
								</div>
							</article>
						@endif
					</div>
				</section>
			@endif

		</div>

		@if( !empty($homeCalendar) )
			<aside class="home-calendar">
				<h2 class="home-calendar-title">Pr&oacute;ximos acontecimientos</h2>
				<ul class="home-calendar-list">
					@foreach( $homeCalendar as $item )
						<li class="cal-item">
							<span class="cal-date">{{ $item['date'] }}</span>
							<span class="cal-text">{{ $item['text'] }}</span>
						</li>
					@endforeach
				</ul>
			</aside>
		@endif
	</div>

	<section class="home-map-wrap">
		<h2 class="home-map-title">&iquest;C&oacute;mo llegar?</h2>
		<div class="home-map">
			<iframe
				src="https://www.google.com/maps?q=Av.+Oasis+S%2FN%2C+Morales%2C+San+Mart%C3%ADn%2C+Peru&output=embed"
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
				allowfullscreen></iframe>
		</div>
		<p class="home-map-address">
			Av. Oasis S/N, Morales, San Mart&iacute;n
			<a href="https://www.google.com/maps/search/?api=1&query=Av.+Oasis+S%2FN%2C+Morales%2C+San+Mart%C3%ADn%2C+Peru" target="_blank" rel="noopener">Ver en Google Maps</a>
		</p>
	</section>
@stop

@section('aimeos_scripts')
	@parent
	@if( !empty($homePhrases) )
		<script src="{{ asset('vendor/shop/themes/default/home-typing.js') }}?v={{ config('shop.version', 1) }}"></script>
	@endif
@stop
