<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2020
 */

$enc = $this->encoder();


$target = $this->config( 'client/html/catalog/tree/url/target' );
$controller = $this->config( 'client/html/catalog/tree/url/controller', 'catalog' );
$action = $this->config( 'client/html/catalog/tree/url/action', 'list' );
$config = $this->config( 'client/html/catalog/tree/url/config', [] );
$filter = array_flip( $this->config( 'client/html/catalog/tree/url/filter', [] ) );


?>
<div class="list-container level-<?= $enc->attr( $this->get( 'level', 0 ) ) ?>">

	<?php if( $this->get( 'level', 0 ) == 1 ) : ?>
		<div class="cat-item catid-home nochild catcode-inicio" data-id="home">
			<div class="item-links row">
				<a class="col-10 cat-link name" href="/">
					<div class="media-list"></div>
					<span class="cat-name">Inicio</span>
				</a>
				<div class="col-2"></div>
			</div>
		</div>
	<?php endif ?>

	<?php foreach( $this->get( 'nodes', [] ) as $item ) : ?>
		<?php if( $item->getStatus() > 0 && $item->getCode() !== 'eventos' ) : ?>
			<?php $name = $item->getName( 'url' ) ?>
			<?php $params = array_diff_key( array_merge( $this->get( 'params', [] ), ['path' => $name, 'f_name' => $name, 'f_catid' => $item->getId()] ), $filter ) ?>
			<?php $url = $this->url( $item->getTarget() ?: $target, $controller, $action, $params, [], $config ) ?>

			<div class="cat-item catid-<?= $enc->attr( $item->getId() .
				( !$item->getChildren()->isEmpty() ? ' withchild' : ' nochild' ) .
				( $this->get( 'path', map() )->has( $item->getId() ) ? ' active' : '' ) .
				' catcode-' . $item->getCode() . ' ' . $item->getConfigValue( 'css-class' ) ) ?>"
				data-id="<?= $item->getId() ?>">

				<div class="item-links row">
					<a class="col-10 cat-link name <?= ( $this->get( 'path', map() )->has( $item->getId() ) ? ' active' : '' ) ?>"
						href="<?= $enc->attr( $url ) ?>">
						<div class="media-list">
							<?php foreach( $item->getRefItems( 'media', 'icon', 'default' ) as $mediaItem ) : ?>
								<?= $this->partial(
									$this->config( 'client/html/common/partials/media', 'common/partials/media' ),
									['item' => $mediaItem, 'boxAttributes' => ['class' => 'media-item']]
								) ?>
							<?php endforeach ?>
						</div>
						<span class="cat-name"><?= $enc->html( $item->getName(), $enc::TRUST ) ?></span>
					</a>
					<?php if( !$item->getChildren()->isEmpty() ) : ?>
						<div class="col-2 next" data-submenu="<?= $enc->attr( $item->getId() ) ?>"
							title="<?= $enc->attr( $this->translate( 'client', 'Open submenu' ) ) ?>">
						</div>
					<?php else : ?>
						<div class="col-2"></div>
					<?php endif ?>
				</div>

				<?php if( count( $item->getChildren() ) > 0 ) : ?>

					<div id="<?= $enc->attr( $item->getId() ) ?>" class="submenu <?= $enc->attr(
						( $this->get( 'path', map() )->has( $item->getId() ) ? ' active opened' : '' ) ) .
						( !$item->getChildren()->isEmpty() ? ' withchild' : ' nochild' ) ?>"
						<?= $this->get( 'path', map() )->getId()->last() == $item->getId() ? 'aria-current="page"' : '' ?>
						data-id="<?= $enc->attr( $item->getId() ) ?>">

						<div class="row header">
							<div class="col-2 back" data-submenu-close="<?= $enc->attr( $item->getId() ) ?>"></div>
							<div class="col-8 name"><?= $enc->html( $item->getName(), $enc::TRUST ) ?></div>
							<div class="col-2 close"></div>
						</div>

						<?= $this->partial( $this->config( 'client/html/catalog/filter/tree/partial', 'catalog/filter/tree-partial' ), [
							'nodes' => $item->getChildren(),
							'path' => $this->get( 'path', map() ),
							'level' => $this->get( 'level', 0 ) + 1,
							'params' => $this->get( 'params', [] )
						] ) ?>

						<?php if( $item->getLevel() == 1 ) : ?>

							<a class="cat-img <?= $enc->attr( ( $this->get( 'path', map() )->getId()->last() == $item->getId() ? ' active' : '' ) ) ?>"
								title="<?= $enc->attr( $item->getRefItems( 'media', 'menu', 'default' )->getProperties( 'title' )->first() ?: $item->getName() ) ?>"
								href="<?= $enc->attr( $url ) ?>">

								<?php foreach( $item->getRefItems( 'media', 'menu', 'default' ) as $mediaItem ) : ?>
									<img class="img-menu" loading="lazy"
										src="<?= $enc->attr( $this->content( $mediaItem->getPreview(), $mediaItem->getFileSystem() ) ) ?>"
										srcset="<?= $enc->attr( $this->imageset( $mediaItem->getPreviews( true ), $mediaItem->getFileSystem() ) ) ?>"
										sizes="<?= $enc->attr( $this->config( 'client/html/common/imageset-sizes', '(min-width: 260px) 240px, 100vw' ) ) ?>"
										alt="<?= $enc->attr( $mediaItem->getProperties( 'name' )->first() ?: $mediaItem->getLabel() ) ?>"
									>
								<?php endforeach ?>

							</a>

						<?php endif ?>

					</div>

				<?php endif ?>

			</div>
		<?php endif ?>
	<?php endforeach ?>
</div>
