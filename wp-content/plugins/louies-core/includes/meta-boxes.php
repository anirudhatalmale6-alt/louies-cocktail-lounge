<?php
/**
 * The edit screens. Kept deliberately plain - this is the bit the bar uses.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'louies_event_details',
		__( 'When & How Much', 'louies' ),
		'louies_event_meta_box',
		'louies_event',
		'normal',
		'high'
	);
	add_meta_box(
		'louies_menu_details',
		__( 'Price & Description', 'louies' ),
		'louies_menu_meta_box',
		'louies_menu_item',
		'normal',
		'high'
	);
} );

function louies_event_meta_box( $post ) {
	$m = louies_event_meta( $post->ID );
	wp_nonce_field( 'louies_save_event', 'louies_event_nonce' );

	$weekdays = array_filter( array_map( 'intval', array_filter( explode( ',', (string) $m['louies_weekdays'] ), 'strlen' ) ) );
	$names    = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
	?>
	<style>
		.louies-grid { display: grid; grid-template-columns: 190px 1fr; gap: 14px 18px; align-items: start; max-width: 760px; }
		.louies-grid > label { padding-top: 6px; font-weight: 600; }
		.louies-grid input[type="text"], .louies-grid input[type="date"], .louies-grid input[type="time"], .louies-grid input[type="url"] { width: 100%; max-width: 320px; }
		.louies-hint { color: #666; font-size: 12px; margin: 4px 0 0; }
		.louies-days label { display: inline-block; margin: 0 12px 6px 0; font-weight: 400; }
		.louies-repeat-only { display: none; }
		.louies-repeat-weekly .louies-when-weekly, .louies-repeat-monthly .louies-when-monthly,
		.louies-repeat-weekly .louies-when-repeat, .louies-repeat-monthly .louies-when-repeat { display: block; }
		.louies-box { background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; padding: 14px 16px; margin-top: 4px; }
	</style>

	<div class="louies-grid" id="louies-event-fields">

		<label for="louies_date"><?php esc_html_e( 'Date', 'louies' ); ?></label>
		<div>
			<input type="date" id="louies_date" name="louies_date" value="<?php echo esc_attr( $m['louies_date'] ); ?>">
			<p class="louies-hint"><?php esc_html_e( 'For a repeating event this is the first night it runs.', 'louies' ); ?></p>
		</div>

		<label for="louies_time_start"><?php esc_html_e( 'Time', 'louies' ); ?></label>
		<div>
			<input type="time" id="louies_time_start" name="louies_time_start" style="width:130px" value="<?php echo esc_attr( $m['louies_time_start'] ); ?>">
			<span style="padding:0 6px">&ndash;</span>
			<input type="time" name="louies_time_end" style="width:130px" value="<?php echo esc_attr( $m['louies_time_end'] ); ?>">
			<p class="louies-hint"><?php esc_html_e( 'Finishing after midnight is fine - put 9:00 PM to 1:30 AM and it shows correctly.', 'louies' ); ?></p>
		</div>

		<label for="louies_price"><?php esc_html_e( 'Cover / price', 'louies' ); ?></label>
		<div>
			<input type="text" id="louies_price" name="louies_price" value="<?php echo esc_attr( $m['louies_price'] ); ?>" placeholder="FREE">
			<p class="louies-hint"><?php esc_html_e( 'Type it however you like - FREE, $10, $10 buy-in.', 'louies' ); ?></p>
		</div>

		<label for="louies_ticket_url"><?php esc_html_e( 'Ticket link', 'louies' ); ?></label>
		<div>
			<input type="url" id="louies_ticket_url" name="louies_ticket_url" value="<?php echo esc_attr( $m['louies_ticket_url'] ); ?>" placeholder="https://">
			<p class="louies-hint"><?php esc_html_e( 'Optional. Adds a "Get Tickets" button.', 'louies' ); ?></p>
		</div>

		<label><?php esc_html_e( 'Repeats?', 'louies' ); ?></label>
		<div class="louies-box louies-repeat-<?php echo esc_attr( $m['louies_repeat'] ); ?>" id="louies-repeat-box">
			<p style="margin-top:0">
				<label style="font-weight:400;margin-right:16px"><input type="radio" name="louies_repeat" value="none" <?php checked( $m['louies_repeat'], 'none' ); ?>> <?php esc_html_e( 'Just once', 'louies' ); ?></label>
				<label style="font-weight:400;margin-right:16px"><input type="radio" name="louies_repeat" value="weekly" <?php checked( $m['louies_repeat'], 'weekly' ); ?>> <?php esc_html_e( 'Every week', 'louies' ); ?></label>
				<label style="font-weight:400"><input type="radio" name="louies_repeat" value="monthly" <?php checked( $m['louies_repeat'], 'monthly' ); ?>> <?php esc_html_e( 'Once a month', 'louies' ); ?></label>
			</p>

			<div class="louies-repeat-only louies-when-weekly">
				<p style="font-weight:600;margin-bottom:6px"><?php esc_html_e( 'On these days:', 'louies' ); ?></p>
				<p class="louies-days">
					<?php foreach ( $names as $i => $n ) : ?>
						<label><input type="checkbox" name="louies_weekdays[]" value="<?php echo (int) $i; ?>" <?php checked( in_array( $i, $weekdays, true ) ); ?>> <?php echo esc_html( $n ); ?></label>
					<?php endforeach; ?>
				</p>
			</div>

			<div class="louies-repeat-only louies-when-monthly">
				<p>
					<label style="font-weight:400"><?php esc_html_e( 'On the', 'louies' ); ?>
						<select name="louies_monthly_nth">
							<?php foreach ( array( '1' => 'first', '2' => 'second', '3' => 'third', '4' => 'fourth', 'last' => 'last' ) as $v => $l ) : ?>
								<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $m['louies_monthly_nth'], $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
						<select name="louies_monthly_day">
							<?php foreach ( array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ) as $i => $n ) : ?>
								<option value="<?php echo (int) $i; ?>" <?php selected( (int) $m['louies_monthly_day'], $i ); ?>><?php echo esc_html( $n ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php esc_html_e( 'of every month', 'louies' ); ?>
					</label>
				</p>
			</div>

			<div class="louies-repeat-only louies-when-repeat">
				<p style="margin-bottom:.2rem">
					<label style="font-weight:600"><?php esc_html_e( 'Stop repeating on', 'louies' ); ?>
						<input type="date" name="louies_repeat_until" value="<?php echo esc_attr( $m['louies_repeat_until'] ); ?>">
					</label>
				</p>
				<p class="louies-hint" style="margin-bottom:1rem"><?php esc_html_e( 'Leave blank to keep going forever.', 'louies' ); ?></p>
				<p>
					<label style="font-weight:600"><?php esc_html_e( 'Skip these dates', 'louies' ); ?><br>
						<input type="text" name="louies_exceptions" style="max-width:420px" value="<?php echo esc_attr( $m['louies_exceptions'] ); ?>" placeholder="2026-12-25, 2027-01-01">
					</label>
					<span class="louies-hint"><?php esc_html_e( 'Cancel one night without deleting the whole series. Comma separated, YYYY-MM-DD.', 'louies' ); ?></span>
				</p>
			</div>
		</div>

		<label for="louies_featured"><?php esc_html_e( 'Front page', 'louies' ); ?></label>
		<div>
			<label style="font-weight:400"><input type="checkbox" id="louies_featured" name="louies_featured" value="1" <?php checked( $m['louies_featured'], '1' ); ?>> <?php esc_html_e( 'Highlight this one at the top of the home page', 'louies' ); ?></label>
		</div>
	</div>

	<script>
	( function () {
		var box = document.getElementById( 'louies-repeat-box' );
		if ( ! box ) { return; }
		function sync() {
			var v = ( document.querySelector( 'input[name="louies_repeat"]:checked' ) || {} ).value || 'none';
			box.className = 'louies-box louies-repeat-' + v;
		}
		Array.prototype.forEach.call(
			document.querySelectorAll( 'input[name="louies_repeat"]' ),
			function ( r ) { r.addEventListener( 'change', sync ); }
		);
		sync();
	} )();
	</script>
	<?php
}

function louies_menu_meta_box( $post ) {
	wp_nonce_field( 'louies_save_menu', 'louies_menu_nonce' );
	$price = get_post_meta( $post->ID, '_louies_price', true );
	$desc  = get_post_meta( $post->ID, '_louies_desc', true );
	?>
	<p>
		<label style="font-weight:600;display:block;margin-bottom:4px"><?php esc_html_e( 'Price', 'louies' ); ?></label>
		<input type="text" name="louies_price" value="<?php echo esc_attr( $price ); ?>" style="width:160px" placeholder="$6.00">
		<span style="color:#666;font-size:12px;margin-left:8px"><?php esc_html_e( 'Leave blank for a plain list entry, like a spirit on the back bar.', 'louies' ); ?></span>
	</p>
	<p>
		<label style="font-weight:600;display:block;margin-bottom:4px"><?php esc_html_e( 'Short description', 'louies' ); ?></label>
		<input type="text" name="louies_desc" value="<?php echo esc_attr( $desc ); ?>" style="width:100%;max-width:560px" placeholder="Juicy, flavorful, and classic">
	</p>
	<p style="color:#666;font-size:12px">
		<?php esc_html_e( 'Use the Order field in the Page Attributes box on the right to move an item up or down its section.', 'louies' ); ?>
	</p>
	<?php
}

add_action( 'save_post_louies_event', function ( $post_id ) {
	if ( ! isset( $_POST['louies_event_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['louies_event_nonce'] ), 'louies_save_event' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text = array( 'louies_date', 'louies_time_start', 'louies_time_end', 'louies_price', 'louies_monthly_nth', 'louies_monthly_day', 'louies_repeat_until', 'louies_exceptions' );
	foreach ( $text as $key ) {
		update_post_meta( $post_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}

	update_post_meta( $post_id, '_louies_ticket_url', esc_url_raw( wp_unslash( $_POST['louies_ticket_url'] ?? '' ) ) );

	$repeat = sanitize_key( wp_unslash( $_POST['louies_repeat'] ?? 'none' ) );
	update_post_meta( $post_id, '_louies_repeat', in_array( $repeat, array( 'none', 'weekly', 'monthly' ), true ) ? $repeat : 'none' );

	$days = array_map( 'intval', (array) ( $_POST['louies_weekdays'] ?? array() ) );
	$days = array_values( array_unique( array_filter( $days, function ( $d ) { return $d >= 0 && $d <= 6; } ) ) );
	sort( $days );
	update_post_meta( $post_id, '_louies_weekdays', implode( ',', $days ) );

	update_post_meta( $post_id, '_louies_featured', empty( $_POST['louies_featured'] ) ? '' : '1' );
} );

add_action( 'save_post_louies_menu_item', function ( $post_id ) {
	if ( ! isset( $_POST['louies_menu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['louies_menu_nonce'] ), 'louies_save_menu' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_louies_price', sanitize_text_field( wp_unslash( $_POST['louies_price'] ?? '' ) ) );
	update_post_meta( $post_id, '_louies_desc', sanitize_text_field( wp_unslash( $_POST['louies_desc'] ?? '' ) ) );
} );
