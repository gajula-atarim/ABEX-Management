<?php
/**
 * ABEX Management Services — child theme bootstrap.
 *
 * Parent: Hello Elementor.
 *
 * @package ABEX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ABEX_VERSION', '1.0.1' );

/**
 * Enqueue the parent stylesheet, the ABEX webfonts and the design system.
 *
 * Archivo is loaded with its `wdth` axis because the design relies on
 * font-stretch values between 105% and 118%; a static Archivo would collapse
 * every display heading back to its normal width.
 */
function abex_enqueue_assets() {
	wp_enqueue_style(
		'hello-elementor-parent',
		get_template_directory_uri() . '/style.css',
		array(),
		ABEX_VERSION
	);

	wp_enqueue_style(
		'abex-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:ital,wdth,wght@0,62..125,400..900;1,62..125,400..900&family=Public+Sans:wght@400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'abex-design-system',
		get_stylesheet_directory_uri() . '/assets/abex.css',
		array( 'hello-elementor-parent', 'abex-fonts' ),
		ABEX_VERSION
	);

	wp_enqueue_script(
		'abex-motion',
		get_stylesheet_directory_uri() . '/assets/abex.js',
		array(),
		ABEX_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'abex_enqueue_assets', 20 );

/**
 * Load the design system inside the Elementor editor canvas too, so what the
 * editor shows matches the front end.
 */
function abex_enqueue_editor_assets() {
	wp_enqueue_style(
		'abex-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:ital,wdth,wght@0,62..125,400..900;1,62..125,400..900&family=Public+Sans:wght@400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'abex-design-system',
		get_stylesheet_directory_uri() . '/assets/abex.css',
		array(),
		ABEX_VERSION
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'abex_enqueue_editor_assets' );
add_action( 'elementor/preview/enqueue_styles', 'abex_enqueue_editor_assets' );

/**
 * Preconnect to the Google Fonts hosts so webfonts start downloading sooner.
 *
 * @param array  $hints    Resource hints for the given relation.
 * @param string $relation Relation type.
 * @return array
 */
function abex_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.googleapis.com' );
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => '',
		);
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'abex_resource_hints', 10, 2 );

/**
 * Register the menu location the UAE header template reads from.
 */
function abex_register_menus() {
	register_nav_menus(
		array(
			'abex_primary' => __( 'ABEX Primary', 'abex' ),
		)
	);
}
add_action( 'after_setup_theme', 'abex_register_menus' );

/**
 * Render the UAE (Header Footer Elementor) templates.
 *
 * Hello Elementor draws its own header and footer unless the
 * `hello_elementor_header_footer` filter says otherwise. UAE's own theme
 * auto-detection does not take over on this install, so the child theme wires
 * the templates in explicitly — preferring UAE's public render functions and
 * falling back to rendering the Elementor template directly.
 */
function abex_hfe_template_id( $type ) {
	static $ids = array();

	if ( isset( $ids[ $type ] ) ) {
		return $ids[ $type ];
	}

	$found = get_posts(
		array(
			'post_type'        => 'elementor-hf',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
			'meta_key'         => 'ek_hfe_template_type',
			'meta_value'       => $type,
		)
	);

	$ids[ $type ] = ! empty( $found ) ? (int) $found[0] : 0;

	return $ids[ $type ];
}

/**
 * Render one Elementor template by post ID.
 *
 * @param int $id Template post ID.
 * @return string
 */
function abex_render_elementor_template( $id ) {
	if ( ! $id || ! did_action( 'elementor/loaded' ) ) {
		return '';
	}

	return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
}

add_filter( 'hello_elementor_header_footer', '__return_false' );

/**
 * Output the ABEX header immediately after <body>.
 */
function abex_output_header() {
	// Rendered directly from the template rather than through UAE's own
	// renderer: on this install hfe_render_header() emits an empty <header>
	// wrapper, which looks like output but shows nothing.
	$id = abex_hfe_template_id( 'type_header' );
	if ( ! $id ) {
		return;
	}

	$html = abex_render_elementor_template( $id );
	if ( '' === trim( wp_strip_all_tags( $html ) ) && false === strpos( $html, '<img' ) ) {
		return;
	}

	echo '<header class="abex-site-header">' . $html . '</header>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
}
add_action( 'wp_body_open', 'abex_output_header', 5 );

/**
 * Output the ABEX footer at the end of the document body.
 */
function abex_output_footer() {
	$id = abex_hfe_template_id( 'type_footer' );
	if ( ! $id ) {
		return;
	}

	$html = abex_render_elementor_template( $id );
	if ( '' === trim( wp_strip_all_tags( $html ) ) && false === strpos( $html, '<img' ) ) {
		return;
	}

	echo '<footer class="abex-site-footer">' . $html . '</footer>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
}
add_action( 'wp_footer', 'abex_output_footer', 5 );

