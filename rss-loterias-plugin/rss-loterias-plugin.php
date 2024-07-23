<?php
if (!defined('ABSPATH')) exit;

/*
    Plugin Name: RSS Loterías 
    Plugin Description: Muestra los resultados de Loterías y Apuestas del Estado.
    Version: 0.7.0
    Author: Utxra
    Author URI: https:/github.com/utxra
    Description: Plugin que muestra los resultados de las loterías y apuestas del estado.
    Plugin URI: https:/github.com/utxra/wp-plugin-lae
*/

function get_page_url_by_slug($slug)
{

    // Obtener el objeto de la página por su slug
    $page = get_page_by_path($slug);


    // Verificar si la página existe
    if ($page) {
        // Obtener y retornar la URL de la página
        return get_the_permalink($page->ID);
    } else {
        // Retornar un mensaje de error o una URL predeterminada si la página no existe
        return 'Página no encontrada';
    }
}
define('PLUGIN_URL', plugin_dir_url(__FILE__));
define('PATH', plugin_dir_path(__FILE__));
define('PLUGIN_NAME', 'rss-loterias-plugin');
define('LAE', ["euromillones", "la-primitiva", "bonoloto", "gordo-primitiva", "eurodreams", "loteria-nacional", "la-quiniela", "el-quinigol", "lototurf"]);
define('URL', 'http://loteriasanmarcos.es/detalles-loteria');

function fetch_all_loterias_resultados_rss(string $loteria)
{
    if ($loteria == "loteria-nacional") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/loteria-nacional/.formatoRSS");
    } elseif ($loteria == "eurodreams") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/eurodreams/.formatoRSS");
    } else {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/$loteria/resultados/.formatoRSS");
    }

    if (!is_wp_error($rss)) {
        return $rss->get_items(0, $rss->get_item_quantity());
    }

    return [];
}

function fetch_loterias_resultados_rss(string $loteria)
{

    if ($loteria == "loteria-nacional") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/loteria-nacional/.formatoRSS");
    } elseif ($loteria == "eurodreams") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/eurodreams/.formatoRSS");
    } else {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/$loteria/resultados/.formatoRSS");
    }


    if (!is_wp_error($rss)) {
        $rss_items = $rss->get_items(0, 1);
    }

    // ob_start();

    // if (!empty($rss_items)) {

    //     echo '<h2>' . ucfirst(str_replace("-", " ", $loteria)) . '</h2>';
    //     foreach ($rss_items as $item) {
    //         include 'loteria.php';
    //     }

    // } 
    // else {
    //     echo '<p>No hay resultados disponibles en este momento.</p>';
    // }

    // return ob_get_clean();

    if (empty($rss_items)) {
        echo '<p>No hay resultados disponibles en este momento.</p>';
    }

    return $rss_items;
}

function fetch_loterias_botes_rss(string $loteria)
{

    if ($loteria == "loteria-nacional") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/loteria-nacional/.formatoRSS");
    } elseif ($loteria == "eurodreams") {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/eurodreams/.formatoRSS");
    } else {
        $rss = fetch_feed("https://www.loteriasyapuestas.es/es/$loteria/botes/.formatoRSS");
    }

    if (!is_wp_error($rss)) {
        $rss_items = $rss->get_items(0, 1);
    }

    ob_start();

    if (!empty($rss_items)) {

        echo '<h2>' . ucfirst(str_replace("-", " ", $loteria)) . '</h2>';
        foreach ($rss_items as $item) {
            include 'loteria.php';
        }
    } else {
        echo '<p>No hay resultados disponibles en este momento.</p>';
    }

    return ob_get_clean();
}

function fetch_lae_rss()
{

    $lae = LAE;

    ob_start();

    foreach ($lae as $loteria) {
        echo fetch_loterias_resultados_rss($loteria);
    }

    return ob_get_clean();
}

function fetch_custom_resultado_loteria(string $loteria)
{
    return fetch_loterias_resultados_rss($loteria);
}

function register_rss_shortcode($atts)
{

    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'rss_loterias');

    if (!empty($atts['id'])) {
        // Llamar a la función específica si se pasa un ID
        return fetch_custom_resultado_loteria($atts['id']);
    } else {
        // Llamar a la función por defecto si no se pasa un ID
        return fetch_lae_rss();
    }
}

function register_menu_shortcode()
{
    ob_start();

    $lae = LAE;

    include 'menu_cuadricula.php';

    return ob_get_clean();
}

