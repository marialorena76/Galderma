<?php
// Simula una lección de LearnDash con el capítulo embebido y el botón "Marcar como completado",
// e imprime el script del snippet. Uso: php test/leccion.php > test/out/sitio/leccion.html
define( 'ABSPATH', __DIR__ );
$GLOBALS['acciones'] = array();
function add_shortcode() {}
function add_action( $h, $f ) { $GLOBALS['acciones'][ $h ][] = $f; }
function is_singular( $t ) { return 'sfwd-lessons' === $t; }
function is_user_logged_in() { return true; }
function get_the_ID() { return 102; }
function learndash_get_course_id( $id = 0 ) { return 99; }
function wp_json_encode( $v ) { return json_encode( $v, JSON_UNESCAPED_SLASHES ); }
function admin_url( $p ) { return '/wp-admin/' . $p; }
function wp_create_nonce( $a ) { return 'nonce-de-prueba'; }
require __DIR__ . '/../mapa-progreso.php';
?><!doctype html><meta charset="utf-8"><title>Capítulo 2</title>
<body style="margin:0;font-family:sans-serif">
<iframe id="gd-cap2" src="/wp-content/uploads/academia/capitulo2.html" style="width:100%;height:700px;border:0"></iframe>
<form class="sfwd-mark-complete" method="post"><input type="submit" class="learndash_mark_complete_button" value="Marcar como completado"></form>
<?php foreach ( $GLOBALS['acciones']['wp_footer'] as $f ) { $f(); } ?>
