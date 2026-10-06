<?php
/**
 * Mapa de progreso — "Experiencia con propósito"
 *
 * Shortcode: [mapa_progreso]
 *   Opcional: [mapa_progreso curso="123"] si se usa fuera de la página del curso.
 *   Opcional: [mapa_progreso barra="no"] saca la barra "Vas por el Capítulo N · 2/6 · Continuar" de abajo.
 *   Opcional: [mapa_progreso solo="no"] deja visible el resto de la página del curso.
 *     Por defecto, al alumno con sesión le muestra solo el mapa: oculta el banner del curso, la
 *     tarjeta lateral, las barras "100% Complete" y los listados "Contenido del Curso".
 *     Al visitante sin sesión no le oculta nada, para que vea cómo inscribirse.
 *
 * Muestra la ilustración del recorrido con el avance real del alumno en LearnDash:
 * las salas de los capítulos hechos y el actual a color, el resto en gris, un tilde
 * en los completos y un pin "Estás acá" en el que le toca. Cada sala lleva a su lección.
 *
 * Las imágenes van en wp-content/uploads/academia/mapa/ (mapa-portada, mapa-1 … mapa-6).
 * mapa-N = salas 1..N a color.
 *
 * Al final del archivo: completado automático de cada capítulo al terminarlo (sin el botón
 * "Marcar como completado" de LearnDash).
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'gd_mapa_salas' ) ) {

	/**
	 * Las seis salas, en el orden de las lecciones del curso.
	 * x/y/w/h: zona clickeable, en % de la imagen (1920×1080).
	 * px/py: dónde va el pin "Estás acá" (y el candado), en % de la imagen.
	 * cx/cy: dónde va el tilde de completado (a la izquierda del número), en % de la imagen.
	 */
	function gd_mapa_salas() {
		return array(
			array( 'n' => '01', 't' => 'Descubrimiento y primer contacto',       'x' => 2.1,  'y' => 39.5, 'w' => 15.1,  'h' => 49.4, 'px' => 10.4, 'py' => 66.0, 'cx' => 1.98, 'cy' => 42.2 ),
			array( 'n' => '02', 't' => 'Consulta y recepción',                   'x' => 17.2, 'y' => 25.9, 'w' => 16.15, 'h' => 43.3, 'px' => 25.0, 'py' => 50.5, 'cx' => 15.99, 'cy' => 28.24 ),
			array( 'n' => '03', 't' => 'Presupuesto y propuesta de tratamiento', 'x' => 33.35,'y' => 35.8, 'w' => 16.15, 'h' => 39.5, 'px' => 41.0, 'py' => 59.5, 'cx' => 33.59, 'cy' => 38.7 ),
			array( 'n' => '04', 't' => 'Momento del tratamiento',                'x' => 49.5, 'y' => 24.1, 'w' => 16.4,  'h' => 45.1, 'px' => 57.7, 'py' => 49.0, 'cx' => 48.7, 'cy' => 26.48 ),
			array( 'n' => '05', 't' => 'Seguimiento post-tratamiento',           'x' => 65.9, 'y' => 36.4, 'w' => 15.1,  'h' => 45.7, 'px' => 74.0, 'py' => 62.0, 'cx' => 64.84, 'cy' => 39.35 ),
			array( 'n' => '06', 't' => 'Recomendación y fidelización',           'x' => 81.0, 'y' => 24.7, 'w' => 17.45, 'h' => 44.5, 'px' => 89.6, 'py' => 49.0, 'cx' => 80.99, 'cy' => 27.31 ),
		);
	}

	/** IDs de las lecciones del curso, en orden. */
	function gd_mapa_lecciones( $course_id ) {
		if ( function_exists( 'learndash_course_get_steps_by_type' ) ) {
			$ids = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
		} elseif ( function_exists( 'learndash_get_course_lessons_list' ) ) {
			$ids = wp_list_pluck( wp_list_pluck( learndash_get_course_lessons_list( $course_id, null, array( 'per_page' => 0 ) ), 'post' ), 'ID' );
		} else {
			$ids = array();
		}
		return array_values( array_map( 'intval', (array) $ids ) );
	}

	function gd_mapa_shortcode( $atts ) {
		$atts      = shortcode_atts( array( 'curso' => 0, 'barra' => 'si', 'solo' => 'si' ), $atts, 'mapa_progreso' );
		$course_id = $atts['curso'] ? (int) $atts['curso'] : ( function_exists( 'learndash_get_course_id' ) ? (int) learndash_get_course_id() : 0 );
		$img_base  = content_url( '/uploads/academia/mapa/' );
		$salas     = gd_mapa_salas();
		$user_id   = get_current_user_id();

		// Fuera de un curso: la portada a color, sin progreso.
		if ( ! $course_id ) {
			return '<div class="gd-mapa"><img class="gd-mapa__img" src="' . esc_url( $img_base . 'mapa-portada.webp' ) . '" width="1920" height="1080" alt="Experiencia con propósito: de la recepción a la fidelización" loading="lazy"></div>' . gd_mapa_estilos();
		}

		$lecciones = array_slice( gd_mapa_lecciones( $course_id ), 0, count( $salas ) );
		$lineal    = function_exists( 'learndash_lesson_progression_enabled' ) ? learndash_lesson_progression_enabled( $course_id ) : true;

		// Link de cada sala. Si la lección no está cargada en el curso (Constructor de LearnDash),
		// se busca igual por su slug "capitulo-N" para que la sala no quede muerta.
		$urls = array();
		foreach ( $salas as $i => $s ) {
			if ( isset( $lecciones[ $i ] ) ) {
				$urls[ $i ] = function_exists( 'learndash_get_step_permalink' ) ? learndash_get_step_permalink( $lecciones[ $i ], $course_id ) : get_permalink( $lecciones[ $i ] );
			} else {
				$suelta     = get_page_by_path( 'capitulo-' . ( $i + 1 ), OBJECT, 'sfwd-lessons' );
				$urls[ $i ] = $suelta ? get_permalink( $suelta ) : '';
			}
		}

		$abierto = function_exists( 'learndash_get_setting' ) && 'open' === learndash_get_setting( $course_id, 'course_price_type' );
		// "Solo el mapa": al alumno con sesión siempre; al visitante solo si el curso es abierto
		// (si no, necesita ver la tarjeta para inscribirse).
		$solo  = 'si' === $atts['solo'] && ( $user_id || $abierto );
		$clase = 'gd-mapa-grupo' . ( $solo ? ' gd-mapa--solo' : '' );

		if ( $user_id ) {
			$hechos = array();
			foreach ( $salas as $i => $s ) {
				$hechos[ $i ] = isset( $lecciones[ $i ] ) && learndash_is_lesson_complete( $user_id, $lecciones[ $i ], $course_id );
			}
			return '<div class="' . $clase . '">' . gd_mapa_html( $hechos, $salas, $urls, $lineal, $img_base, $atts, false ) . '</div>' . gd_mapa_estilos();
		}

		// Visitante sin sesión: LearnDash no le guarda el avance, así que lo lleva su navegador
		// (lo anota la lección al terminar cada capítulo). Van los 7 estados posibles y se muestra
		// el que corresponde; las imágenes de los ocultos no se descargan. Así además la página
		// sirve igual desde la caché de LiteSpeed.
		$html = '';
		for ( $k = 0; $k <= count( $salas ); $k++ ) {
			$hechos = array();
			foreach ( $salas as $i => $s ) {
				$hechos[ $i ] = $i < $k;
			}
			$html .= '<div class="gd-mapa-var" data-k="' . $k . '"' . ( $k ? ' hidden' : '' ) . '>' . gd_mapa_html( $hechos, $salas, $urls, $lineal, $img_base, $atts, true ) . '</div>';
		}
		$html  = '<div class="' . $clase . '" data-gd-invitado="' . (int) $course_id . '">' . $html . '</div>';
		$html .= '<script>(function(){var g=document.currentScript.previousElementSibling,k=0;'
			. 'try{k=parseInt(localStorage.getItem("gd_hechos_"+g.dataset.gdInvitado),10)||0}catch(e){}'
			. 'g.querySelectorAll(".gd-mapa-var").forEach(function(v){v.hidden=+v.dataset.k!==k})})();</script>';
		return $html . gd_mapa_estilos();
	}

	/** Un estado del mapa: $hechos[i] = capítulo i completado. */
	function gd_mapa_html( $hechos, $salas, $urls, $lineal, $img_base, $atts, $diferida ) {
		$total  = count( $salas );
		$actual = array_search( false, $hechos, true );  // primer capítulo sin completar (false = terminó todo)
		$ultimo = 0;                                      // hasta qué sala va a color
		foreach ( $hechos as $i => $h ) {
			if ( $h ) {
				$ultimo = $i + 1;
			}
		}
		$color    = false === $actual ? $total : max( $actual + 1, $ultimo );
		$n_hechos = count( array_filter( $hechos ) );

		ob_start();
		?>
		<div class="gd-mapa" data-gd-mapa>
			<div class="gd-mapa__lienzo">
				<img class="gd-mapa__img"<?php echo $diferida ? ' loading="lazy"' : ''; ?> src="<?php echo esc_url( $img_base . 'mapa-' . $color . '.webp' ); ?>" width="1920" height="1080"
					alt="<?php echo esc_attr( sprintf( 'Tu recorrido: %d de %d capítulos completados', $n_hechos, $total ) ); ?>">
				<?php foreach ( $salas as $i => $s ) :
					$url    = $urls[ $i ];
					$estado = $hechos[ $i ] ? 'hecho' : ( $i === $actual ? 'actual' : 'pendiente' );
					$traba  = 'pendiente' === $estado && $lineal;
					if ( 'hecho' === $estado ) {
						$nota = 'Completado · Repasar';
					} elseif ( 'actual' === $estado ) {
						$nota = $i > 0 || $n_hechos ? 'Estás acá · Continuar' : 'Empezá por acá';
					} else {
						$nota = $traba ? 'Se desbloquea al terminar el capítulo ' . $salas[ $i - 1 ]['n'] : 'Ir al capítulo';
					}
					$pos  = sprintf( 'left:%s%%;top:%s%%;width:%s%%;height:%s%%', $s['x'], $s['y'], $s['w'], $s['h'] );
					$pin  = sprintf( '--px:%s%%;--py:%s%%;--cx:%s%%;--cy:%s%%', ( $s['px'] - $s['x'] ) / $s['w'] * 100, ( $s['py'] - $s['y'] ) / $s['h'] * 100, ( $s['cx'] - $s['x'] ) / $s['w'] * 100, ( $s['cy'] - $s['y'] ) / $s['h'] * 100 );
					$tag  = $traba || ! $url ? 'span' : 'a';
					$attr = 'a' === $tag ? ' href="' . esc_url( $url ) . '"' : ' tabindex="0" role="note" aria-disabled="true"';
					?>
					<<?php echo $tag . $attr; ?> class="gd-sala gd-sala--<?php echo esc_attr( $estado ); ?>" style="<?php echo esc_attr( $pos . ';' . $pin ); ?>"
						aria-label="<?php echo esc_attr( 'Capítulo ' . $s['n'] . ': ' . $s['t'] . ' — ' . $nota ); ?>">
						<?php if ( 'actual' === $estado ) : ?>
							<span class="gd-pin" aria-hidden="true"><span class="gd-pin__onda"></span><span class="gd-pin__chip"><?php echo 'Empezá por acá' === $nota ? 'Empezá acá' : 'Estás acá'; ?></span></span>
						<?php elseif ( 'hecho' === $estado ) : ?>
							<span class="gd-tilde" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
						<?php elseif ( $traba ) : ?>
							<span class="gd-candado" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg></span>
						<?php endif; ?>
						<span class="gd-sala__nota" aria-hidden="true"><b><?php echo esc_html( $s['n'] ); ?></b> <?php echo esc_html( $nota ); ?></span>
					</<?php echo $tag; ?>>
				<?php endforeach; ?>
			</div>

			<?php if ( 'si' === $atts['barra'] ) : ?>
			<div class="gd-mapa__barra">
				<div class="gd-mapa__texto">
					<?php if ( false === $actual ) : ?>
						<strong>¡Completaste el recorrido!</strong> <span>Los 6 capítulos, de la recepción a la fidelización.</span>
					<?php else : ?>
						<strong><?php echo $n_hechos ? 'Vas por el Capítulo ' . esc_html( $salas[ $actual ]['n'] ) : 'Empezá por el Capítulo 01'; ?></strong>
						<span><?php echo esc_html( $salas[ $actual ]['t'] ); ?></span>
					<?php endif; ?>
				</div>
				<div class="gd-mapa__avance" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo (int) $total; ?>" aria-valuenow="<?php echo (int) $n_hechos; ?>"
					aria-label="<?php echo esc_attr( $n_hechos . ' de ' . $total . ' capítulos completados' ); ?>">
					<span class="gd-mapa__riel"><span style="width:<?php echo esc_attr( round( $n_hechos / $total * 100, 2 ) ); ?>%"></span></span>
					<span class="gd-mapa__cuenta"><?php echo (int) $n_hechos; ?>/<?php echo (int) $total; ?></span>
				</div>
				<?php if ( false !== $actual && $urls[ $actual ] ) : ?>
					<a class="gd-mapa__cta" href="<?php echo esc_url( $urls[ $actual ] ); ?>">
						<?php echo $n_hechos ? 'Continuar' : 'Empezar'; ?> <span aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/** CSS y JS, una sola vez por página. */
	function gd_mapa_estilos() {
		static $impreso = false;
		if ( $impreso ) {
			return '';
		}
		$impreso = true;
		ob_start();
		?>
		<style>
		.gd-mapa{--tint:#9A064E;--tint-soft:#F7E8EF;--ink:#1A1A1A;--muted:#737373;--card:#FFFFFF;--line:#E0E0E0;
			font-family:'Poppins',system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink);max-width:1400px;margin:0 auto 32px}
		.gd-mapa__lienzo{position:relative;container-type:inline-size;border-radius:16px;overflow:hidden;background:#fff}
		.gd-mapa__img{display:block;width:100%;height:auto;margin:0;border:0}
		.gd-mapa>.gd-mapa__img{border-radius:16px}

		.gd-sala{position:absolute;display:block;border-radius:14px;outline:none;text-decoration:none;color:inherit;cursor:pointer;
			transition:background-color .2s}
		.gd-sala--pendiente[aria-disabled]{cursor:not-allowed}
		.gd-sala:hover,.gd-sala:focus-visible{background:rgba(154,6,78,.06)}
		.gd-sala:focus-visible{box-shadow:inset 0 0 0 2px var(--tint)}

		/* Pin "Estás acá" */
		.gd-pin{position:absolute;left:var(--px);top:var(--py);transform:translate(-50%,-50%);pointer-events:none}
		.gd-pin__onda{position:absolute;left:50%;top:50%;width:4.2cqw;height:4.2cqw;margin:-2.1cqw 0 0 -2.1cqw;border-radius:50%;
			background:var(--tint);opacity:.35;animation:gd-onda 1.8s ease-out infinite}
		.gd-pin__chip{position:relative;display:block;white-space:nowrap;background:var(--tint);color:#fff;font-weight:600;
			font-size:max(10px,1.05cqw);line-height:1;padding:.7em 1.1em;border-radius:999px;box-shadow:0 .4em 1.2em rgba(154,6,78,.35);
			animation:gd-flota 2.4s ease-in-out infinite}
		.gd-pin__chip::after{content:"";position:absolute;left:50%;bottom:-.45em;width:.9em;height:.9em;background:var(--tint);
			transform:translateX(-50%) rotate(45deg);border-radius:2px;z-index:-1}
		@keyframes gd-onda{0%{transform:scale(.4);opacity:.45}100%{transform:scale(1.9);opacity:0}}
		@keyframes gd-flota{0%,100%{transform:translateY(0)}50%{transform:translateY(-.3em)}}

		/* Tilde de completado y candado */
		.gd-tilde,.gd-candado{position:absolute;left:var(--px);top:var(--py);transform:translate(-50%,-50%);width:max(20px,2.6cqw);height:max(20px,2.6cqw);
			border-radius:50%;display:grid;place-items:center;pointer-events:none}
		.gd-tilde{left:var(--cx);top:var(--cy);width:max(16px,1.9cqw);height:max(16px,1.9cqw);background:var(--tint);box-shadow:0 0 0 max(2px,.3cqw) #fff,0 .3cqw 1cqw rgba(0,0,0,.25)}
		.gd-tilde svg{width:62%;height:62%;fill:none;stroke:#fff;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
		.gd-candado{background:rgba(255,255,255,.92);box-shadow:0 .2cqw .8cqw rgba(0,0,0,.18);opacity:0;transition:opacity .2s}
		.gd-candado svg{width:55%;height:55%;fill:none;stroke:var(--muted);stroke-width:2.2;stroke-linecap:round}
		.gd-sala:hover .gd-candado,.gd-sala:focus-visible .gd-candado{opacity:1}

		/* Etiqueta al pasar el mouse */
		.gd-sala__nota{position:absolute;left:50%;bottom:4%;transform:translate(-50%,6px);white-space:nowrap;background:var(--card);
			color:var(--ink);font-size:max(11px,.9cqw);padding:.55em .9em;border-radius:8px;box-shadow:0 .3em 1.2em rgba(0,0,0,.15);
			opacity:0;transition:opacity .18s,transform .18s;pointer-events:none}
		.gd-sala__nota b{color:var(--tint)}
		.gd-sala:hover .gd-sala__nota,.gd-sala:focus-visible .gd-sala__nota{opacity:1;transform:translate(-50%,0)}

		/* Barra de avance */
		.gd-mapa__barra{display:flex;align-items:center;gap:20px;margin-top:14px;padding:14px 18px;background:var(--card);
			border:1px solid var(--line);border-radius:14px}
		.gd-mapa__texto{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px;font-size:14px;line-height:1.35}
		.gd-mapa__texto strong{font-size:16px;color:var(--tint)}
		.gd-mapa__texto span{color:var(--muted)}
		.gd-mapa__avance{display:flex;align-items:center;gap:10px;flex:0 1 220px}
		.gd-mapa__riel{flex:1;height:8px;background:var(--tint-soft);border-radius:99px;overflow:hidden}
		.gd-mapa__riel span{display:block;height:100%;background:var(--tint);border-radius:99px}
		.gd-mapa__cuenta{font-size:13px;font-weight:600;color:var(--muted);font-variant-numeric:tabular-nums}
		.gd-mapa__cta{flex:none;background:var(--tint);color:#fff !important;text-decoration:none !important;font-weight:600;font-size:15px;
			padding:11px 22px;border-radius:999px;transition:filter .15s}
		.gd-mapa__cta:hover{filter:brightness(1.12)}

		@media (max-width:640px){
			.gd-mapa__lienzo,.gd-mapa>.gd-mapa__img{border-radius:10px}
			.gd-mapa__barra{flex-wrap:wrap;gap:12px;padding:12px 14px}
			.gd-mapa__texto{flex-basis:100%}
			.gd-mapa__avance{flex:1 1 auto}
			.gd-sala__nota{display:none}
		}
		@media (prefers-reduced-motion:reduce){.gd-pin__onda,.gd-pin__chip{animation:none}}

		/* Modo "solo el mapa": se ocultan de entrada los bloques conocidos de BuddyBoss/LearnDash
		   (evita el parpadeo); el script de abajo se encarga del resto, se llamen como se llamen. */
		body:has(.gd-mapa--solo) :is(.bb-learndash-banner,.bb-single-course-sidebar,.bb-course-preview-wrap,
			.learndash-wrapper .ld-course-status,.learndash-wrapper .ld-progress,.learndash-wrapper .ld-item-list,
			.learndash-wrapper .ld-section-heading,.learndash-wrapper .ld-tabs-navigation):not(:has(.gd-mapa)){display:none !important}
		.gd-mapa--solo{width:100%;padding-top:24px}
		</style>
		<script>
		/* En pantallas táctiles: el primer toque en una sala bloqueada muestra la nota en vez de no hacer nada. */
		document.addEventListener('click',function(e){
			var s=e.target.closest&&e.target.closest('.gd-sala[aria-disabled]');
			if(s){s.focus();}
		});

		/* Modo "solo el mapa": sube desde el mapa hasta el contenedor de la página y oculta todo
		   lo que tenga al lado en cada nivel (banner, tarjeta lateral, progreso, listados, duplicados).
		   Nunca toca el encabezado ni el pie del sitio. A los contenedores del camino les saca el
		   ancho fijo de columna para que el mapa use todo el ancho. */
		(function(){
			var SALVAR='script,style,link,template,#wpadminbar,.site-header,#masthead,.site-footer,#colophon,'+
				'[data-elementor-type="header"],[data-elementor-type="footer"],.bb-mobile-header,.buddypanel';
			var TOPE='#content,.site-content,#page,body';
			function aislar(){
				var m=document.querySelector('.gd-mapa--solo'); if(!m) return;
				var el=m;
				while(el.parentElement && !el.matches(TOPE)){
					var padre=el.parentElement;
					Array.prototype.forEach.call(padre.children,function(h){
						if(h===el || h.matches(SALVAR) || h.querySelector(SALVAR)) return;
						h.style.setProperty('display','none','important');
					});
					if(padre.matches(TOPE)) break;
					padre.style.setProperty('max-width','100%','important');
					padre.style.setProperty('width','100%','important');
					padre.style.setProperty('flex','0 0 100%','important');
					padre.style.setProperty('grid-column','1 / -1','important');
					el=padre;
				}
			}
			if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',aislar);}else{aislar();}
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	add_shortcode( 'mapa_progreso', 'gd_mapa_shortcode' );
}

/*
 * Completado automático de los capítulos.
 *
 * Cuando el alumno llega al final de un capítulo (el HTML del iframe), el capítulo le avisa a la
 * lección; la lección marca la lección como completada en LearnDash y lleva al capítulo siguiente
 * (o al curso, en el último). Así el mapa avanza solo y el botón "Marcar como completado" sobra:
 * se oculta en las lecciones que tienen un capítulo embebido.
 */
if ( ! function_exists( 'gd_leccion_completar_ajax' ) ) {

	function gd_leccion_completar_ajax() {
		check_ajax_referer( 'gd_completar', 'nonce' );
		$user_id   = get_current_user_id();
		$lesson_id = isset( $_POST['leccion'] ) ? absint( $_POST['leccion'] ) : 0;
		$course_id = isset( $_POST['curso'] ) ? absint( $_POST['curso'] ) : 0;

		if ( ! $user_id || ! $lesson_id || 'sfwd-lessons' !== get_post_type( $lesson_id ) || ! function_exists( 'learndash_process_mark_complete' ) ) {
			wp_send_json_error();
		}
		if ( ! $course_id ) {
			$course_id = (int) learndash_get_course_id( $lesson_id );
		}
		if ( function_exists( 'sfwd_lms_has_access' ) && ! sfwd_lms_has_access( $lesson_id, $user_id ) ) {
			wp_send_json_error();
		}

		$ok = learndash_is_lesson_complete( $user_id, $lesson_id, $course_id )
			|| learndash_process_mark_complete( $user_id, $lesson_id, false, $course_id );

		wp_send_json_success( array(
			'completado' => (bool) $ok,
			'curso'      => $course_id ? get_permalink( $course_id ) : home_url( '/' ),
		) );
	}
	add_action( 'wp_ajax_gd_completar', 'gd_leccion_completar_ajax' );

	function gd_leccion_completar_script() {
		if ( ! is_singular( 'sfwd-lessons' ) ) {
			return;
		}
		$lesson_id = get_the_ID();
		$course_id = function_exists( 'learndash_get_course_id' ) ? (int) learndash_get_course_id( $lesson_id ) : 0;
		$con_login = is_user_logged_in();
		// Para el visitante: qué número de capítulo es esta lección (orden del curso, o el slug capitulo-N).
		$posicion  = $course_id ? array_search( $lesson_id, gd_mapa_lecciones( $course_id ), true ) : false;
		if ( false === $posicion && preg_match( '/^capitulo-(\d+)$/', (string) get_post_field( 'post_name', $lesson_id ), $m ) ) {
			$posicion = (int) $m[1] - 1;
		}
		?>
		<style>
		/* El capítulo se completa solo al terminarlo: el botón de LearnDash sobra. Tampoco hace
		   falta la cabecera de la lección (migas "Curso > Capítulo 1", "Lección 1 of 6" y flechas):
		   el capítulo trae su propia navegación. */
		body:has(iframe[src*="/uploads/academia/"]) :is(form.sfwd-mark-complete,.learndash_mark_complete_button,
			.bb-lms-header,.ld-breadcrumbs,.bb-position,.sfwd-course-position,.sfwd-course-nav){display:none !important}
		</style>
		<script>
		(function () {
			var CON_LOGIN = <?php echo $con_login ? 'true' : 'false'; ?>,
				AJAX = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
				NONCE = <?php echo wp_json_encode( $con_login ? wp_create_nonce( 'gd_completar' ) : '' ); ?>,
				LECCION = <?php echo (int) $lesson_id; ?>, CURSO = <?php echo (int) $course_id; ?>,
				POSICION = <?php echo false === $posicion ? -1 : (int) $posicion; ?>,
				URL_CURSO = <?php echo wp_json_encode( $course_id ? get_permalink( $course_id ) : home_url( '/' ) ); ?>,
				enCurso = false;
			function sinWww(o) { return String(o).replace('//www.', '//'); }

			// Cabecera de la lección, aunque el tema use otras clases: el bloque que tiene el texto
			// "Lección N of M" y el link al curso, sin el capítulo adentro.
			function sacarCabecera() {
				if (!document.querySelector('iframe[src*="/uploads/academia/"]')) return;
				var w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT), n, el;
				while ((n = w.nextNode())) {
					if (!/lecci[oó]n|lesson/i.test(n.nodeValue)) continue;
					el = n.parentElement;
					if (!el || !/(lecci[oó]n|lesson)\s*\d+\s*(of|de)\s*\d+/i.test(el.closest('div') ? el.closest('div').textContent : '')) continue;
					while (el.parentElement && el.parentElement !== document.body) {
						var p = el.parentElement;
						if (p.querySelector('iframe, .lms-topic-sidebar-wrapper')) break;
						el = p;
					}
					if (el.querySelector('a[href*="/courses/"]')) el.style.setProperty('display', 'none', 'important');
					return;
				}
			}
			if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', sacarCabecera); else sacarCabecera();
			window.addEventListener('message', function (e) {
				if (!e.data || !e.data.gdFin || sinWww(e.origin) !== sinWww(location.origin)) return;
				try { e.source.postMessage({ gdAck: e.data.gdId }, '*'); } catch (x) {}
				if (enCurso) return;
				enCurso = true;
				var destino = e.data.destino;
				if (!CON_LOGIN) {
					// Visitante: el avance queda en su navegador y lo lee el mapa del curso.
					try {
						var clave = 'gd_hechos_' + CURSO, antes = parseInt(localStorage.getItem(clave), 10) || 0;
						if (POSICION >= 0 && POSICION + 1 > antes) localStorage.setItem(clave, POSICION + 1);
					} catch (x) {}
					window.location.href = destino || URL_CURSO;
					return;
				}
				var fd = new FormData();
				fd.append('action', 'gd_completar'); fd.append('nonce', NONCE);
				fd.append('leccion', LECCION); fd.append('curso', CURSO);
				fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.catch(function () { return null; })
					.then(function (r) {
						var curso = r && r.success && r.data.curso;
						window.location.href = destino || curso || '/';
					});
			});
		})();
		</script>
		<?php
	}
	add_action( 'wp_footer', 'gd_leccion_completar_script' );
}

/*
 * Barra lateral de las lecciones (BuddyBoss) con la estética Galderma: magenta en lugar del azul
 * del tema, tipografía Poppins, capítulo actual resaltado, sin tachado en los completos y textos
 * en español ("Volver al curso", "33% completado", "Última actividad", "Participantes").
 */
if ( ! function_exists( 'gd_sidebar_galderma' ) ) {

	function gd_sidebar_es_leccion() {
		return is_singular( array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) );
	}

	function gd_sidebar_galderma() {
		if ( ! gd_sidebar_es_leccion() ) {
			return;
		}
		?>
		<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
		<style>
		.lms-topic-sidebar-wrapper{
			--gd-tint:#9A064E;--gd-tint-soft:#F7E8EF;--gd-ink:#1A1A1A;--gd-muted:#737373;--gd-line:#EADDE3;
			--bb-primary-color:var(--gd-tint);--bb-primary-button-background-regular:var(--gd-tint);
			--bb-primary-button-border-regular:var(--gd-tint);--bb-primary-button-background-hover:#7d043f;
			background:#fff !important;border-right:1px solid var(--gd-line);
		}
		.lms-topic-sidebar-wrapper,.lms-topic-sidebar-wrapper *:not([class*="icon"]):not(i){font-family:'Poppins',system-ui,-apple-system,"Segoe UI",sans-serif !important}

		/* "Volver al curso" */
		.lms-topic-sidebar-wrapper a[class*="course-entry"],
		.lms-topic-sidebar-wrapper [class*="navigation-heading"] a{
			background:var(--gd-tint-soft) !important;color:var(--gd-tint) !important;border:0 !important;border-radius:999px !important;
			font-weight:500;font-size:13px;padding:6px 14px !important;transition:background .15s}
		.lms-topic-sidebar-wrapper a[class*="course-entry"]:hover,
		.lms-topic-sidebar-wrapper [class*="navigation-heading"] a:hover{background:#efd3e0 !important}
		.lms-topic-sidebar-wrapper a[class*="course-entry"] *{color:inherit !important}

		/* Título del curso */
		.lms-topic-sidebar-wrapper :is(h1,h2,h3)[class*="title"],.lms-topic-sidebar-wrapper :is(h1,h2,h3)[class*="title"] a{
			color:var(--gd-ink) !important;font-weight:600 !important;letter-spacing:-.01em}

		/* Barra de avance */
		.lms-topic-sidebar-wrapper .ld-progress-bar{background:var(--gd-tint-soft) !important;border-radius:99px;height:6px !important;overflow:hidden}
		.lms-topic-sidebar-wrapper .ld-progress-bar-percentage{background:var(--gd-tint) !important;border-radius:99px}
		.lms-topic-sidebar-wrapper .ld-progress-percentage{color:var(--gd-tint) !important;font-weight:600}
		.lms-topic-sidebar-wrapper :is(.ld-progress-steps,.ld-progress-stats,[class*="last-activity"]){color:var(--gd-muted) !important}

		/* Lista de capítulos */
		.lms-topic-sidebar-wrapper .lms-lesson-item,.lms-topic-sidebar-wrapper .lms-lesson-item *{text-decoration:none !important}
		.lms-topic-sidebar-wrapper .lms-lesson-item a{color:var(--gd-ink) !important;transition:color .15s}
		.lms-topic-sidebar-wrapper .lms-lesson-item a:hover{color:var(--gd-tint) !important}
		.lms-topic-sidebar-wrapper .lms-lesson-item:is([class*="complete"],[class*="completed"]) a{color:var(--gd-muted) !important}
		.lms-topic-sidebar-wrapper .lms-lesson-item.current,.lms-topic-sidebar-wrapper .lms-lesson-item.current > *{background:var(--gd-tint-soft) !important}
		.lms-topic-sidebar-wrapper .lms-lesson-item.current,.lms-topic-sidebar-wrapper .lms-lesson-item.current > :first-child{box-shadow:inset 3px 0 0 var(--gd-tint)}
		.lms-topic-sidebar-wrapper .lms-lesson-item.current a{color:var(--gd-tint) !important;font-weight:600}
		</style>
		<?php
	}
	add_action( 'wp_head', 'gd_sidebar_galderma', 99 );

	function gd_sidebar_galderma_js() {
		if ( ! gd_sidebar_es_leccion() ) {
			return;
		}
		?>
		<script>
		(function () {
			var TINT = '#9A064E';
			var TEXTOS = [
				[/Back to (Course|Curso)/i, 'Volver al curso'],
				[/(\d+)\s*% Complete/i, '$1% completado'],
				[/Last activity on/i, 'Última actividad:'],
				[/\bParticipants\b/, 'Participantes']
			];
			// Azul del tema (BuddyBoss): se detecta por tono, así cubre tildes, círculos y bordes
			// aunque cambie el nombre de las clases.
			function esAzul(c) {
				var m = /rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?/.exec(c || '');
				if (!m || (m[4] !== undefined && +m[4] === 0)) return false;
				var r = +m[1], g = +m[2], b = +m[3];
				return b > 180 && b - r > 90 && b - g > 60;
			}
			function pintar(raiz) {
				raiz.querySelectorAll('*').forEach(function (el) {
					var cs = getComputedStyle(el);
					if (esAzul(cs.backgroundColor)) el.style.setProperty('background-color', TINT, 'important');
					if (esAzul(cs.borderTopColor) || esAzul(cs.borderLeftColor)) el.style.setProperty('border-color', TINT, 'important');
					if (esAzul(cs.color)) el.style.setProperty('color', TINT, 'important');
					if (el instanceof SVGElement) {
						if (esAzul(cs.fill)) el.style.setProperty('fill', TINT, 'important');
						if (esAzul(cs.stroke)) el.style.setProperty('stroke', TINT, 'important');
					}
				});
			}
			function traducir(raiz) {
				var w = document.createTreeWalker(raiz, NodeFilter.SHOW_TEXT), n;
				while ((n = w.nextNode())) {
					TEXTOS.forEach(function (t) { if (t[0].test(n.nodeValue)) n.nodeValue = n.nodeValue.replace(t[0], t[1]); });
				}
			}
			function aplicar() {
				var s = document.querySelector('.lms-topic-sidebar-wrapper');
				if (!s) return;
				traducir(s); pintar(s);
			}
			if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', aplicar); else aplicar();
			window.addEventListener('load', aplicar);
		})();
		</script>
		<?php
	}
	add_action( 'wp_footer', 'gd_sidebar_galderma_js', 99 );
}
