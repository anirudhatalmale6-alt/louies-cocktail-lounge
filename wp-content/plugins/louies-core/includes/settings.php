<?php
/**
 * The handful of details that appear all over the site - phone, address, hours.
 * One screen, one place, so a new phone number is a single edit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function louies_settings_fields() {
	return array(
		'phone'        => array( 'Phone number', '(916) 362-9151', 'Shown in the header and footer. Tapping it on a phone dials.' ),
		'address_1'    => array( 'Street address', '3030 Mather Field Rd.', '' ),
		'address_2'    => array( 'City, state, ZIP', 'Rancho Cordova, CA 95670', '' ),
		'hours'        => array( 'Opening hours', '6:00 am &ndash; 2:00 am daily', '' ),
		'happy_hour'   => array( 'Happy hour', '6am &ndash; 10am and 4pm &ndash; 7pm', '' ),
		'maps_query'   => array( 'Google Maps search', '3030 Mather Field Rd, Rancho Cordova, CA 95670', 'What the map and the Directions button look up.' ),
		'facebook'     => array( 'Facebook URL', '', '' ),
		'instagram'    => array( 'Instagram URL', '', '' ),
		'email'        => array( 'Contact email', 'info@louiescocktails.com', 'Where the contact form sends to.' ),
		'notice'       => array( 'Banner message', '', 'Optional. Shows a strip across the top of every page - handy for "Closed Thanksgiving".' ),
		'hero_image_id' => array( 'Home page background photo (media ID)', '', 'Upload to Media, open it, and copy the number from the URL. Leave blank for the default.' ),
		'gallery_ids'  => array( 'Photo strip (media IDs)', '', 'Comma separated. These are the photos of the room shown on the home page and the gallery.' ),
		'social_image_id' => array( 'Share image (media ID)', '', 'The picture that appears when someone posts a link to the site on Facebook. 1200x630 works best.' ),
		'seo_tagline'  => array( 'Search description', 'Karaoke Wednesday to Saturday, live music, bingo, pool and every game on 13 screens. Two happy hours daily. Rancho Cordova, one block off Highway 50.', 'The sentence Google shows under the site name. Aim for 150-160 characters.' ),
	);
}

function louies_option( $key, $fallback = '' ) {
	$opts    = get_option( 'louies_settings', array() );
	$fields  = louies_settings_fields();
	$default = $fields[ $key ][1] ?? $fallback;
	$value   = $opts[ $key ] ?? '';
	return '' === trim( (string) $value ) ? $default : $value;
}

function louies_phone_link() {
	$digits = preg_replace( '/[^0-9+]/', '', louies_option( 'phone' ) );

	// A bare 10-digit US number dials more reliably with the country code,
	// especially from a phone that's roaming.
	if ( preg_match( '/^[0-9]{10}$/', $digits ) ) {
		$digits = '+1' . $digits;
	} elseif ( preg_match( '/^1[0-9]{10}$/', $digits ) ) {
		$digits = '+' . $digits;
	}

	return 'tel:' . $digits;
}

function louies_directions_link() {
	return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( louies_option( 'maps_query' ) );
}

function louies_map_embed_src() {
	return 'https://maps.google.com/maps?q=' . rawurlencode( louies_option( 'maps_query' ) ) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
}

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Bar Details', 'louies' ),
		__( 'Bar Details', 'louies' ),
		'manage_options',
		'louies-settings',
		'louies_settings_page',
		'dashicons-store',
		22
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'louies_settings_group', 'louies_settings', array(
		'sanitize_callback' => function ( $input ) {
			$clean = array();
			foreach ( louies_settings_fields() as $key => $meta ) {
				$raw = $input[ $key ] ?? '';
				$clean[ $key ] = in_array( $key, array( 'facebook', 'instagram' ), true )
					? esc_url_raw( $raw )
					: sanitize_text_field( $raw );
			}
			return $clean;
		},
	) );
} );

function louies_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bar Details', 'louies' ); ?></h1>
		<p><?php esc_html_e( 'Change something here and it updates everywhere on the site at once.', 'louies' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'louies_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( louies_settings_fields() as $key => $meta ) : ?>
					<tr>
						<th scope="row"><label for="louies_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta[0] ); ?></label></th>
						<td>
							<input type="text" id="louies_<?php echo esc_attr( $key ); ?>" name="louies_settings[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( louies_option( $key ) ); ?>" class="regular-text">
							<?php if ( $meta[2] ) : ?>
								<p class="description"><?php echo esc_html( $meta[2] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
