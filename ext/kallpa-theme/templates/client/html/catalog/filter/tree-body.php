<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2020
 */

$enc = $this->encoder();


$enforce = $this->config( 'client/html/catalog/filter/tree/force-search', false );


?>
<?php $this->block()->start( 'catalog/filter/tree' ) ?>
<?php if( isset( $this->treeCatalogTree ) && $this->treeCatalogTree->getStatus() > 0 && !$this->treeCatalogTree->getChildren()->isEmpty() ) : ?>

	<div class="section catalog-filter-tree <?= ( $this->config( 'client/html/catalog/count/enable', true ) ? 'catalog-filter-count' : '' ); ?>"
		aria-label="<?= $enc->attr( $this->translate( 'client', 'Product categories' ) ) ?>"
		data-counturl="<?= $enc->attr( $this->link( 'client/html/catalog/count/url', ['count' => 'tree'] + $this->get( 'filterParams', [] ) ) ) ?>">

		<div class="aimeos-overlay-offscreen"></div>
		<a class="menu" href="#" title="<?= $enc->attr( $this->translate( 'client', 'Categories' ) ) ?>"><span class="icon"></span></a>
		<div class="zeynep list-container level-0 catcode-<?= $enc->attr( $this->treeCatalogTree->getCode() ) ?> <?= $enc->attr( $this->treeCatalogTree->getConfigValue( 'css-class' ) ) ?>">

			<div class="row header">
				<div class="col-2"></div>
				<div class="col-8 name"><?= $enc->html( $this->translate( 'client', 'Categories' ), $enc::TRUST ) ?></div>
				<div class="col-2 close" data-submenu-close="<?= $enc->attr( $this->treeCatalogTree->getId() ) ?>"></div>
			</div>

			<?php if( $enforce ) : ?>
				<input type="hidden"
					name="<?= $enc->attr( $this->formparam( ['f_catid'] ) ) ?>"
					value="<?= $enc->attr( $this->param( 'f_catid' ) ) ?>"
				>
				<input type="hidden"
					name="<?= $enc->attr( $this->formparam( ['f_name'] ) ) ?>"
					value="<?= $enc->attr( $this->get( 'treeCatalogPath', map() )->getName()->get( $this->param( 'f_catid' ) ) ) ?>"
				>
			<?php endif ?>

			<div class="list-container level-1 kallpa-home-item">
				<div class="cat-item catid-home nochild catcode-inicio" data-id="home">
					<div class="item-links row">
						<a class="col-10 cat-link name" href="/">
							<div class="media-list"></div>
							<span class="cat-name">Inicio</span>
						</a>
						<div class="col-2"></div>
					</div>
				</div>
			</div>

			<?= $this->partial(
				$this->config( 'client/html/catalog/filter/tree/partial', 'catalog/filter/tree-partial' ), [
					'nodes' => $this->treeCatalogTree->getChildren(),
					'path' => $this->get( 'treeCatalogPath', map() ),
					'params' => [],
					'level' => 1
				] ) ?>
		</div>
	</div>

<?php endif ?>
<?php $this->block()->stop() ?>
<?= $this->block()->get( 'catalog/filter/tree' ) ?>
