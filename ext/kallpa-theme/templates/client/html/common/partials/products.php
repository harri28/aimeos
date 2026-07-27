<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2026
 */

/* Expected data:
 * - products : List of products incl. referenced items
 * - basket-add : True to display "add to basket" button, false if not (optional)
 * - require-stock : True if the stock level should be displayed (optional)
 * - itemprop : Schema.org property for the product items (optional)
 * - position : Position is product list to start from (optional)
 */


$enc = $this->encoder();
$position = $this->get( 'position' );
$attrTypes = $this->get( 'attributeTypes', map() );


$detailTarget = $this->config( 'client/html/catalog/detail/url/target' );
$detailController = $this->config( 'client/html/catalog/detail/url/controller', 'catalog' );
$detailAction = $this->config( 'client/html/catalog/detail/url/action', 'detail' );
$detailConfig = $this->config( 'client/html/catalog/detail/url/config', [] );
$detailFilter = array_flip( $this->config( 'client/html/catalog/detail/url/filter', ['d_prodid'] ) );


?>
<?php foreach( $this->get( 'products', [] ) as $id => $productItem ) : ?>
	<?php
		$name = $productItem->getName( 'url' );
		$params = array_diff_key( ['path' => $name, 'd_name' => $name, 'd_prodid' => $productItem->getId(), 'd_pos' => $position !== null ? $position++ : ''], $detailFilter );
		$url = $this->url( ( $productItem->getTarget() ?: $detailTarget ), $detailController, $detailAction, $params, [], $detailConfig );

		$mediaItems = $productItem->getRefItems( 'media', 'default', 'default' );
		$itemProp = $this->get( 'itemprop' );
	?>

	<div class="product row <?= $enc->attr( $productItem->getConfigValue( 'css-class' ) ) ?>"
		data-prodid="<?= $enc->attr( $id ) ?>" data-reqstock="<?= (int) $this->get( 'require-stock', true ) ?>"
		<?= $itemProp ? 'itemprop="' . $itemProp . '"' : '' ?> itemscope itemtype="http://schema.org/Product">

		<div class="list-column col-12">

			<?= $this->partial(
				$this->config( 'client/html/common/partials/badges', 'common/partials/badges' )
			) ?>

			<a class="media-list <?= $mediaItems->count() > 1 ? 'multiple' : '' ?>"
				href="<?= $enc->attr( $url ) ?>" title="<?= $enc->attr( $productItem->getName(), $enc::TRUST ) ?>">

				<?php if( $mediaItem = $mediaItems->first() ) : ?>

					<noscript>
						<div class="media-item">
							<img loading="lazy" itemprop="image"
								src="<?= $enc->attr( $this->content( $mediaItem->getPreview(), $mediaItem->getFileSystem() ) ) ?>"
								srcset="<?= $enc->attr( $this->imageset( $mediaItem->getPreviews( true ), $mediaItem->getFileSystem() ) ) ?>"
								sizes="<?= $enc->attr( $this->config( 'client/html/common/imageset-sizes', '(min-width: 260px) 240px, 100vw' ) ) ?>"
								alt="<?= $enc->attr( $mediaItem->getProperties( 'title' )->first() ?: $productItem->getName() ) ?>"
							>
						</div>
					</noscript>

					<?php foreach( $mediaItems->take( 2 ) as $mediaItem ) : ?>

						<div class="media-item">
							<img loading="lazy" itemprop="image"
								src="<?= $enc->attr( $this->content( $mediaItem->getPreview(), $mediaItem->getFileSystem() ) ) ?>"
								srcset="<?= $enc->attr( $this->imageset( $mediaItem->getPreviews( true ), $mediaItem->getFileSystem() ) ) ?>"
								sizes="<?= $enc->attr( $this->config( 'client/html/common/imageset-sizes', '(min-width: 260px) 240px, 100vw' ) ) ?>"
								alt="<?= $enc->attr( $mediaItem->getProperties( 'title' )->first() ?: $productItem->getName() ) ?>"
							>
						</div>

					<?php endforeach ?>
				<?php endif ?>

			</a>
		</div>

		<div class="list-column col-12">
			<a href="<?= $enc->attr( $url ) ?>">

				<div class="product-info">
					<?php if( $supplier = $productItem->getRefItems( 'supplier' )->getName()->first() ) : ?>
						<div class="supplier"><?= $enc->html( $supplier ) ?></div>
					<?php elseif( $siteItem = $productItem->getSiteItem() ) : ?>
						<div class="supplier"><?= $enc->html( $siteItem->getLabel() ) ?></div>
					<?php endif ?>

					<div class="rating"><!--
						--><span class="stars"><?= str_repeat( '★', (int) round( $productItem->getRating() ) ) ?></span><!--
					--></div>
				</div>

				<div class="text-list">
					<h2 class="name" itemprop="name"><?= $enc->html( $productItem->getName(), $enc::TRUST ) ?></h2>

					<?php foreach( $productItem->getRefItems( 'text', 'short', 'default' ) as $textItem ) : ?>

						<div class="text-item" itemprop="description">
							<?= $enc->html( $textItem->getContent(), $enc::TRUST ) ?>
						</div>

					<?php endforeach ?>

				</div>
			</a>

			<div class="offer" itemscope itemprop="offers" itemtype="http://schema.org/Offer">

				<div class="section">
					<div class="stock-list">
						<div class="articleitem <?= !in_array( $productItem->getType(), ['group'] ) ? 'stock-actual' : '' ?>"
							data-prodid="<?= $enc->attr( $productItem->getId() ) ?>">
						</div>

						<?php foreach( $productItem->getRefItems( 'product', null, 'default' ) as $articleId => $articleItem ) : ?>

							<div class="articleitem" data-prodid="<?= $enc->attr( $articleId ) ?>"></div>

						<?php endforeach ?>

					</div>

					<div class="price-list">
						<div class="articleitem price price-actual" data-prodid="<?= $enc->attr( $productItem->getId() ) ?>">

							<?= $this->partial(
								$this->config( 'client/html/common/partials/price', 'common/partials/price' ),
								['prices' => $productItem->getRefItems( 'price', null, 'default' )]
							) ?>

						</div>

						<?php if( $this->get( 'basket-add', false ) && $productItem->getType() === 'select' ) : ?>
							<?php foreach( $productItem->getRefItems( 'product', 'default', 'default' ) as $prodid => $product ) : ?>
								<?php if( !( $prices = $product->getRefItems( 'price', null, 'default' ) )->isEmpty() ) : ?>

									<div class="articleitem price" data-prodid="<?= $enc->attr( $prodid ) ?>">
										<?= $this->partial(
											$this->config( 'client/html/common/partials/price', 'common/partials/price' ),
											array( 'prices' => $prices )
										) ?>
									</div>

								<?php endif ?>
							<?php endforeach ?>
						<?php endif ?>
					</div>

				</div>

				<?php if( $this->get( 'basket-add', false ) ) : ?>

					<form class="basket" method="POST" action="<?= $enc->attr( $this->link( 'client/html/basket/standard/url' ) ) ?>">
						<!-- catalog.lists.items.csrf -->
						<?= $this->csrf()->formfield() ?>
						<!-- catalog.lists.items.csrf -->

						<?php if( $productItem->getType() === 'select' ) : ?>

							<div class="items-selection">
								<?= $this->partial( $this->config( 'client/html/common/partials/selection', 'common/partials/selection' ), [
									'productItems' => $productItem->getRefItems( 'product', 'default', 'default' ),
									'productItem' => $productItem,
									'attributeTypes' => $attrTypes
								] ) ?>
							</div>

						<?php endif ?>

						<div class="items-attribute">

							<?= $this->partial(
								$this->config( 'client/html/common/partials/attribute', 'common/partials/attribute' ),
								[
									'productItem' => $productItem,
									'attributeTypes' => $attrTypes
								]
							) ?>

						</div>

						<?php if( !$productItem->getRefItems( 'price', 'default', 'default' )->empty() ) : ?>
							<div class="addbasket">
								<input type="hidden" value="add"
									name="<?= $enc->attr( $this->formparam( 'b_action' ) ) ?>"
								>
								<input type="hidden" value="<?= $id ?>"
									name="<?= $enc->attr( $this->formparam( array( 'b_prod', 0, 'prodid' ) ) ) ?>"
								>
								<div class="input-group">
									<input type="number" max="2147483647"
										value="<?= $enc->attr( $productItem->getScale() ) ?>"
										min="<?= $enc->attr( $productItem->getScale() ) ?>"
										step="<?= $enc->attr( $productItem->getScale() ) ?>"
										required="required" <?= !$productItem->isAvailable() ? 'disabled' : '' ?>
										name="<?= $enc->attr( $this->formparam( array( 'b_prod', 0, 'quantity' ) ) ) ?>"
										title="<?= $enc->attr( $this->translate( 'client', 'Quantity' ), $enc::TRUST ) ?>"
									><!--
									--><button class="btn btn-primary btn-action" type="submit"
										title="<?= $enc->attr( $this->translate( 'client', 'Add to basket' ), $enc::TRUST ) ?>"
										<?= !$productItem->isAvailable() ? 'disabled' : '' ?> >
										<?= $enc->html( $this->translate( 'client', 'Add to basket' ), $enc::TRUST ) ?>
									</button><!--
									--><a class="btn-pin"
										href="<?= $enc->attr( $this->link( 'client/html/catalog/session/pinned/url', ['pin_action' => 'add', 'pin_id' => $id, 'd_name' => $productItem->getName( 'url' )] ) ) ?>"
										data-rmurl="<?= $enc->attr( $this->link( 'client/html/catalog/session/pinned/url', ['pin_action' => 'delete', 'pin_id' => $id, 'd_name' => $productItem->getName( 'url' )] ) ) ?>"
										title="<?= $enc->attr( $this->translate( 'client', 'Pin product' ), $enc::TRUST ) ?>">
									</a>
								</div>
							</div>
						<?php endif ?>

					</form>

				<?php endif ?>

			</div>
		</div>
	</div>

<?php endforeach ?>
