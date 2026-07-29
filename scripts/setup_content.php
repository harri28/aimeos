<?php

/**
 * Crea/actualiza contenido de Kallpa Room que vive en la base de datos
 * (no viaja con git): las paginas CMS "Empresa" (/p/about) y "Terminos y
 * condiciones" (/p/terms), la categoria y los productos de "Eventos" de
 * ejemplo, y los enlaces de redes sociales del sitio.
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

$cmsManager = \Aimeos\MShop::create($c, 'cms');
$textManager = \Aimeos\MShop::create($c, 'text');

/**
 * Crea o actualiza una pagina CMS con el HTML dado.
 */
function upsertCmsPage($cmsManager, $textManager, string $url, string $label, string $html): void
{
    $json = json_encode(['css' => '', 'html' => $html], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $existingId = DB::table('mshop_cms')->whereIn('url', [$url, '/'.$url])->value('id');

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
            $textItem = $textManager->create()->setLabel($label.': contenido')->setType('content')->setDomain('cms')->setStatus(1)->setContent($json);
            $item->addListItem('text', $cmsManager->createListItem()->setType('default'), $textItem);
        }
        $item->setLabel($label)->setStatus(1);
        $cmsManager->save($item);
        echo "Pagina $label ACTUALIZADA (id ".$item->getId().")".PHP_EOL;
    } else {
        $item = $cmsManager->create()->setUrl($url)->setLabel($label)->setStatus(1);
        $textItem = $textManager->create()->setLabel($label.': contenido')->setType('content')->setDomain('cms')->setStatus(1)->setContent($json);
        $item->addListItem('text', $cmsManager->createListItem()->setType('default'), $textItem);
        $cmsManager->save($item);
        echo "Pagina $label CREADA (id ".$item->getId().")".PHP_EOL;
    }
}

/* ----------------------------------------------------------------------
 * 1) Pagina CMS "Empresa" (/p/about)
 * -------------------------------------------------------------------- */

$aboutHtml = <<<'HTML'
<div class="container-xl about-page"><h1 class="about-title">Nosotros</h1><p>En Servicios Globales Green creemos que viajar es mucho m&aacute;s que visitar un destino; es vivir experiencias que conectan con la naturaleza, la cultura y la calidez de las personas. Nos dedicamos a brindar servicios de hospedaje y experiencias tur&iacute;sticas en la regi&oacute;n San Mart&iacute;n, ofreciendo espacios c&oacute;modos, seguros y rodeados de la belleza de la Amazon&iacute;a peruana.</p><p>Nuestro compromiso es hacer que cada visitante disfrute de una estad&iacute;a memorable, combinando atenci&oacute;n personalizada, comodidad y el encanto natural que caracteriza a nuestra regi&oacute;n. Trabajamos para promover un turismo responsable y sostenible, contribuyendo al desarrollo local y valorando la riqueza cultural y ambiental de San Mart&iacute;n.</p><p>Ya sea que nos visites por descanso, aventura, trabajo o vacaciones en familia, encontrar&aacute;s un lugar preparado para brindarte tranquilidad, confianza y una experiencia aut&eacute;ntica en el coraz&oacute;n de la Amazon&iacute;a.</p><div class="row about-mv"><div class="col-md"><div class="about-card"><h2>Misi&oacute;n</h2><p>Brindar servicios de hospedaje y experiencias tur&iacute;sticas de calidad en la regi&oacute;n San Mart&iacute;n, ofreciendo atenci&oacute;n personalizada, comodidad y seguridad, promoviendo el turismo sostenible y generando experiencias memorables para nuestros visitantes.</p></div></div><div class="col-md"><div class="about-card"><h2>Visi&oacute;n</h2><p>Ser una empresa referente en hospedaje y turismo de la Amazon&iacute;a peruana, reconocida por la excelencia en el servicio, la innovaci&oacute;n, el compromiso con el desarrollo sostenible y la promoci&oacute;n de los atractivos naturales y culturales de la regi&oacute;n San Mart&iacute;n.</p></div></div></div><h2 class="about-subtitle">Nuestros valores</h2><div class="about-values"><div class="value-item"><span class="value-name">Hospitalidad:</span> Atendemos a cada hu&eacute;sped con calidez y respeto.</div><div class="value-item"><span class="value-name">Compromiso:</span> Trabajamos para superar las expectativas de nuestros visitantes.</div><div class="value-item"><span class="value-name">Calidad:</span> Buscamos la mejora continua en cada uno de nuestros servicios.</div><div class="value-item"><span class="value-name">Responsabilidad:</span> Promovemos pr&aacute;cticas sostenibles y el cuidado del medio ambiente.</div><div class="value-item"><span class="value-name">Integridad:</span> Actuamos con honestidad, transparencia y &eacute;tica.</div><div class="value-item"><span class="value-name">Innovaci&oacute;n:</span> Incorporamos soluciones tecnol&oacute;gicas para mejorar la experiencia del cliente.</div></div><div class="about-contact" id="contacto"><h2>Cont&aacute;ctanos</h2><p>Av. Oasis S/N, Morales, San Mart&iacute;n</p><p>Tel. 910964688</p><p><a class="about-wa" href="https://wa.me/51910964688" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg> Escr&iacute;benos por WhatsApp</a></p></div></div>
HTML;