function register_selector_dias_shortcode()
{
    ob_start();

    $loteria = $_POST['loteria'];

    if ($loteria) {
        $resultados = fetch_all_loterias_resultados_rss($loteria);

        include 'selector_dias.php';
    } else {
        // header('Location: https://loteriasanmarcos.es/resultados/');
    }

    return ob_get_clean();
}

function extract_euromillones($descripcion)
{
    // Buscar una combinación de 5 números
    preg_match('/(\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    // Buscar las estrellas
    preg_match('/Estrellas: (\d{2}) - (\d{2})/', $descripcion, $estrellas);

    $estrellas = $estrellas ? "E(" . $estrellas[1] . " - " . $estrellas[2] . ")" : null;

    // Buscar el número del Millón


    preg_match('/Millón: (\w{3}\d{5})/i', $descripcion, $millon);
    $millon = $millon ? $millon[1] : " ";

    return "$numbers $estrellas $millon";
}

function extract_primitiva($descripcion)
{
    // Buscar combinación de 6 números
    preg_match('/(\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    // Buscar el número complementario
    preg_match('/Complementario: C\((\d{2})\)/', $descripcion, $complementario);
    $complementario = $complementario ? "C(" . $complementario[1] . ")" : null;

    // Buscar el reintegro
    preg_match('/Reintegro: R\((\d{1})\)/', $descripcion, $reintegro);
    $reintegro = $reintegro ? "R(" . $reintegro[1] . ")" : null;

    // Buscar el Joker
    preg_match('/Joker: J\((\d{7})\)/', $descripcion, $joker);
    $joker = $joker ? "J(" . $joker[1] . ")" : null;

    return "$numbers $complementario $reintegro $joker";
}

function extract_bonoloto($descripcion)
{
    // Buscar combinación de 6 números
    preg_match('/(\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    // Buscar el número complementario
    preg_match('/Complementario: C\((\d{2})\)/', $descripcion, $complementario);
    $complementario = $complementario ? "C(" . $complementario[1] . ")" : null;

    // Buscar el reintegro
    preg_match('/Reintegro: R\((\d{1})\)/', $descripcion, $reintegro);
    $reintegro = $reintegro ? "R(" . $reintegro[1] . ")" : null;

    return "$numbers $complementario $reintegro";
}


function extract_gordo_primitiva($descripcion)
{
    // Buscar una combinación de 5 números
    preg_match('/(\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    // Buscar el reintegro
    preg_match('/Número clave \(reintegro\): R\((\d+)\)/', $descripcion, $reintegro);
    $reintegro = $reintegro ? "R(" . $reintegro[1] . ")" : null;

    return "$numbers $reintegro";
}

function extract_eurodreams($descripcion)
{
    // Buscar combinación de 6 números
    preg_match('/(\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2}) - (\d{2})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    // Buscar el sueño
    preg_match('/Sueño: (\d{1})/', $descripcion, $sueño);
    $sueño = $sueño ? "S(" . $sueño[1] . ")" : null;

    return "$numbers $sueño";
}

function extract_loteria_nacional($descripcion)
{
    // Buscar una combinación de 5 números
    preg_match('/(\d{5})/', strip_tags($descripcion), $matches);

    if ($matches) {
        $numbers = implode(' ', array_slice($matches, 1, 6));
    } else {
        return null;
    }

    return "$numbers";
}

function extract_la_quiniela($descripcion)
{
    // Ajustar la expresión regular para capturar todos los partidos
    preg_match_all('/(\d+) ([^0-9]+) - ([^0-9]+) ([1X2])/', $descripcion, $matches);

    // Extraer el Pleno al 15
    preg_match('/Pleno al 15 ([^0-9]+) - ([^0-9]+) (\d) - (\d)/', $descripcion, $pleno_matches);

    // Formatear los resultados
    $results = [];
    foreach ($matches[1] as $key => $match) {
        $results[] = "{$matches[1][$key]} {$matches[2][$key]} - {$matches[3][$key]} {$matches[4][$key]} <br>";
        if ($key == 13) {
            break;
        }
    }

    // Formatear el Pleno al 15
    if ($pleno_matches) {
        $results[] = "Pleno al 15 {$pleno_matches[1]} - {$pleno_matches[2]} {$pleno_matches[3]} - {$pleno_matches[4]} <br>";
    }

    // Unir todos los resultados en un solo string
    $formatted_results = implode("", $results);

    // Retornar los resultados formateados
    return $formatted_results;
}


function extract_el_quinigol($descripcion)
{
    // Ajustamos la expresión regular para capturar todos los partidos
    preg_match_all('/(\d+) ([^0-9]+) - ([^0-9]+) ([M01234567890]) - ([M01234567890])/i', $descripcion, $matches);

    if ($matches) {
        $numbers = implode('<br>', $matches[0]);
    } else {
        return null;
    }

    return "$numbers";
}

function extract_lototurf($descripcion)
{
    // Ajustamos la expresión regular para capturar todos las carreras
    preg_match_all('/Carrera (\d+): (\d+)/i', $descripcion, $matches);

    // Extraer el bonus
    preg_match('/Carrera (\d+) \(2º Clasificado\): (\d+)/i', $descripcion, $bonus);

    // Extraer 6 números de la combinación ganadora
    preg_match_all('/(\d+) - (\d+) - (\d+) - (\d+) - (\d+) - (\d+)/', $descripcion, $combinacion);

    if ($matches[0]) {
        $numbers = implode('<br>', $matches[0]);
        return "$numbers <br>";
    } elseif ($combinacion[0]) {

        preg_match_all('/Caballo\((\d+)\)/', $descripcion, $caballo);

        preg_match_all('/Reintegro: R\((\d+)\)/', $descripcion, $reintegro);

        $numbers = $combinacion[0][0] . " " . $caballo[0][0] . " " . $reintegro[0][0];

        return $numbers;
    } else {
        return null;
    }
}

function extract_combination($descripcion, string $loteria)
{
    $descripcion = strip_tags($descripcion);

    $combinacion_ganadora = match ($loteria) {
        "euromillones" => extract_euromillones($descripcion),
        "la-primitiva"  => extract_primitiva($descripcion),
        "bonoloto" => extract_bonoloto($descripcion),
        "gordo-primitiva" => extract_gordo_primitiva($descripcion),
        "eurodreams" => extract_eurodreams($descripcion),
        "loteria-nacional" => extract_loteria_nacional($descripcion),
        "la-quiniela" => extract_la_quiniela($descripcion),
        "el-quinigol" => extract_el_quinigol($descripcion),
        "lototurf" => extract_lototurf($descripcion),
    };

    return $combinacion_ganadora;
}

function extract_all_combinations() {
    $combinaciones = [];

    foreach (LAE as $loteria) {
        $resultados = fetch_all_loterias_resultados_rss($loteria);

        if (!empty($resultados)) {
            // Ordenar los resultados por fecha en orden descendente
            usort($resultados, function($a, $b) {
                return $b->get_date('U') - $a->get_date('U'); // Comparar timestamps
            });

            // Obtener el resultado más reciente
            $resultado_reciente = $resultados[0];
            $fecha = $resultado_reciente->get_date('j-m-Y');
            $combinacion = extract_combination($resultado_reciente->get_description(), $loteria);

            // Guardar la combinación más reciente
            $combinaciones[$loteria] = [
                'name' => $loteria,
                'fecha' => $fecha,
                'combinacion' => $combinacion,
            ];
        } else {
            // Si no hay resultados, indicar que no hay datos disponibles
            $combinaciones[$loteria] = [
                'name' => $loteria,
                'fecha' => 'No disponible',
                'combinacion' => 'No hay combinación disponible',
            ];
        }
    }

    return $combinaciones;
}

function render_combinations($combinaciones) {
    ob_start();
    include 'combinaciones.php';
    return ob_get_clean();
}

function register_combinations_shortcode() {
    $combinaciones = extract_all_combinations();
    return render_combinations($combinaciones);
}

function get_resultado_ajax()
{
    $loteria = $_GET['loteria'];
    $date = $_GET['date'];

    $resultados = fetch_all_loterias_resultados_rss($loteria);
    foreach ($resultados as $resultado) {
        if ($resultado->get_date('j-m-Y') == $date) {
            $response = [
                'date' => $resultado->get_date('j-m-Y | g:i a'),
                'combination' => extract_combination($resultado->get_description(), $loteria),
                'description' => $resultado->get_description(),
                'link' => $resultado->get_permalink()
            ];
            echo json_encode($response);
            wp_die();
        }
    }

    echo json_encode(['error' => 'No hay resultados disponibles para esta fecha.']);
    wp_die();
}
add_action('wp_ajax_get_resultado', 'get_resultado_ajax');
add_action('wp_ajax_nopriv_get_resultado', 'get_resultado_ajax');



function loteria_enqueue_styles()
{
    wp_enqueue_style('rss-loterias-plugin', plugins_url('css/style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'loteria_enqueue_styles');

add_shortcode('rss_loterias', 'register_rss_shortcode');

add_shortcode('menu_loterias', 'register_menu_shortcode');

add_shortcode('selector_dias', 'register_selector_dias_shortcode');

add_shortcode('last_combinations', 'register_combinations_shortcode');