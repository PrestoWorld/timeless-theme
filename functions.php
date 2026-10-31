<?php
/**
 * Timeless functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Timeless 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'timeless_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Timeless 1.0
	 *
	 * @return void
	 */
	function timeless_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'timeless_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'timeless_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Timeless 1.0
	 *
	 * @return void
	 */
	function timeless_editor_style() {
		add_editor_style( get_parent_theme_file_uri( 'assets/css/editor-style.css' ) );
	}
endif;
add_action( 'after_setup_theme', 'timeless_editor_style' );

// Registers custom block styles.
if ( ! function_exists( 'timeless_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Timeless 1.0
	 *
	 * @return void
	 */
	function timeless_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'timeless' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'timeless_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'timeless_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Timeless 1.0
	 *
	 * @return void
	 */
	function timeless_pattern_categories() {

		register_block_pattern_category(
			'timeless_page',
			array(
				'label'       => __( 'Pages', 'timeless' ),
				'description' => __( 'A collection of full page layouts.', 'timeless' ),
			)
		);

		register_block_pattern_category(
			'timeless_post-format',
			array(
				'label'       => __( 'Post formats', 'timeless' ),
				'description' => __( 'A collection of post format patterns.', 'timeless' ),
			)
		);
	}
endif;
add_action( 'init', 'timeless_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'timeless_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Timeless 1.0
	 *
	 * @return void
	 */
	function timeless_register_block_bindings() {
		register_block_bindings_source(
			'timeless/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'timeless' ),
				'get_value_callback' => 'timeless_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'timeless_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'timeless_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Timeless 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function timeless_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

/**
 * Forces the HTML lang attribute to Vietnamese (matches the target frontend).
 */
if ( ! function_exists( 'timeless_language_attributes' ) ) :
	function timeless_language_attributes( $attr ) {
		$attr = preg_replace( '/lang="[^"]*"/', 'lang="vi"', $attr );
		if ( stripos( $attr, 'lang=' ) === false ) {
			$attr = trim( $attr ) . ' lang="vi"';
		}
		return $attr;
	}
endif;
add_filter( 'language_attributes', 'timeless_language_attributes' );

/**
 * Injects head meta tags, font preconnects and document title.
 */
if ( ! function_exists( 'timeless_head_meta' ) ) :
	function timeless_head_meta() {
		?>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
		<title>CheepHub - Tiệm Sách &amp; Thuê Sách Toàn Quốc 20k/45 Ngày</title>
		<meta name="description" content="CheepHub - Nền tảng mua bán, ký gửi và cho thuê sách cũ giá rẻ toàn quốc chỉ 20.000đ/45 ngày. Trải nghiệm đọc sách văn minh, tối giản và tiết kiệm." />
		<?php
	}
endif;
add_action( 'wp_head', 'timeless_head_meta', 1 );

/**
 * Enqueues the compiled Tailwind CSS on the front (Gutenberg block rendering only, no SPA).
 */
if ( ! function_exists( 'timeless_enqueue_assets' ) ) :
	function timeless_enqueue_assets() {
		wp_enqueue_style(
			'timeless-tailwind',
			get_parent_theme_file_uri( 'assets/css/dist/tailwind.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'timeless_enqueue_assets' );