/* ----------------------------------------------------------------------
 * 2) Pagina CMS "Terminos y condiciones" (/p/terms)
 * -------------------------------------------------------------------- */

$termsHtml = <<<'HTML'
<div class="container-xl legal-page"><h1 class="legal-title">T&eacute;rminos y condiciones de uso</h1><p class="legal-updated">&Uacute;ltima actualizaci&oacute;n: 28 de julio de 2026</p><p>Bienvenido al sitio web de Servicios Globales Green E.I.R.L. Al acceder y utilizar este sitio web, el usuario acepta los presentes T&eacute;rminos y Condiciones. Si no est&aacute; de acuerdo con alguno de ellos, le recomendamos no utilizar nuestros servicios.</p><h2>1. Objeto</h2><p>El presente documento regula el acceso, navegaci&oacute;n y uso del sitio web, as&iacute; como la contrataci&oacute;n de servicios de hospedaje, paquetes tur&iacute;sticos y dem&aacute;s servicios ofrecidos por Servicios Globales Green E.I.R.L.</p><h2>2. Uso del sitio web</h2><p>El usuario se compromete a:</p><ul><li>Proporcionar informaci&oacute;n veraz durante el proceso de registro o reserva.</li><li>Utilizar el sitio &uacute;nicamente para fines legales.</li><li>No realizar acciones que afecten la seguridad, disponibilidad o funcionamiento del portal.</li><li>No utilizar el sitio para actividades fraudulentas o il&iacute;citas.</li></ul><h2>3. Reservas</h2><p>Las reservas estar&aacute;n sujetas a la disponibilidad de habitaciones, paquetes tur&iacute;sticos y dem&aacute;s servicios ofrecidos.</p><p>La empresa se reserva el derecho de aceptar o rechazar una reserva cuando:</p><ul><li>Existan errores en la informaci&oacute;n proporcionada.</li><li>No se confirme el pago correspondiente.</li><li>Se detecten actividades sospechosas o fraudulentas.</li></ul><p>Una vez confirmada la reserva, el usuario recibir&aacute; una confirmaci&oacute;n mediante correo electr&oacute;nico y/o WhatsApp.</p><h2>4. Tarifas y pagos</h2><p>Los precios publicados incluyen los impuestos aplicables, salvo indicaci&oacute;n expresa.</p><p>Los pagos podr&aacute;n realizarse mediante los m&eacute;todos habilitados en la plataforma.</p><p>La reserva ser&aacute; considerada confirmada &uacute;nicamente cuando el pago haya sido aprobado por la pasarela de pagos correspondiente.</p><h2>5. Modificaciones y cancelaciones</h2><p>El cliente podr&aacute; solicitar modificaciones o cancelaciones conforme a las pol&iacute;ticas vigentes de la empresa.</p><p>Dependiendo de la fecha de cancelaci&oacute;n, podr&aacute;n aplicarse cargos administrativos o penalidades.</p><p>En caso de fuerza mayor o situaciones extraordinarias, la empresa evaluar&aacute; cada solicitud de manera individual.</p><h2>6. Check-in y Check-out</h2><p>Los horarios de ingreso y salida ser&aacute;n comunicados durante el proceso de reserva.</p><p>El incumplimiento de dichos horarios podr&aacute; generar cargos adicionales seg&uacute;n las pol&iacute;ticas internas del establecimiento.</p><h2>7. Responsabilidad del hu&eacute;sped</h2><p>El hu&eacute;sped se compromete a:</p><ul><li>Cuidar las instalaciones y bienes del establecimiento.</li><li>Respetar las normas de convivencia.</li><li>No realizar actividades ilegales dentro del hospedaje.</li><li>Asumir la responsabilidad por los da&ntilde;os ocasionados por negligencia o mal uso de las instalaciones.</li></ul><h2>8. Propiedad intelectual</h2><p>Todo el contenido del sitio web, incluyendo textos, fotograf&iacute;as, logotipos, dise&ntilde;os, software y dem&aacute;s elementos gr&aacute;ficos, pertenece a Servicios Globales Green E.I.R.L. o cuenta con las autorizaciones correspondientes.</p><p>Queda prohibida su reproducci&oacute;n, distribuci&oacute;n o utilizaci&oacute;n sin autorizaci&oacute;n previa y por escrito.</p><h2>9. Protecci&oacute;n de datos personales</h2><p>La informaci&oacute;n proporcionada por los usuarios ser&aacute; tratada conforme a la Ley N.&deg; 29733 - Ley de Protecci&oacute;n de Datos Personales del Per&uacute;.</p><p>Los datos recopilados ser&aacute;n utilizados &uacute;nicamente para:</p><ul><li>Gestionar reservas.</li><li>Procesar pagos.</li><li>Brindar atenci&oacute;n al cliente.</li><li>Enviar informaci&oacute;n relacionada con los servicios contratados.</li><li>Cumplir obligaciones legales.</li></ul><p>La empresa implementa medidas de seguridad para proteger la informaci&oacute;n de sus clientes.</p><h2>10. Disponibilidad del servicio</h2><p>La empresa realizar&aacute; los esfuerzos razonables para mantener el sitio web disponible de forma continua.</p><p>Sin embargo, no garantiza que el servicio est&eacute; libre de interrupciones ocasionadas por mantenimiento, fallas t&eacute;cnicas o causas ajenas a su control.</p><h2>11. Limitaci&oacute;n de responsabilidad</h2><p>Servicios Globales Green E.I.R.L. no ser&aacute; responsable por:</p><ul><li>Fallas en los servicios de Internet del usuario.</li><li>Problemas ocasionados por terceros, incluyendo entidades financieras o pasarelas de pago.</li><li>Eventos de fuerza mayor como desastres naturales, conflictos sociales o restricciones gubernamentales.</li></ul><h2>12. Modificaciones</h2><p>La empresa podr&aacute; actualizar estos T&eacute;rminos y Condiciones en cualquier momento.</p><p>Las modificaciones entrar&aacute;n en vigencia desde su publicaci&oacute;n en este sitio web.</p><h2>13. Legislaci&oacute;n aplicable</h2><p>Los presentes T&eacute;rminos y Condiciones se rigen por las leyes de la Rep&uacute;blica del Per&uacute;.</p><p>Cualquier controversia ser&aacute; resuelta por las autoridades competentes del distrito judicial correspondiente al domicilio de la empresa.</p></div>
HTML;

