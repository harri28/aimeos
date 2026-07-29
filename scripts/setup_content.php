<?php

/**
 * Crea/actualiza contenido de Kallpa Room que vive en la base de datos
 * (no viaja con git): la pagina CMS "Empresa" (/p/about) y los enlaces
 * de redes sociales en la configuracion del sitio.
 *
 * Uso (dentro del contenedor / entorno con Laravel):
 *   php scripts/setup_content.php
 *
 * Es idempotente: se puede ejecutar varias veces sin duplicar.
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$c = app('Aimeos\Shop\Base\Context')->get(true, 'backend');
$c->locale()->setSiteId(1);

/* ----------------------------------------------------------------------
 * 1) Pagina CMS "Empresa" (/p/about)
 * -------------------------------------------------------------------- */

$html = <<<'HTML'
<div class="container-xl about-page"><h1 class="about-title">Nosotros</h1><p>En Servicios Globales Green creemos que viajar es mucho m&aacute;s que visitar un destino; es vivir experiencias que conectan con la naturaleza, la cultura y la calidez de las personas. Nos dedicamos a brindar servicios de hospedaje y experiencias tur&iacute;sticas en la regi&oacute;n San Mart&iacute;n, ofreciendo espacios c&oacute;modos, seguros y rodeados de la belleza de la Amazon&iacute;a peruana.</p><p>Nuestro compromiso es hacer que cada visitante disfrute de una estad&iacute;a memorable, combinando atenci&oacute;n personalizada, comodidad y el encanto natural que caracteriza a nuestra regi&oacute;n. Trabajamos para promover un turismo responsable y sostenible, contribuyendo al desarrollo local y valorando la riqueza cultural y ambiental de San Mart&iacute;n.</p><p>Ya sea que nos visites por descanso, aventura, trabajo o vacaciones en familia, encontrar&aacute;s un lugar preparado para brindarte tranquilidad, confianza y una experiencia aut&eacute;ntica en el coraz&oacute;n de la Amazon&iacute;a.</p><div class="row about-mv"><div class="col-md"><div class="about-card"><h2>Misi&oacute;n</h2><p>Brindar servicios de hospedaje y experiencias tur&iacute;sticas de calidad en la regi&oacute;n San Mart&iacute;n, ofreciendo atenci&oacute;n personalizada, comodidad y seguridad, promoviendo el turismo sostenible y generando experiencias memorables para nuestros visitantes.</p></div></div><div class="col-md"><div class="about-card"><h2>Visi&oacute;n</h2><p>Ser una empresa referente en hospedaje y turismo de la Amazon&iacute;a peruana, reconocida por la excelencia en el servicio, la innovaci&oacute;n, el compromiso con el desarrollo sostenible y la promoci&oacute;n de los atractivos naturales y culturales de la regi&oacute;n San Mart&iacute;n.</p></div></div></div><h2 class="about-subtitle">Nuestros valores</h2><div class="about-values"><div class="value-item"><span class="value-name">Hospitalidad:</span> Atendemos a cada hu&eacute;sped con calidez y respeto.</div><div class="value-item"><span class="value-name">Compromiso:</span> Trabajamos para superar las expectativas de nuestros visitantes.</div><div class="value-item"><span class="value-name">Calidad:</span> Buscamos la mejora continua en cada uno de nuestros servicios.</div><div class="value-item"><span class="value-name">Responsabilidad:</span> Promovemos pr&aacute;cticas sostenibles y el cuidado del medio ambiente.</div><div class="value-item"><span class="value-name">Integridad:</span> Actuamos con honestidad, transparencia y &eacute;tica.</div><div class="value-item"><span class="value-name">Innovaci&oacute;n:</span> Incorporamos soluciones tecnol&oacute;gicas para mejorar la experiencia del cliente.</div></div><div class="about-contact" id="contacto"><h2>Cont&aacute;ctanos</h2><p>Av. Oasis S/N, Morales, San Mart&iacute;n</p><p>Tel. 910964688</p><p><a class="about-wa" href="https://wa.me/51910964688" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg> Escr&iacute;benos por WhatsApp</a></p></div></div>
HTML;

$json = json_encode(['css' => '', 'html' => $html], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$cmsManager = \Aimeos\MShop::create($c, 'cms');
$textManager = \Aimeos\MShop::create($c, 'text');

$existingId = DB::table('mshop_cms')->whereIn('url', ['about', '/about'])->value('id');

if ($existingId) {
    $item = $cmsManager->get($existingId, ['text']);
    $textItem = null;
    foreach ($item->getListItems('text') as $li) {
        $ref = $li->getRefItem();
        if ($ref && $ref->getType() === 'content') { $textItem = $ref; break; }
    }
    if ($textItem) {
        $textItem->setContent($json);
    } else {
        $textItem = $textManager->create()->setLabel('Empresa: contenido')->setType('content')->setDomain('cms')->setStatus(1)->setContent($json);
        $item->addListItem('text', $cmsManager->createListItem()->setType('default'), $textItem);
    }
    $item->setLabel('Empresa')->setStatus(1);
    $cmsManager->save($item);
    echo 'Pagina Empresa ACTUALIZADA (id '.$item->getId().')'.PHP_EOL;
} else {
    $item = $cmsManager->create()->setUrl('about')->setLabel('Empresa')->setStatus(1);
    $textItem = $textManager->create()->setLabel('Empresa: contenido')->setType('content')->setDomain('cms')->setStatus(1)->setContent($json);
    $item->addListItem('text', $cmsManager->createListItem()->setType('default'), $textItem);
    $cmsManager->save($item);
    echo 'Pagina Empresa CREADA (id '.$item->getId().')'.PHP_EOL;
}

/* ----------------------------------------------------------------------
 * 2) Enlaces de redes sociales en la configuracion del sitio
 * -------------------------------------------------------------------- */

$siteManager = \Aimeos\MShop::create($c, 'locale/site');
$site = $siteManager->get(1);
$config = $site->getConfig();

$config['social'] = [
    'facebook'  => 'https://www.facebook.com/profile.php?id=61591652023764',
    'instagram' => 'https://www.instagram.com/',
    'twitter'   => '',
    'youtube'   => '',
];

$site->setConfig($config);
$siteManager->save($site);

echo 'Config de redes sociales guardada: '.json_encode($siteManager->get(1)->getConfigValue('social')).PHP_EOL;
echo 'Listo.'.PHP_EOL;
