<?php

/**
 * Kallpa Room: versión horizontal del árbol de categorías del catálogo.
 * Reemplaza el sidebar lateral deslizante por un menú de barra (navbar).
 * Mantiene las clases .cat-item / .submenu que ya usa tree-partial.php
 * para no romper el CSS de categoría activa, pero omite el botón
 * hamburguesa y el contenedor ".zeynep" (sidebar Zeynep).
 */

$enc = $this->encoder();

?>
<?php $this->block()->start( 'catalog/filter/tree' ) ?>
<?php if( isset( $this->treeCatalogTree ) && $this->treeCatalogTree->getStatus() > 0 && !$this->treeCatalogTree->getChildren()->isEmpty() ) : ?>

	<nav class="section catalog-filter-tree catalog-nav-horizontal <?= ( $this->config( 'client/html/catalog/count/enable', true ) ? 'catalog-filter-count' : '' ); ?>"
		aria-label="<?= $enc->attr( $this->translate( 'client', 'Product categories' ) ) ?>">

		<?= $this->partial(
			$this->config( 'client/html/catalog/filter/tree/partial', 'catalog/filter/tree-partial' ), [
				'nodes' => $this->treeCatalogTree->getChildren(),
				'path' => $this->get( 'treeCatalogPath', map() ),
				'params' => [],
				'level' => 1
			] ) ?>
	</nav>

<?php endif ?>
<?php $this->block()->stop() ?>
<?= $this->block()->get( 'catalog/filter/tree' ) ?>
