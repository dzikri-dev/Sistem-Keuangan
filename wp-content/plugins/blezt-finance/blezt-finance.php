<?php
/**
 * Plugin Name: Blezt Finance
 * Description: CRUD transaksi keuangan (pendapatan & pengeluaran) untuk dashboard Blezt.id.
 * Version: 1.0.0
 * Author: Studio Code
 * Text Domain: blezt-finance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------- */
/* Custom post type: Transaksi                                        */
/* ---------------------------------------------------------------- */

add_action( 'init', function () {
	register_post_type( 'transaksi', array(
		'labels'       => array(
			'name'          => 'Transaksi',
			'singular_name' => 'Transaksi',
			'add_new'       => 'Tambah Transaksi',
			'add_new_item'  => 'Tambah Transaksi Baru',
			'edit_item'     => 'Edit Transaksi',
			'all_items'     => 'Semua Transaksi',
			'search_items'  => 'Cari Transaksi',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-list-view',
		'supports'     => array( 'title' ),
		'rewrite'      => array( 'slug' => 'transaksi' ),
		'has_archive'  => false,
	) );
} );

/* ---------------------------------------------------------------- */
/* Meta box (admin editor)                                           */
/* ---------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'blezt_transaksi_meta', 'Detail Transaksi', 'blezt_transaksi_meta_cb', 'transaksi', 'normal', 'high' );
} );

function blezt_transaksi_meta_cb( $post ) {
	wp_nonce_field( 'blezt_transaksi_save', 'blezt_transaksi_nonce' );
	$tipe   = get_post_meta( $post->ID, '_blezt_tipe', true ) ?: 'pendapatan';
	$jumlah = get_post_meta( $post->ID, '_blezt_jumlah', true );
	$tanggal = get_post_meta( $post->ID, '_blezt_tanggal', true ) ?: current_time( 'Y-m-d' );
	?>
	<p>
		<label for="blezt_tipe"><strong>Tipe</strong></label><br>
		<select id="blezt_tipe" name="blezt_tipe" style="min-width:200px;margin-top:6px">
			<option value="pendapatan" <?php selected( $tipe, 'pendapatan' ); ?>>Pendapatan</option>
			<option value="pengeluaran" <?php selected( $tipe, 'pengeluaran' ); ?>>Pengeluaran</option>
		</select>
	</p>
	<p>
		<label for="blezt_jumlah"><strong>Jumlah (Rp)</strong></label><br>
		<input type="number" step="0.01" min="0" id="blezt_jumlah" name="blezt_jumlah" value="<?php echo esc_attr( $jumlah ); ?>" style="min-width:200px;margin-top:6px">
	</p>
	<p>
		<label for="blezt_tanggal"><strong>Tanggal</strong></label><br>
		<input type="date" id="blezt_tanggal" name="blezt_tanggal" value="<?php echo esc_attr( $tanggal ); ?>" style="margin-top:6px">
	</p>
	<p><em>Keterangan diisi pada kolom judul transaksi di atas.</em></p>
	<?php
}

add_action( 'save_post_transaksi', function ( $post_id ) {
	if ( ! isset( $_POST['blezt_transaksi_nonce'] ) || ! wp_verify_nonce( $_POST['blezt_transaksi_nonce'], 'blezt_transaksi_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array(
		'_blezt_tipe'   => isset( $_POST['blezt_tipe'] ) ? sanitize_text_field( wp_unslash( $_POST['blezt_tipe'] ) ) : 'pendapatan',
		'_blezt_jumlah' => isset( $_POST['blezt_jumlah'] ) ? floatval( $_POST['blezt_jumlah'] ) : 0,
		'_blezt_tanggal'=> isset( $_POST['blezt_tanggal'] ) ? sanitize_text_field( wp_unslash( $_POST['blezt_tanggal'] ) ) : '',
	);
	foreach ( $fields as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
} );

/* ---------------------------------------------------------------- */
/* Admin columns                                                     */
/* ---------------------------------------------------------------- */

add_filter( 'manage_transaksi_posts_columns', function ( $cols ) {
	$cols['blezt_tipe']    = 'Tipe';
	$cols['blezt_jumlah']  = 'Jumlah';
	$cols['blezt_tanggal'] = 'Tanggal';
	return $cols;
} );

add_action( 'manage_transaksi_posts_custom_column', function ( $col, $post_id ) {
	if ( 'blezt_tipe' === $col ) {
		$t = get_post_meta( $post_id, '_blezt_tipe', true );
		echo 'pendapatan' === $t ? 'Pendapatan' : 'Pengeluaran';
	}
	if ( 'blezt_jumlah' === $col ) {
		echo 'Rp ' . number_format( (float) get_post_meta( $post_id, '_blezt_jumlah', true ), 0, ',', '.' );
	}
	if ( 'blezt_tanggal' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_blezt_tanggal', true ) );
	}
}, 10, 2 );

/* ---------------------------------------------------------------- */
/* Front-end helpers                                                 */
/* ---------------------------------------------------------------- */

function blezt_format_rp( $n ) {
	return 'Rp ' . number_format( (float) $n, 0, ',', '.' );
}

function blezt_get_transaksi( $limit = 200 ) {
	$q = new WP_Query( array(
		'post_type'      => 'transaksi',
		'post_status'    => array( 'publish', 'private' ),
		'posts_per_page' => $limit,
		'meta_key'       => '_blezt_tanggal',
		'orderby'        => 'meta_value',
		'order'          => 'DESC',
	) );
	return $q->posts;
}

/* ---------------------------------------------------------------- */
/* Shortcode: input form                                             */
/* ---------------------------------------------------------------- */

add_shortcode( 'blezt_transaksi_form', 'blezt_transaksi_form' );
function blezt_transaksi_form() {
	if ( ! is_user_logged_in() ) {
		return '<p>Silakan <a href="' . esc_url( wp_login_url() ) . '">login</a> untuk menambahkan transaksi.</p>';
	}

	$msg = '';
	if ( isset( $_POST['blezt_baru'] ) ) {
		check_admin_referer( 'blezt_input', 'blezt_nonce' );
		$tipe    = isset( $_POST['blezt_tipe'] ) ? sanitize_text_field( wp_unslash( $_POST['blezt_tipe'] ) ) : 'pendapatan';
		$jumlah  = isset( $_POST['blezt_jumlah'] ) ? floatval( $_POST['blezt_jumlah'] ) : 0;
		$ket     = isset( $_POST['blezt_keterangan'] ) ? sanitize_text_field( wp_unslash( $_POST['blezt_keterangan'] ) ) : '';
		$tanggal = isset( $_POST['blezt_tanggal'] ) ? sanitize_text_field( wp_unslash( $_POST['blezt_tanggal'] ) ) : current_time( 'Y-m-d' );

		if ( $jumlah > 0 && '' !== $ket ) {
			$id = wp_insert_post( array(
				'post_type'    => 'transaksi',
				'post_status'  => 'private',
				'post_title'   => $ket,
				'meta_input'   => array(
					'_blezt_tipe'    => 'pendapatan' === $tipe ? 'pendapatan' : 'pengeluaran',
					'_blezt_jumlah'  => $jumlah,
					'_blezt_tanggal' => $tanggal,
				),
			) );
			$msg = $id ? '<p class="blezt-form-ok">Transaksi berhasil disimpan.</p>' : '<p class="blezt-form-err">Gagal menyimpan.</p>';
		} else {
			$msg = '<p class="blezt-form-err">Isi jumlah yang valid dan keterangan.</p>';
		}
	}

	ob_start();
	?>
	<div class="blezt-form is-style-card">
		<?php echo $msg; ?>
		<form method="post">
			<?php wp_nonce_field( 'blezt_input', 'blezt_nonce' ); ?>
			<div class="blezt-frow">
				<label for="blezt_tipe">Tipe</label>
				<select id="blezt_tipe" name="blezt_tipe">
					<option value="pendapatan">Pendapatan</option>
					<option value="pengeluaran">Pengeluaran</option>
				</select>
			</div>
			<div class="blezt-frow">
				<label for="blezt_jumlah">Jumlah (Rp)</label>
				<input type="number" step="0.01" min="0" id="blezt_jumlah" name="blezt_jumlah" placeholder="cth: 250000" required>
			</div>
			<div class="blezt-frow">
				<label for="blezt_keterangan">Keterangan</label>
				<input type="text" id="blezt_keterangan" name="blezt_keterangan" placeholder="cth: Penjualan produk Q3" required>
			</div>
			<div class="blezt-frow">
				<label for="blezt_tanggal">Tanggal</label>
				<input type="date" id="blezt_tanggal" name="blezt_tanggal" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
			</div>
			<button type="submit" name="blezt_baru" value="1" class="wp-block-button__link wp-element-button">Simpan Transaksi</button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/* ---------------------------------------------------------------- */
/* Shortcode: daftar transaksi (tabel)                               */
/* ---------------------------------------------------------------- */

add_shortcode( 'blezt_daftar_transaksi', 'blezt_daftar_transaksi' );
function blezt_daftar_transaksi() {
	$bulan = isset( $_GET['blt_bulan'] ) ? sanitize_text_field( wp_unslash( $_GET['blt_bulan'] ) ) : '';
	$tipe  = isset( $_GET['blt_tipe'] ) ? sanitize_text_field( wp_unslash( $_GET['blt_tipe'] ) ) : '';
	$q     = isset( $_GET['blt_q'] ) ? sanitize_text_field( wp_unslash( $_GET['blt_q'] ) ) : '';

	$args = array(
		'post_type'      => 'transaksi',
		'post_status'    => array( 'publish', 'private' ),
		'posts_per_page' => 500,
		'meta_key'       => '_blezt_tanggal',
		'orderby'        => 'meta_value',
		'order'          => 'DESC',
	);

	$meta = array();
	if ( $bulan ) {
		$meta[] = array( 'key' => '_blezt_tanggal', 'value' => $bulan, 'compare' => 'LIKE' );
	}
	if ( $tipe && 'semua' !== $tipe ) {
		$meta[] = array( 'key' => '_blezt_tipe', 'value' => $tipe );
	}
	if ( count( $meta ) === 1 ) {
		$args['meta_query'] = $meta;
	} elseif ( count( $meta ) > 1 ) {
		$args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $meta );
	}
	if ( $q ) {
		$args['s'] = $q;
	}

	$query = new WP_Query( $args );
	$rows  = $query->posts;

	$edit_link = current_user_can( 'edit_posts' )
		? '<a href="' . esc_url( admin_url( 'edit.php?post_type=transaksi' ) ) . '">Kelola di admin</a>'
		: '';

	if ( empty( $rows ) ) {
		$empty_note = '<p>Belum ada transaksi untuk filter ini. Gunakan form "Input Transaksi" atau menu Transaksi di admin.</p>';
	} else {
		$empty_note = '';
	}

	ob_start();
	?>
	<div class="blezt-table-panel is-style-card">
		<div class="blezt-filter">
			<form method="get">
				<input type="month" name="blt_bulan" value="<?php echo esc_attr( $bulan ); ?>" aria-label="Bulan">
				<select name="blt_tipe">
					<option value="semua" <?php selected( $tipe, 'semua' ); ?>>Semua tipe</option>
					<option value="pendapatan" <?php selected( $tipe, 'pendapatan' ); ?>>Pendapatan</option>
					<option value="pengeluaran" <?php selected( $tipe, 'pengeluaran' ); ?>>Pengeluaran</option>
				</select>
				<input type="text" name="blt_q" value="<?php echo esc_attr( $q ); ?>" placeholder="Cari keterangan…">
				<button type="submit">Filter</button>
				<?php if ( $bulan || ( $tipe && 'semua' !== $tipe ) || $q ) : ?>
					<a class="blezt-reset" href="<?php echo esc_url( get_permalink() ); ?>">Reset</a>
				<?php endif; ?>
			</form>
		</div>
		<?php if ( $empty_note ) : ?>
			<?php echo $empty_note; ?>
		<?php else : ?>
		<div class="blezt-table-head">
			<h2 class="wp-block-heading">Semua Transaksi</h2>
			<?php if ( $edit_link ) : ?>
				<span class="blezt-admin-link"><?php echo $edit_link; ?></span>
			<?php endif; ?>
		</div>
		<table class="wp-block-table blezt-ledger">
			<thead>
				<tr><th>Tanggal</th><th>Keterangan</th><th>Tipe</th><th>Jumlah</th>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?><th></th><?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $p ) :
				$tipe   = get_post_meta( $p->ID, '_blezt_tipe', true );
				$jumlah = (float) get_post_meta( $p->ID, '_blezt_jumlah', true );
				$tanggal = get_post_meta( $p->ID, '_blezt_tanggal', true );
				$is_in  = 'pendapatan' === $tipe;
				?>
				<tr>
					<td><?php echo esc_html( $tanggal ); ?></td>
					<td><?php echo esc_html( $p->post_title ); ?></td>
					<td class="<?php echo $is_in ? 'blezt-up' : 'blezt-down'; ?>"><?php echo $is_in ? 'Pendapatan' : 'Pengeluaran'; ?></td>
					<td class="<?php echo $is_in ? '' : 'blezt-down'; ?>"><?php echo $is_in ? '' : '−'; ?><?php echo esc_html( blezt_format_rp( $jumlah ) ); ?></td>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<td class="blezt-row-actions"><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>">Edit</a></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/* ---------------------------------------------------------------- */
/* Shortcode: grafik tren bulanan                                    */
/* ---------------------------------------------------------------- */

add_shortcode( 'blezt_chart_trend', 'blezt_chart_trend' );
function blezt_chart_trend() {
	$rows = blezt_get_transaksi( 100000 );
	if ( empty( $rows ) ) {
		return '<div class="blezt-chart-panel is-style-card"><h2 class="wp-block-heading">Tren bulanan</h2><p>Belum ada data untuk ditampilkan.</p></div>';
	}

	$months = array();
	foreach ( $rows as $p ) {
		$tg = get_post_meta( $p->ID, '_blezt_tanggal', true );
		$m  = substr( $tg, 0, 7 );
		$j  = (float) get_post_meta( $p->ID, '_blezt_jumlah', true );
		$t  = get_post_meta( $p->ID, '_blezt_tipe', true );
		if ( ! isset( $months[ $m ] ) ) {
			$months[ $m ] = array( 'in' => 0, 'out' => 0 );
		}
		if ( 'pendapatan' === $t ) {
			$months[ $m ]['in'] += $j;
		} else {
			$months[ $m ]['out'] += $j;
		}
	}

	krsort( $months );
	$months = array_slice( $months, 0, 6, true );
	$months = array_reverse( $months, true );

	$max = 1;
	foreach ( $months as $d ) {
		$max = max( $max, $d['in'], $d['out'] );
	}

	$labels = array( '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des' );

	ob_start();
	?>
	<div class="blezt-chart-panel is-style-card">
		<div class="blezt-chart-head">
			<h2 class="wp-block-heading">Tren bulanan</h2>
			<span class="blezt-legend"><span class="lg-in">Pendapatan</span><span class="lg-out">Pengeluaran</span></span>
		</div>
		<div class="blezt-chart">
			<?php foreach ( $months as $m => $d ) :
				$label = $labels[ substr( $m, 5, 2 ) ] . ' ' . substr( $m, 0, 4 );
				$hin   = $d['in'] / $max * 100;
				$hout  = $d['out'] / $max * 100;
				?>
				<div class="blezt-month">
					<div class="blezt-bars">
						<div class="blezt-bar in" style="height:<?php echo round( max( $hin, 3 ) ); ?>%"></div>
						<div class="blezt-bar out" style="height:<?php echo round( max( $hout, 3 ) ); ?>%"></div>
					</div>
					<span class="blezt-month-label"><?php echo esc_html( $label ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* ---------------------------------------------------------------- */
/* Komputasi total real-time dari database                            */
/* ---------------------------------------------------------------- */

function blezt_totals() {
	$rows = blezt_get_transaksi( 100000 );
	$in  = 0;
	$out = 0;
	foreach ( $rows as $p ) {
		$t = get_post_meta( $p->ID, '_blezt_tipe', true );
		$j = (float) get_post_meta( $p->ID, '_blezt_jumlah', true );
		if ( 'pendapatan' === $t ) {
			$in += $j;
		} else {
			$out += $j;
		}
	}
	return array( 'in' => $in, 'out' => $out, 'net' => $in - $out );
}

add_shortcode( 'blezt_kpi_pendapatan', function () {
	return esc_html( blezt_format_rp( blezt_totals()['in'] ) );
} );

add_shortcode( 'blezt_kpi_pengeluaran', function () {
	return esc_html( blezt_format_rp( blezt_totals()['out'] ) );
} );

add_shortcode( 'blezt_kpi_laba', function () {
	return esc_html( blezt_format_rp( blezt_totals()['net'] ) );
} );

add_shortcode( 'blezt_kpi_arus', function () {
	$n    = blezt_totals()['net'];
	$sign = $n >= 0 ? '+' : '-';
	return esc_html( $sign . blezt_format_rp( abs( $n ) ) );
} );

add_shortcode( 'blezt_saldo', function () {
	return '<span class="blezt-live-num">' . esc_html( blezt_format_rp( blezt_totals()['net'] ) ) . '</span>';
} );

add_shortcode( 'blezt_transaksi_terbaru', 'blezt_transaksi_terbaru' );
function blezt_transaksi_terbaru() {
	$q = new WP_Query( array(
		'post_type'      => 'transaksi',
		'post_status'    => array( 'publish', 'private' ),
		'posts_per_page' => 6,
		'meta_key'       => '_blezt_tanggal',
		'orderby'        => 'meta_value',
		'order'          => 'DESC',
	) );
	$rows = $q->posts;

	if ( empty( $rows ) ) {
		return '<p>Belum ada transaksi. Tambah lewat halaman Input Transaksi.</p>';
	}

	ob_start();
	?>
	<div class="blezt-table-panel is-style-card">
		<div class="blezt-table-head">
			<h2 class="wp-block-heading">Transaksi terbaru</h2>
			<span class="blezt-admin-link"><a href="<?php echo esc_url( home_url( '/daftar-transaksi/' ) ); ?>">Lihat semua</a></span>
		</div>
		<table class="wp-block-table blezt-ledger">
			<thead>
				<tr><th>Tanggal</th><th>Keterangan</th><th>Tipe</th><th>Jumlah</th></tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $p ) :
				$t  = get_post_meta( $p->ID, '_blezt_tipe', true );
				$j  = (float) get_post_meta( $p->ID, '_blezt_jumlah', true );
				$tg = get_post_meta( $p->ID, '_blezt_tanggal', true );
				$is = 'pendapatan' === $t;
				?>
				<tr>
					<td><?php echo esc_html( $tg ); ?></td>
					<td><?php echo esc_html( $p->post_title ); ?></td>
					<td class="<?php echo $is ? 'blezt-up' : 'blezt-down'; ?>"><?php echo $is ? 'Pendapatan' : 'Pengeluaran'; ?></td>
					<td class="<?php echo $is ? '' : 'blezt-down'; ?>"><?php echo $is ? '' : '−'; ?><?php echo esc_html( blezt_format_rp( $j ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}

/* ---------------------------------------------------------------- */
/* Front-end styles                                                  */
/* ---------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', function () {
	wp_add_inline_style( 'bleztd-style', '
.blezt-form{max-width:560px}
.blezt-frow{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.blezt-frow label{font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--wp--preset--color--text-muted,#9aa0b0);font-weight:600}
.blezt-frow input,.blezt-frow select{background:#0b0d14;border:1px solid rgba(255,255,255,.12);color:#f4f5f8;border-radius:12px;padding:12px 14px;font-size:.95rem;font-family:inherit;width:100%;box-sizing:border-box}
.blezt-frow input:focus,.blezt-frow select:focus{outline:none;border-color:var(--wp--preset--color--primary,#2f6bff)}
.blezt-form-ok{color:#5bdf8a;font-weight:600}
.blezt-form-err{color:#ff6f8e}
.blezt-table-head{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:26px 20px 10px}
.blezt-table-head h2{margin:0}
.blezt-admin-link{font-size:.78rem;color:var(--wp--preset--color--text-muted,#9aa0b0)}
.blezt-admin-link a{color:var(--wp--preset--color--primary,#2f6bff);text-decoration:none}
.blezt-row-actions a{color:var(--wp--preset--color--text-muted,#9aa0b0);text-decoration:none}
.blezt-filter{padding:22px 20px 4px}
.blezt-filter form{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.blezt-filter input,.blezt-filter select{background:#0b0d14;border:1px solid rgba(255,255,255,.12);color:#f4f5f8;border-radius:10px;padding:9px 12px;font-size:.85rem;font-family:inherit}
.blezt-filter input:focus,.blezt-filter select:focus{outline:none;border-color:var(--wp--preset--color--primary,#2f6bff)}
.blezt-filter button{background:var(--wp--preset--color--primary,#2f6bff);color:#fff;border:0;border-radius:10px;padding:9px 16px;font-weight:600;cursor:pointer;font-family:inherit}
.blezt-filter .blezt-reset{color:var(--wp--preset--color--text-muted,#9aa0b0);font-size:.82rem;text-decoration:none}
.blezt-chart-panel{padding:26px 24px 24px}
.blezt-chart-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap}
.blezt-chart-head h2{margin:0}
.blezt-legend{display:flex;gap:14px;font-size:.75rem;color:var(--wp--preset--color--text-muted,#9aa0b0)}
.blezt-legend span{display:inline-flex;align-items:center;gap:6px}
.blezt-legend span::before{content:"";width:10px;height:10px;border-radius:3px}
.blezt-legend .lg-in::before{background:var(--wp--preset--color--primary,#2f6bff)}
.blezt-legend .lg-out::before{background:var(--wp--preset--color--secondary,#ff3d8a)}
.blezt-chart{display:flex;align-items:flex-end;gap:12px;height:230px;padding:0 4px}
.blezt-month{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%}
.blezt-bars{display:flex;align-items:flex-end;justify-content:center;gap:6px;width:100%;flex:1}
.blezt-bar{width:30%;border-radius:6px 6px 0 0;min-height:3px}
.blezt-bar.in{background:linear-gradient(180deg,var(--wp--preset--color--primary,#2f6bff),rgba(47,107,255,.3))}
.blezt-bar.out{background:linear-gradient(180deg,var(--wp--preset--color--secondary,#ff3d8a),rgba(255,61,138,.3))}
.blezt-month-label{font-size:.72rem;color:var(--wp--preset--color--text-muted,#9aa0b0);white-space:nowrap}
@media(max-width:640px){.blezt-table-head{flex-direction:column;align-items:flex-start}.blezt-chart-head{flex-direction:column;align-items:flex-start}.blezt-chart{height:180px}}
' );
} );
