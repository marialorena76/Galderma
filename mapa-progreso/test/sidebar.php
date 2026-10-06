<?php
// Réplica de la barra lateral de lección de BuddyBoss (como en la captura) + el estilo del snippet.
// Uso: php test/sidebar.php [sin] > test/out/sidebar.html   ("sin" = sin el snippet, para comparar)
define( 'ABSPATH', __DIR__ );
$GLOBALS['acciones'] = array();
function add_shortcode() {}
function add_action( $h, $f ) { $GLOBALS['acciones'][ $h ][] = $f; }
function is_singular( $t ) { return is_array( $t ) ? in_array( 'sfwd-lessons', $t, true ) : 'sfwd-lessons' === $t; }
function is_user_logged_in() { return true; }
function get_the_ID() { return 103; }
function learndash_get_course_id( $id = 0 ) { return 99; }
function wp_json_encode( $v ) { return json_encode( $v, JSON_UNESCAPED_SLASHES ); }
function admin_url( $p ) { return '/wp-admin/' . $p; }
function wp_create_nonce( $a ) { return 'n'; }
require __DIR__ . '/../mapa-progreso.php';
$con = ( $argv[1] ?? '' ) !== 'sin';
$li = function ( $n, $estado ) {
	$cls = 'lms-lesson-item' . ( 'hecho' === $estado ? ' lms-is-complete' : '' ) . ( 'actual' === $estado ? ' current' : '' );
	$chk = 'hecho' === $estado ? '<span class="bb-lms-status done"><svg viewBox="0 0 24 24" width="22" height="22"><circle cx="12" cy="12" r="11" fill="#385DFF"/><path d="M7 12.5l3.5 3.5L17 9" fill="none" stroke="#fff" stroke-width="2"/></svg></span>'
		: '<span class="bb-lms-status" style="width:20px;height:20px;border:2px solid #d6d9e0;border-radius:50%"></span>';
	return "<li class=\"$cls\"><div class=\"bb-lesson-head\"><a href=\"#\" style=\"" . ( 'hecho' === $estado ? 'text-decoration:line-through' : '' ) . "\">Capítulo $n</a>$chk</div></li>";
};
?><!doctype html><meta charset="utf-8"><title>Lección</title>
<?php if ( $con ) foreach ( $GLOBALS['acciones']['wp_head'] as $f ) { $f(); } ?>
<style>body{margin:0;font-family:Arial,sans-serif;background:#fff}
.lms-topic-sidebar-wrapper{width:370px;background:#f8f9fb;padding:30px 0;min-height:860px}
.ld-course-navigation-heading,.lms-progress-wrap,.bb-participants{padding:0 32px}
.course-entry-link{display:inline-block;background:#eef0f4;color:#6b7280;border-radius:6px;padding:7px 12px;font-size:13px;text-decoration:none}
.course-entril-title{font-size:26px;margin:18px 0}
.ld-progress-bar{height:4px;background:#d6d9e0}.ld-progress-bar-percentage{height:100%;background:#385DFF}
.ld-progress-percentage,.ld-progress-last-activity{font-size:12px;color:#9ca3af;font-weight:bold;display:block;margin-top:10px}
ul{list-style:none;margin:20px 0;padding:0;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb}
.bb-lesson-head{display:flex;justify-content:space-between;align-items:center;padding:18px 32px}
.bb-lesson-head a{color:#1f2937;text-decoration:none;font-size:14px}.lms-lesson-item.current{background:#f2f3f6}
</style>
<div class="lms-topic-sidebar-wrapper">
 <div class="ld-course-navigation-heading"><h3><a class="course-entry-link" href="#"><span>‹ Back to Curso</span></a></h3>
 <h2 class="course-entril-title">Patient Journey en Acción</h2></div>
 <div class="lms-progress-wrap"><div class="ld-progress"><div class="ld-progress-bar"><div class="ld-progress-bar-percentage" style="width:33%"></div></div>
 <span class="ld-progress-percentage">33% Complete</span><span class="ld-progress-last-activity">Last activity on octubre 6, 2026 3:17 pm</span></div></div>
 <ul class="bb-lessons-list"><?php echo $li(1,'hecho').$li(2,'hecho').$li(3,'actual').$li(4,'').$li(5,'').$li(6,''); ?></ul>
 <div class="bb-participants"><h4>Participants <span>0</span></h4><p>hola@soyloregonzalez.com</p><p>tuservic-admin</p></div>
</div>
<?php if ( $con ) foreach ( $GLOBALS['acciones']['wp_footer'] as $f ) { if ( 'gd_leccion_completar_script' !== $f ) $f(); } ?>
