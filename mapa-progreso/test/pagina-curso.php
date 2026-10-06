<?php
// Simula la página de curso de BuddyBoss + LearnDash con los bloques duplicados de la captura,
// y el mapa metido en un widget de Elementor. Uso: php test/pagina-curso.php 1 > test/out/pagina.html
ob_start(); require __DIR__ . '/render.php'; $mapa = ob_get_clean();
$mapa = substr( $mapa, strpos( $mapa, '<div class="gd-mapa' ) );
$prog = '<div class="ld-course-status"><div class="ld-progress"><b>100% Complete</b> — Last activity…</div><span class="ld-status">Completado</span></div>';
$lista = '<div class="ld-section-heading"><h2>Contenido del Curso</h2></div><div class="ld-item-list"><s>Capítulo 1</s> ✓</div>';
?>
<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{margin:0;font-family:sans-serif;background:#f6f7f9}.site-header{height:70px;background:#fff;border-bottom:1px solid #ddd;display:flex;align-items:center;justify-content:flex-end;padding:0 40px}
.bb-learndash-banner{background:#454e5f;color:#fff;padding:60px 230px;height:220px}.container{max-width:1200px;margin:0 auto}
.bb-grid{display:flex;gap:40px}#primary{flex:0 0 60%;max-width:60%}.bb-single-course-sidebar{flex:1;background:#fff;height:480px;margin-top:-280px;border-radius:8px}
.ld-course-status,.ld-item-list{background:#eef0f4;padding:20px;margin:20px 0}.site-footer{background:#222;color:#fff;padding:30px;margin-top:40px}</style>
<header id="masthead" class="site-header">tuservic-admin</header>
<div id="page"><div id="content" class="site-content">
<div class="bb-learndash-banner"><h1>Patient Journey en Acción</h1>View Curso details</div>
<div class="container"><div class="bb-grid">
 <div id="primary" class="content-area"><article>
  <div class="learndash-wrapper"><?php echo $prog . $prog; ?>
   <div class="ld-tab-content"><div class="elementor"><section class="elementor-section"><div class="elementor-column"><div class="elementor-widget-shortcode"><div class="elementor-shortcode"><?php echo $mapa; ?></div></div></div></section></div></div>
   <?php echo $lista . $lista; ?>
  </div>
 </article></div>
 <aside class="bb-single-course-sidebar"><button>Completado</button> Curso Includes</aside>
</div></div></div></div>
<footer class="site-footer">Pie del sitio</footer>
