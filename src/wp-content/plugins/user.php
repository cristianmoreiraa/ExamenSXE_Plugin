<?php
/**
 * @package Gestion de Notas
 * @version 1.0.7
 */
/*
Plugin Name: Gestion de Notas
Plugin URI: http://wordpress.org/plugins/gestion-notas/
Description: Plugin para gestionar las notas de los alumnos en tres trimestres.
Author: CristianMoreira
Version: 1.0.9
Author URI: http://tu-sitio-web.com/
*/

// Función para crear la tabla de notas en la base de datos
function crearTablaNotas() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notas';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        alumno varchar(255) NOT NULL,
        trimestre_1 decimal(4,2) NOT NULL,
        trimestre_2 decimal(4,2) NOT NULL,
        trimestre_3 decimal(4,2) NOT NULL,
        media decimal(4,2) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Función para insertar datos de ejemplo o proporcionados desde fuera
function insertarDatos($datos) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notas';

    foreach ($datos as $dato) {
        $wpdb->insert($table_name, $dato);
    }
}

// Función para calcular la media de las notas
function calcularMedia($trimestre_1, $trimestre_2, $trimestre_3) {
    return number_format((float)(($trimestre_1 + $trimestre_2 + $trimestre_3) / 3), 2, '.', '');
}

// Función para actualizar las medias en la base de datos
function actualizarMedias() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notas';

    $alumnos = $wpdb->get_results("SELECT * FROM $table_name");

    foreach ($alumnos as $alumno) {
        $media = calcularMedia($alumno->trimestre_1, $alumno->trimestre_2, $alumno->trimestre_3);

        $wpdb->update(
            $table_name,
            array('media' => $media),
            array('id' => $alumno->id)
        );
    }
}

// Función para mostrar las notas utilizando un shortcode
function mostrarNotasShortcode($atts) {
    ob_start();
    mostrarNotas();
    return ob_get_clean();
}

// Función para mostrar las notas
function mostrarNotas() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notas';

    $alumnos = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Alumno</th><th>Trimestre 1</th><th>Trimestre 2</th><th>Trimestre 3</th><th>Media</th></tr></thead><tbody>';

    foreach ($alumnos as $alumno) {
        echo '<tr>';
        echo '<td>' . $alumno->alumno . '</td>';
        echo '<td>' . $alumno->trimestre_1 . '</td>';
        echo '<td>' . $alumno->trimestre_2 . '</td>';
        echo '<td>' . $alumno->trimestre_3 . '</td>';
        echo '<td>' . $alumno->media . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

// Acción de activación del plugin
register_activation_hook(__FILE__, 'crearTablaNotas');

// Acción para permitir la inserción de datos desde fuera
if (isset($_GET['insertar_notas'])) {
    $datos_personalizados = array(
        array('alumno' => 'Jorge', 'trimestre_1' => 7.5, 'trimestre_2' => 8.0, 'trimestre_3' => 7.8),
        array('alumno' => 'Chechu', 'trimestre_1' => 6.2, 'trimestre_2' => 7.5, 'trimestre_3' => 6.8),
        array('alumno' => 'Marcos', 'trimestre_1' => 8.5, 'trimestre_2' => 9.0, 'trimestre_3' => 8.8),
        array('alumno' => 'Popi', 'trimestre_1' => 5.5, 'trimestre_2' => 6.0, 'trimestre_3' => 5.8),
    );

    insertarDatos($datos_personalizados);
    actualizarMedias();
}

// Registrar el shortcode
add_shortcode('Notas_DAM', 'mostrarNotasShortcode');

// Filtro para cambiar el título de la página
function cambiarTituloPagina($title) {
    // Cambiar el título solo si estamos en una página de notas
    if (is_page() && get_the_title() === 'Notas') {
        return 'Notas DAM y sus Medias';
    }
    return $title;
}

add_filter('the_title', 'cambiarTituloPagina', 999, 1);