upsertCmsPage($cmsManager, $textManager, 'about', 'Empresa', $aboutHtml);
upsertCmsPage($cmsManager, $textManager, 'terms', 'Terminos y condiciones', $termsHtml);

/* ----------------------------------------------------------------------
 * 3) Categoria "Eventos" (para gestionar los eventos del inicio).
 *    Se crea como hija de la raiz del catalogo. Se oculta del menu de
 *    navegacion desde la plantilla del arbol (por su codigo "eventos").
 * -------------------------------------------------------------------- */

$catalogManager = \Aimeos\MShop::create($c, 'catalog');

$eventos = null;
try { $eventos = $catalogManager->find('eventos'); } catch (\Throwable $e) {}

if (!$eventos) {
    $rootId = DB::table('mshop_catalog')->where('level', 0)->value('id');
    $eventos = $catalogManager->create()->setCode('eventos')->setLabel('Eventos')->setStatus(1);
    $catalogManager->insert($eventos, $rootId);
    echo 'Categoria "Eventos" CREADA (id '.$eventos->getId().')'.PHP_EOL;
} else {
    echo 'Categoria "Eventos" ya existe (id '.$eventos->getId().')'.PHP_EOL;
}

/* ----------------------------------------------------------------------
 * 3b) Eventos de ejemplo (productos dentro de la categoria "Eventos").
 *     Las imagenes ya viajan con git (public/aimeos/1.d/product/...).
 * -------------------------------------------------------------------- */

