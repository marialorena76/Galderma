<?php
// Arnés de prueba: simula WordPress + LearnDash y renderiza el shortcode.
// Uso: php test/render.php <completados separados por coma | visitante> > salida.html
define( 'ABSPATH', __DIR__ );
$arg  = $argv[1] ?? '';
$GLOBALS['done'] = $arg === '' ? array() : array_map( 'intval', explode( ',', $arg ) );
function shortcode_atts( $d, $a ) { return array_merge( $d, (array) $a ); }
function add_shortcode( $t, $f ) { $GLOBALS['sc'] = $f; }
function content_url( $p ) { return str_replace( '/uploads/academia/mapa', '../../img', $p ); }
function get_current_user_id() { return $GLOBALS['argv'][1] === 'visitante' ? 0 : 7; }
function learndash_get_course_id() { return 99; }
function learndash_course_get_steps_by_type( $c, $t ) { return array( 101, 102, 103, 104, 105, 106 ); }
function learndash_is_lesson_complete( $u, $l, $c ) { return in_array( $l - 100, $GLOBALS['done'], true ); }
function learndash_get_step_permalink( $l, $c ) { return 'https://soyloregonzalez.com.ar/experienciaconproposito/lessons/capitulo-' . ( $l - 100 ) . '/'; }
function learndash_lesson_progression_enabled( $c ) { return true; }
function esc_url( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
require __DIR__ . '/../mapa-progreso.php';
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"><body style="margin:0;padding:24px 16px;background:#f6f4f2">';
echo call_user_func( $GLOBALS['sc'], array() );