/**
 * Where enquiries are delivered. Filterable so the address can be changed
 * without editing the theme.
 *
 * @return string
 */
function abex_enquiry_recipient() {
	/*
	 * TEST ADDRESS. Deliberately not the client's inbox while the site is on
	 * staging. Before launch, change this to abex@abex.com.au (or filter it).
	 */
	return apply_filters( 'abex_enquiry_recipient', 'test@gmail.com' );
}

/**
 * The enquiry form, as a shortcode so it can carry a nonce and render its own
 * confirmation state.
 *
 * @return string
 */
function abex_enquiry_form_shortcode() {
	$state = isset( $_GET['abex_sent'] ) ? sanitize_key( wp_unslash( $_GET['abex_sent'] ) ) : '';

	ob_start();

	if ( 'ok' === $state ) {
		echo '<div class="abex-form__notice abex-form__notice--ok">Thank you. We will be in touch shortly.</div>';
	} elseif ( 'invalid' === $state ) {
		echo '<div class="abex-form__notice abex-form__notice--err">Please enter your name and a valid email address.</div>';
	} elseif ( 'error' === $state ) {
		echo '<div class="abex-form__notice abex-form__notice--err">Sorry, the message could not be sent. Please call +61 (07) 5543 5553.</div>';
	}
	?>
	<form class="abex-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="abex_enquiry">
		<?php wp_nonce_field( 'abex_enquiry', 'abex_nonce' ); ?>
		<p class="abex-form__hp" aria-hidden="true">
			<label>Leave this field empty
				<input type="text" name="abex_website" tabindex="-1" autocomplete="off" value="">
			</label>
		</p>
		<div class="abex-form__grid">
			<label class="abex-form__full">Your name*
				<input type="text" name="abex_name" placeholder="Write your name here" required>
			</label>
			<label>Your phone
				<input type="tel" name="abex_phone" placeholder="Write your phone number">
			</label>
			<label>Your email*
				<input type="email" name="abex_email" placeholder="Write your email" required>
			</label>
			<label class="abex-form__full">Company
				<input type="text" name="abex_company" placeholder="Your organisation">
			</label>
			<label class="abex-form__full">Service
				<select name="abex_service">
					<option>Select a service</option>
					<option>Project management systems and controls</option>
					<option>Project planning and scheduling</option>
					<option>Procurement management</option>
					<option>Contracts Management</option>
					<option>Dispute resolution</option>
					<option>Other</option>
				</select>
			</label>
			<label class="abex-form__full">Message
				<textarea name="abex_message" rows="3" placeholder="Tell us about your project"></textarea>
			</label>
		</div>
		<button type="submit" class="abex-btn">Send message</button>
	</form>
	<?php

	return ob_get_clean();
}
add_shortcode( 'abex_enquiry_form', 'abex_enquiry_form_shortcode' );

/**
 * Receive an enquiry, email it, and bounce back to the form with a result.
 */
function abex_handle_enquiry() {
	$back = wp_get_referer();
	if ( ! $back ) {
		$back = home_url( '/' );
	}

	$finish = static function ( $state ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'abex_sent', $state, $back ) . '#contact' );
		exit;
	};

	$nonce = isset( $_POST['abex_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abex_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'abex_enquiry' ) ) {
		$finish( 'error' );
	}

	// Honeypot: silently accept so bots do not learn they were caught.
	if ( ! empty( $_POST['abex_website'] ) ) {
		$finish( 'ok' );
	}

	$field = static function ( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	};

	$name    = $field( 'abex_name' );
	$email   = sanitize_email( isset( $_POST['abex_email'] ) ? wp_unslash( $_POST['abex_email'] ) : '' );
	$phone   = $field( 'abex_phone' );
	$company = $field( 'abex_company' );
	$service = $field( 'abex_service' );
	$message = isset( $_POST['abex_message'] )
		? sanitize_textarea_field( wp_unslash( $_POST['abex_message'] ) )
		: '';

	if ( '' === $name || ! is_email( $email ) ) {
		$finish( 'invalid' );
	}

	$lines = array(
		'Name:     ' . $name,
		'Email:    ' . $email,
		'Phone:    ' . ( '' !== $phone ? $phone : '-' ),
		'Company:  ' . ( '' !== $company ? $company : '-' ),
		'Service:  ' . ( '' !== $service ? $service : '-' ),
		'',
		'Message:',
		'' !== $message ? $message : '-',
		'',
		'---',
		'Sent from ' . home_url( '/' ),
	);

	$sent = wp_mail(
		abex_enquiry_recipient(),
		sprintf( 'Website enquiry from %s', $name ),
		implode( "\n", $lines ),
		array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $name, $email ),
		)
	);

	$finish( $sent ? 'ok' : 'error' );
}
add_action( 'admin_post_nopriv_abex_enquiry', 'abex_handle_enquiry' );
add_action( 'admin_post_abex_enquiry', 'abex_handle_enquiry' );