$productManager = \Aimeos\MShop::create($c, 'product');
$mediaManager = \Aimeos\MShop::create($c, 'media');

function upsertEventProduct($productManager, $textManager, $mediaManager, $catalogManager, $catalogId, array $data): void
{
    $product = null;
    try { $product = $productManager->find($data['code']); } catch (\Throwable $e) {}

    if (!$product) {
        $product = $productManager->create()->setType('default')->setCode($data['code'])->setLabel($data['label'])->setUrl($data['url'])->setStatus(1);

        $textItem = $textManager->create()->setType('short')->setDomain('product')->setLabel($data['label'].': texto')->setStatus(1)->setContent($data['text']);
        $product->addListItem('text', $productManager->createListItem()->setType('default'), $textItem);

        $mediaItem = $mediaManager->create()->setType('default')->setDomain('product')->setFileSystem('fs-media')
            ->setLabel($data['mediaLabel'])->setUrl($data['mediaLink'])->setPreviews($data['mediaPreviews'])->setMimeType($data['mimetype'])->setStatus(1);
        $product->addListItem('media', $productManager->createListItem()->setType('default'), $mediaItem);

        $productManager->save($product);
        echo 'Producto evento "'.$data['label'].'" CREADO (id '.$product->getId().')'.PHP_EOL;
    } else {
        echo 'Producto evento "'.$data['label'].'" ya existe (id '.$product->getId().')'.PHP_EOL;
    }

    // El indice de busqueda (usado por Controller\Frontend\Product->category())
    // lee mshop_product_list (domain=catalog), del lado del PRODUCTO, no del
    // lado de la categoria. Hay que vincular desde aqui para que el evento
    // aparezca en el listado del inicio.
    $product = $productManager->get($product->getId(), ['catalog']);
    $linked = false;
    foreach ($product->getListItems('catalog') as $li) {
        if ((string) $li->getRefId() === (string) $catalogId) { $linked = true; break; }
    }
    if (!$linked) {
        $catalogItem = $catalogManager->get($catalogId);
        $listItem = $productManager->createListItem()->setType('default')->setDomain('catalog')->setRefId((string) $catalogId)->setStatus(1);
        $product->addListItem('catalog', $listItem, $catalogItem);
        $productManager->save($product);
        echo 'Producto "'.$data['label'].'" vinculado a categoria Eventos.'.PHP_EOL;
    }
}

upsertEventProduct($productManager, $textManager, $mediaManager, $catalogManager, $eventos->getId(), [
    'code' => 'evento-san-juan',
    'label' => 'Festival de San Juan',
    'url' => 'festival-de-san-juan',
    'text' => '24 de junio &mdash; Vive la fiesta m&aacute;s grande de la Amazon&iacute;a en Tarapoto: danzas, gastronom&iacute;a t&iacute;pica y tradici&oacute;n junto al r&iacute;o.',
    'mediaLabel' => 'Tarapoto.jpg',
    'mediaLink' => '1.d/product/0/e/0ef4569c_tarapoto.jpg',
    'mediaPreviews' => [
        '240' => '1.d/product/3/6/36513769_0ef4569c_tarapoto.webp',
        '480' => '1.d/product/6/9/69c67dd3_0ef4569c_tarapoto.webp',
        '960' => '1.d/product/5/4/54a2810f_0ef4569c_tarapoto.webp',
        '1920' => '1.d/product/b/d/bd2d62e9_0ef4569c_tarapoto.webp',
    ],
    'mimetype' => 'image/jpeg',
]);

