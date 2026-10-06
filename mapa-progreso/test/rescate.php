<?php
// Simula la página del curso para un visitante con el shortcode sin ejecutar ("[mapa_progreso]"
// como texto) y verifica que el snippet lo reemplace por el mapa. Uso: php test/rescate.php
define( 'ABSPATH', __DIR__ );
$GLOBALS['acc'] = array();
function add_shortcode() {}
function add_action( $h, $f ) { $GLOBALS['acc'][ $h ][] = $f; }
function is_singular( $t ) { return 'sfwd-courses' === $t; }
function get_queried_object_id() { return 99; }
function shortcode_parse_atts( $s ) { return trim( $s ) === '' ? '' : array( 'barra' => 'si' ); }
function shortcode_atts( $d, $a ) { return array_merge( $d, (array) $a ); }
function get_current_user_id() { return 0; }
function content_url( $p ) { return '/wp-content' . $p; }
function learndash_course_get_steps_by_type( $c, $t ) { return array( 101, 102, 103, 104, 105, 106 ); }
function learndash_get_step_permalink( $l, $c ) { return '/lessons/capitulo-' . ( $l - 100 ) . '/'; }
function learndash_lesson_progression_enabled( $c ) { return true; }
function learndash_get_setting( $c, $k ) { return 'open'; }
function esc_url( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
require __DIR__ . '/../mapa-progreso.php';
foreach ( $GLOBALS['acc']['template_redirect'] as $f ) { $f(); }
echo '<div class="entry-content"><p>[mapa_progreso]</p><h2>Contenido del Curso</h2></div>';
foreach ( $GLOBALS['acc']['shutdown'] as $f ) { $f(); }