upsertEventProduct($productManager, $textManager, $mediaManager, $catalogManager, $eventos->getId(), [
    'code' => 'evento-lamas',
    'label' => 'Feria Turística de Lamas',
    'url' => 'feria-turistica-de-lamas',
    'text' => '15 de agosto &mdash; Conoce la cultura viva del pueblo Kechwa-Lamas: artesan&iacute;a, m&uacute;sica y el famoso Castillo de Lamas.',
    'mediaLabel' => 'Lamas.jpg',
    'mediaLink' => '1.d/product/8/f/8fbda936_lamas.jpg',
    'mediaPreviews' => [
        '240' => '1.d/product/c/5/c50b517a_8fbda936_lamas.webp',
        '480' => '1.d/product/6/d/6d541500_8fbda936_lamas.webp',
        '960' => '1.d/product/1/4/148f79fb_8fbda936_lamas.webp',
        '1920' => '1.d/product/d/5/d5fbcad7_8fbda936_lamas.webp',
    ],
    'mimetype' => 'image/jpeg',
]);

/* ----------------------------------------------------------------------
 * 4) Enlaces de redes sociales en la configuracion del sitio
 * -------------------------------------------------------------------- */

$siteManager = \Aimeos\MShop::create($c, 'locale/site');
$site = $siteManager->get(1);
$config = $site->getConfig();

// Valores por defecto SOLO si no existen, para no pisar lo que se edite en el panel.

if (empty($config['social'] ?? null)) {
    $config['social'] = [
        'facebook'  => 'https://www.facebook.com/profile.php?id=61591652023764',
        'instagram' => 'https://www.instagram.com/',
        'twitter'   => '',
        'youtube'   => '',
    ];
    echo 'Config de redes sociales inicializada.'.PHP_EOL;
} else {
    echo 'Config de redes sociales ya existe, se respeta.'.PHP_EOL;
}

if (empty($config['home']['typing'] ?? null)) {
    $config['home']['typing'] = [
        1 => 'Vive la Amazonia peruana en Tarapoto',
        2 => 'Descuentos especiales en nuestras Suites',
        3 => 'Conoce la Laguna Azul y las Cataratas de Ahuashiyacu',
        4 => 'Reserva hoy tu experiencia inolvidable',
    ];
    echo 'Frases del inicio inicializadas.'.PHP_EOL;
} else {
    echo 'Frases del inicio ya existen, se respetan.'.PHP_EOL;
}

// Calendario de proximos acontecimientos (formato "fecha | texto").
if (empty($config['home']['calendar'] ?? null)) {
    $config['home']['calendar'] = [
        1 => '24 Jun | Festival de San Juan en Tarapoto',
        2 => '15 Ago | Feria Turistica de Lamas',
        3 => '20 Feb | Carnaval Amazonico',
        4 => '28 Jul | Fiestas Patrias: tours especiales',
    ];
    echo 'Calendario del inicio inicializado.'.PHP_EOL;
} else {
    echo 'Calendario del inicio ya existe, se respeta.'.PHP_EOL;
}

// Paquete turistico promocionado junto a los eventos del inicio.
if (empty($config['home']['promo'] ?? null)) {
    $config['home']['promo'] = [
        'product'  => 'paquete-aventura-amazonica',
        'discount' => 10,
        'badge'    => 'Oferta -10%',
        'hook'     => '¿Vienes a nuestros eventos? Quédate con nosotros y aprovecha esta oferta.',
    ];
    echo 'Paquete promocionado del inicio inicializado.'.PHP_EOL;
} else {
    echo 'Paquete promocionado del inicio ya existe, se respeta.'.PHP_EOL;
}

$site->setConfig($config);
$siteManager->save($site);

echo 'Listo.'.PHP_EOL;
