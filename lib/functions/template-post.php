<?php
/**
 * Post Template Functions.
 *
 * Gets content for the current post in the loop.
 *
 * @package    Cherry_Framework
 * @subpackage Functions
 * @author     Cherry Team <support@cherryframework.com>
 * @copyright  Copyright (c) 2012 - 2014, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Check if post has an image attached.
 *
 * @since  4.0.0
 * @param  int  $post_id Optional. Post ID.
 * @return bool          Whether post has an image attached.
 */
function cherry_has_post_thumbnail( $post_id = null ) {
	$post_id   = ( null === $post_id ) ? get_the_ID() : $post_id;
	$post_type = get_post_type( $post_id );

	if ( !current_theme_supports( 'post-thumbnails' ) ) {
		return false;
	}

	$thumbnail_support = post_type_supports( $post_type, 'thumbnail' );

	if ( 'attachment' === $post_type ) {

		$mime_type = get_post_mime_type();

		if ( !$thumbnail_support && $mime_type ) {

			if ( 0 === strpos( $mime_type, 'audio/' ) ) {

				$thumbnail_support = post_type_supports( 'attachment:audio', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:audio' );

			} elseif ( 0 === strpos( $mime_type, 'video/' ) ) {

				$thumbnail_support = post_type_supports( 'attachment:video', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:video' );

			}

		}

	}

	if ( $thumbnail_support && has_post_thumbnail( $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Display the post thumbnail.
 *
 * @since 4.0.0
 */
function cherry_the_post_thumbnail( $args ) {
	echo cherry_get_the_post_thumbnail( $args );
}

/**
 * Retrieve the post thumbnail.
 *
 * @since  4.0.0
 * @param  array  $args
 * @return string $output The post thumbnail HTML
 */
function cherry_get_the_post_thumbnail( $args ) {
	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	if ( !cherry_has_post_thumbnail( $post_id ) ) {
		return;
	}

	/**
	 * Filter the default arguments used to display a post thumbnail.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_get_the_post_thumbnail_defaults', array(
		'container'       => 'figure',
		'container_class' => 'entry-thumbnail',
		'size'            => 'slider-post-thumbnail',
		'class'           => '',
		'before'          => '',
		'after'           => '',
		'wrap'            => is_singular( $post_type ) ? '<%1$s class="%2$s">%5$s</%1$s>' : '<%1$s class="%2$s"><a href="%3$s" title="%4$s">%5$s</a></%1$s>',
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	// Get the intermediate image sizes and add the full size to the array.
	$sizes = get_intermediate_image_sizes();
	$sizes[] = 'full';

	// Checks if a value exists in an arrays
	$size = ( in_array( $args['size'], $sizes ) ) ? $args['size'] : $defaults['size'];

	// Gets the Featured Image.
	$thumbnail = get_the_post_thumbnail( $post_id, $args['size'], array( 'class' => $args['class'] ) );
	$thumbnail = $args['before'] . $thumbnail . $args['after'];

	$output = sprintf(
		$args['wrap'],
		tag_escape( $args['container'] ),
		esc_attr( $args['container_class'] ),
		get_permalink( $post_id ),
		esc_attr( the_title_attribute( 'echo=0' ) ),
		$thumbnail
	);

	/**
	 * Filter the retrieved post thumbnail.
	 *
	 * @since 4.0.0
	 */
	return apply_filters( 'cherry_the_post_thumbnail', $output, $args );
}

/**
 * Display the post title.
 *
 * @since 4.0.0
 */
function cherry_the_post_title( $args ) {
	echo cherry_get_the_post_title( $args );
}

/**
 * Retrieve the post title.
 *
 * @since 4.0.0
 */
function cherry_get_the_post_title( $args ) {
	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	/**
	 * Filter the default arguments used to display a post title.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_get_the_post_title_defaults', array(
		'tag'    => 'h2',
		'class'  => '',
		'linked' => is_singular( $post_type ) ? 'no' : 'yes',
		'url'    => 'permalink',
		'before' => '<header class="entry-header">',
		'after'  => '</header>',
		'wrap'   => '<%1$s class="%2$s">%3$s</%1$s>',
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );
	$args['class'] .= ' entry-title';

	$post_title = the_title( '', '', false );

	if ( empty( $post_title ) ) {
		return;
	}

	if ( 'yes' === $args['linked'] ) {
		$url = ( $args['url'] ) ? $args['url'] : $defaults['url'];

		if ( 'permalink' == $url ) {
			$url = get_permalink( $post_id );
		}

		$linked_wrap = apply_filters(
			'cherry_get_the_post_linked_wrap',
			'<a href="%1$s" rel="bookmark">%2$s</a>',
			$post_id,
			$post_type
		);
		$post_title = sprintf( $linked_wrap, $url, $post_title );
	}

	$output = sprintf(
		$args['wrap'],
		tag_escape( $args['tag'] ),
		esc_attr( trim( $args['class'] ) ),
		$post_title
	);

	$output = $args['before'] . $output . $args['after'];

	/**
	 * Filter the displayed post title.
	 *
	 * @since 4.0.0
	 */
	return apply_filters( 'cherry_get_the_post_title', $output, $args );
}

/**
 * Display the post content.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_the_post_content( $args ) {
	global $post;

	if ( !$post->post_content ) {
		return;
	}

	$post_id   = $post->ID;
	$post_type = $post->post_type;

	/**
	 * Filter the default arguments used to display a post content.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_the_post_content_defaults', array(
		'type'   => 'full',
		'class'  => 'entry-content',
		'before' => '',
		'after'  => '',
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	if ( !is_singular( $post_type ) ) {
		$args['type'] = cherry_get_option( 'blog-content-type' );
	}

	printf( '<div class="%1$s">%2$s', esc_attr( $args['class'] ), $args['before'] );

	if ( 'full' == $args['type'] ) {
		the_content( '' );
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'cherry' ),
			'after'  => '</div>',
		) );

	} elseif ( 'part' == $args['type'] ) {
		echo wp_trim_excerpt();
	}

	printf( '%s</div>', $args['after'] );
}

/**
 * Retrieve the post content.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_get_the_post_content( $args ) {
	ob_start();
	cherry_the_post_content( $args );
	$output = ob_get_contents();
	ob_end_clean();

	return apply_filters( 'cherry_get_the_post_content', $output );
}

/**
 * Display the post excerpt.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_the_post_excerpt( $args ) {
	global $post;

	$post_id   = $post->ID;
	$post_type = $post->post_type;

	if ( !post_type_supports( $post_type, 'excerpt' ) ) {
		return;
	}

	if ( !has_excerpt( $post_id ) ) {
		return;
	}

	/**
	 * Filter the default arguments used to display a post excerpt.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_the_post_excerpt_defaults', array(
		'class'  => 'entry-summary',
		'before' => '',
		'after'  => '',
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	printf( '<div class="%1$s">%2$s', esc_attr( $args['class'] ), $args['before'] );
		the_excerpt();
	printf( '%s</div>', $args['after'] );
}

/**
 * Retrieve the post excerpt.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_get_the_post_excerpt( $args ) {
	ob_start();
	cherry_the_post_excerpt( $args );
	$excerpt = ob_get_contents();
	ob_end_clean();

	return apply_filters( 'cherry_get_the_post_excerpt', $excerpt );
}

/**
 * Display the post button.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_the_post_button( $args ) {
	echo cherry_get_the_post_button( $args );
}

/**
 * Retrieve the post button.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_get_the_post_button( $args ) {

	if ( 'false' == cherry_get_option( 'blog-button' ) ) {
		return;
	}

	if ( '' == cherry_get_option( 'blog-button-text' ) ) {
		return;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	if ( is_singular( $post_type ) ) {
		return;
	}

	/**
	 * Filter the default arguments used to display a post button.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_get_the_post_button_defaults', array(
		'before' => '<div class="entry-permalink">',
		'after'  => '</div>',
		'class'  => 'btn btn-default',
		'wrap'   => '<a href="%1$s" class="%2$s">%3$s</a>',
		'text'   => cherry_get_option( 'blog-button-text' ),
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	$output = sprintf(
		$args['wrap'],
		get_permalink( $post_id ),
		esc_attr( $args['class'] ),
		esc_html( $args['text'] )
	);

	$output = $args['before'] . $output . $args['after'];

	return apply_filters( 'cherry_get_the_post_button', $output, $args );
}

/**
 * Display featured image.
 *
 * @since 4.0.0
 * @param array $args
 */
function cherry_the_post_image( $args ) {
	echo cherry_get_the_post_image( $args );
}

/**
 * Get featured image.
 * If has post thumbnail - will get post thumbnail, else - get first image from content.
 *
 * @since  4.0.0
 * @param  array  $args
 * @return string
 */
function cherry_get_the_post_image( $args ) {
	/**
	 * Filter post format image output to rewrite image from child theme or plugins.
	 * @since  4.0.0
	 */
	$result = apply_filters( 'cherry_pre_get_post_image', false );

	if ( false !== $result ) {
		return $result;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	/**
	 * Filter the default arguments used to display a post image.
	 *
	 * @since 4.0.0
	 * @param array  $defaults  Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_get_the_post_image_defaults', array(
		'container'       => 'figure',
		'container_class' => 'post-thumbnail',
		'size'            => 'slider-post-thumbnail',
		'before'          => '',
		'after'           => '',
		'wrap'            => '<%1$s class="%2$s"><a href="%4$s" class="%2$s-link popup-img" data-init=\'%5$s\'>%3$s</a></%1$s>'
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	$default_init = array(
		'type' => 'image'
	);

	/**
	 * Filter the arguments used to init image zoom popup.
	 *
	 * @since 4.0.0
	 * @param array $args Array of arguments.
	 */
	$init = apply_filters( 'cherry_get_the_post_image_zoom_init', $default_init );
	$init = wp_parse_args( $init, $default_init );

	$init = json_encode( $init );

	if ( cherry_has_post_thumbnail( $post_id ) ) {

		$thumb = get_the_post_thumbnail( $post_id, $args['size'], array( 'class' => $args['container_class'] . '-img' ) );
		$thumb = $args['before'] . $thumb . $args['after'];
		$url   = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );

	} else {

		$img = cherry_get_post_images();

		if ( ! $img || empty( $img ) || empty( $img[0] ) ) {
			return false;

		} elseif ( is_int( $img[0] ) ) {

			$thumb = wp_get_attachment_image( $img[0], $args['size'], '', array( 'class' => $args['container_class'] . '-img' ) );
			$thumb = $args['before'] . $thumb . $args['after'];
			$url   = wp_get_attachment_url( $img[0] );

		} else {

			global $_wp_additional_image_sizes;

			if ( ! isset( $_wp_additional_image_sizes[ $args['size'] ] ) ) {
				return false;
			}

			$thumb = '<img src="' . esc_url( $img[0] ) . '" class="' . $args['container_class'] . '-img" width="' . $_wp_additional_image_sizes[ $args['size'] ]['width'] . '">';
			$thumb = $args['before'] . $thumb . $args['after'];
			$url   = $img[0];

		}
	}

	$result = sprintf(
		$args['wrap'],
		$args['container'], $args['container_class'], $thumb, $url, $init
	);

	/**
	 * Filter featured image.
	 *
	 * @since 4.0.0
	 */
	return apply_filters( 'cherry_get_the_post_image', $result, $args );
}

/**
 * Display featured gallery.
 *
 * @since 4.0.0
 */
function cherry_the_post_gallery() {
	echo cherry_get_the_post_gallery();
}

/**
 * Get featured gallery.
 * If has post thumbnail - will get post thumbnail, else - get first image from content.
 *
 * @since  4.0.0
 * @return string
 */
function cherry_get_the_post_gallery() {
	/**
	 * Filter post format gallery output to rewrite gallery from child theme or plugins
	 * @since  4.0.0
	 */
	$result = apply_filters( 'cherry_pre_get_post_gallery', false );

	if ( false !== $result ) {
		return $result;
	}

	$post_id = get_the_ID();

	// first - try to get images from galleries in post
	$shortcode_replaced = cherry_get_option( 'blog-gallery-shortcode', 'true' );
	$is_html            = ( 'true' == $shortcode_replaced ) ? true : false;
	$post_gallery       = get_post_gallery( $post_id, $is_html );

	// if stanadrd gallery shortcode replaced with cherry - return HTML
	if ( is_string( $post_gallery ) && ! empty( $post_gallery ) ) {
		return $post_gallery;
	}

	if ( ! empty( $post_gallery['ids'] ) ) {
		$post_gallery = explode( ',', $post_gallery['ids'] );
	} elseif ( ! empty( $post_gallery['src'] ) ) {
		$post_gallery = $post_gallery['src'];
	} else {
		$post_gallery = false;
	}

	// if can't try to catch images inserted into post
	if ( ! $post_gallery ) {
		$post_gallery = cherry_get_post_images( $post_id, 15 );
	}

	// and if not find any images - try to get images attached to post
	if ( ! $post_gallery || empty( $post_gallery ) ) {

		$attachments = get_children( array(
			'post_parent'    => $post_id,
			'posts_per_page' => 3,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
		) );

		if ( $attachments && is_array( $attachments ) ) {
			$post_gallery = array_keys( $attachments );
		}
	}

	if ( ! $post_gallery || empty( $post_gallery ) ) {
		return false;
	}

	$output = cherry_get_gallery_html( $post_gallery );

	/**
	 * Filter a post gallery.
	 *
	 * @since 4.0.0
	 */
	$output = apply_filters( 'cherry_get_the_post_gallery', $output );

	return $output;
}

/**
 * Custom output for gallery shortcode.
 *
 * @since  4.0.0
 * @param  array  $atts Shortcode atts
 * @return string       Gallery HTML
 */
function cherry_gallery_shortcode( $result, $attr ) {

	$replace_allowed = cherry_get_option( 'blog-gallery-shortcode', 'true' );

	if ( 'true' != $replace_allowed ) {
		return '';
	}

	/**
	 * Filter gallery output.
	 *
	 * @since  4.0.0
	 */
	$result = apply_filters( 'cherry_pre_get_gallery_shortcode', false, $attr );

	$post = get_post();

	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'include'    => '',
		'exclude'    => '',
		'link'       => '',
	), $attr, 'gallery' );

	if ( false !== $result ) {
		return $result;
	}

	$id = intval( $atts['id'] );

	if ( ! empty( $atts['include'] ) ) {

		$attachments = explode( ',', str_replace( ' ', '', $atts['include'] ) );

	} elseif ( ! empty( $atts['exclude'] ) ) {

		$attachments = get_children(
			array(
				'post_parent'    => $id,
				'exclude'        => $atts['exclude'],
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $atts['order'],
				'orderby'        => $atts['orderby'],
			)
		);
		$attachments = array_keys( $attachments );

	} else {

		$attachments = get_children(
			array(
				'post_parent'    => $id,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $atts['order'],
				'orderby'        => $atts['orderby'],
			)
		);

		$attachments = array_keys( $attachments );
	}

	if ( empty( $attachments ) || ! is_array( $attachments ) ) {
		return;
	}

	$result = cherry_get_gallery_html( $attachments );

	return $result;
}

/**
 * Build default gallery HTML from images array.
 *
 * @since  4.0.0
 * @param  array  $images Images array can contain image IDs or URLs
 * @return string         Gallery HTML markup
 */
function cherry_get_gallery_html( $images ) {

	$defaults = array(
		'container_class'  => 'post-gallery',
		'size'             => 'slider-post-thumbnail',
		'container_format' => '<div class="%2$s popup-gallery" data-init=\'%3$s\' data-popup-init=\'%4$s\'>%1$s</div>',
		'item_format'      => '<figure class="%3$s"><a href="%2$s" class="%3$s_link popup-gallery-item" >%1$s</a></figure>',
	);

	/**
	 * Filter default gallery arguments.
	 *
	 * @since  4.0.0
	 */
	$args = apply_filters( 'cherry_get_the_post_gallery_args', $defaults );
	$args = wp_parse_args( $args, $defaults );

	$default_slider_init = array(
		'infinite' => true,
		'speed'    => 300,
		'fade'     => true,
		'cssEase'  => 'linear',
	);

	/**
	 * Filter default gallery slider inits.
	 *
	 * @since  4.0.0
	 */
	$init = apply_filters( 'cherry_get_the_post_gallery_args', $default_slider_init );
	$init = wp_parse_args( $init, $default_slider_init );
	$init = json_encode( $init );

	$default_gall_init = array(
		'delegate' => '.popup-gallery-item',
		'type'     => 'image',
		'gallery'  => array(
			'enabled' => true
		)
	);

	/**
	 * Filter default gallery popup inits.
	 *
	 * @since  4.0.0
	 */
	$gall_init = apply_filters( 'cherry_get_the_post_gallery_popup_args', $default_gall_init );
	$gall_init = wp_parse_args( $gall_init, $default_gall_init );
	$gall_init = json_encode( $gall_init );

	$items = array();

	foreach ( $images as $img ) {

		if ( 0 < intval( $img ) ) {
			$image = wp_get_attachment_image( $img, $args['size'], '', array( 'class' => $args['container_class'] . '_item_img' ) );
			$url   = wp_get_attachment_url( $img );

			$attachment = get_post( $img );

			if ( ! empty( $attachment->post_excerpt ) ) {
				$image .= '<figcaption>' . wptexturize( $attachment->post_excerpt ) . '</figcaption>';
			}

		} else {

			global $_wp_additional_image_sizes;

			if ( ! isset( $_wp_additional_image_sizes[$args['size']] ) ) {
				$width = 'auto';
			} else {
				$width = $_wp_additional_image_sizes[$args['size']]['width'];
			}

			$image = '<img src="' . esc_url( $img ) . '" class="' . $args['container_class'] . '_item_img" width="' . $width . '">';
			$url   = $img;
		}

		$items[] = sprintf(
			$args['item_format'],
			$image, $url, $args['container_class'] . '_item'
		);
	}

	$items = implode( "\r\n", $items );

	$result = sprintf(
		$args['container_format'],
		$items, $args['container_class'], $init, $gall_init
	);

	return $result;
}

/**
 * Get images from post content.
 * Returns image ID's if can find this image in database,
 * returns image URL or bollen false in other case.
 *
 * @since  4.0.0
 *
 * @param  int $post_id post ID to search image in
 * @param  int $limit   max images count to search
 * @return bool|string|int
 */
function cherry_get_post_images( $post_id = null, $limit = 1 ) {

	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	$content = get_the_content();

	// Gets first image from content.
	preg_match_all( '/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $matches );

	if ( !isset( $matches[1] ) ) {
		return false;
	}

	$result = array();

	global $wpdb;

	for ( $i = 0; $i < $limit; $i++ ) {

		if ( empty( $matches[1][$i] ) ) {
			continue;
		}

		$image_src = esc_url( $matches[1][$i] );
		$image_src = preg_replace( '/^(.+)(-\d+x\d+)(\..+)$/', '$1$3', $image_src );

		// Try to get current image ID.
		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
		$id = $wpdb->get_var( $query );

		if ( ! $id ) {
			$result[] = $image_src;
		} else {
			$result[] = (int)$id;
		}

	}

	return $result;
}

/**
 * Show featured video.
 *
 * @since 4.0.0
 */
function cherry_the_post_video() {
	echo cherry_get_the_post_video();
}

/**
 * Get featured video.
 * Returns first finded video, iframe, object or embed tag in content.
 *
 * @since 4.0.0
 */
function cherry_get_the_post_video() {
	/**
	 * Filter post format video output to rewrite video from child theme or plugins.
	 *
	 * @since  4.0.0
	 */
	$result = apply_filters( 'cherry_pre_get_post_video', false );

	if ( false !== $result ) {
		return $result;
	}

	$content = apply_filters( 'the_content', get_the_content() );
	$types   = array( 'video', 'object', 'embed', 'iframe' );
	$embeds  = get_media_embedded_in_content( $content, $types );

	if ( empty( $embeds ) ) {
		return;
	}

	foreach ( $types as $tag ) {
		if ( preg_match( "/<{$tag}[^>]*>(.*?)<\/{$tag}>/", $embeds[0], $matches ) ) {
			$result = $matches[0];
			break;
		}
	}

	if ( false === $result ) {
		return;
	}

	$result = sprintf( '<div class="entry-video embed-responsive embed-responsive-16by9">%s</div>', $result );

	/**
	 * Filter featured video.
	 *
	 * @since 4.0.0
	 */
	return apply_filters( 'cherry_get_the_post_video', $result );
}

/**
 * Show featured audio.
 *
 * @since 4.0.0
 */
function cherry_the_post_audio() {
	echo cherry_get_the_post_audio();
}

/**
 * Get featured audio.
 * Returns first finded audio tag in page content.
 *
 * @since 4.0.0
 */
function cherry_get_the_post_audio() {
	/**
	 * Filter post format audio output to rewrite audio from child theme or plugins.
	 *
	 * @since  4.0.0
	 */
	$result = apply_filters( 'cherry_pre_get_post_audio', false );

	if ( false !== $result ) {
		return $result;
	}

	$content = get_the_content();
	$embeds  = get_media_embedded_in_content( apply_filters( 'the_content', $content ), array( 'audio' ) );

	if ( empty( $embeds ) ) {
		return;
	}

	if ( false == preg_match( '/<audio[^>]*>(.*?)<\/audio>/', $embeds[0], $matches ) ) {
		return;
	}

	/**
	 * Filter featured audio.
	 *
	 * @since 4.0.0
	 */
	return apply_filters( 'cherry_get_the_post_audio', $matches[0] );
}

/**
 * Display the post author avatar.
 *
 * @since  4.0.0
 * @param  array $args
 */
function cherry_the_post_avatar( $args ) {
	echo cherry_get_the_post_avatar( $args );
}


/**
 * Retrieve the post author avatar.
 *
 * @since  4.0.0
 * @param  array  $args
 * @return string $output
 */
function cherry_get_the_post_avatar( $args ) {

	// If avatars are enabled.
	if ( !get_option( 'show_avatars' ) ) {
		return false;
	}

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	/**
	 * Filter the default arguments used to display a author avatar.
	 *
	 * @since 4.0.0
	 * @param array  $args      Array of arguments.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type of the current post.
	 */
	$defaults = apply_filters( 'cherry_get_the_post_avatar_defaults', array(
		'size'            => '96',
		'container'       => 'figure',
		'container_class' => 'entry-avatar',
		'wrap'            => is_singular( $post_type ) ? '<%1$s class="%2$s">%5$s</%1$s>' : '<%1$s class="%2$s"><a href="%3$s" title="%4$s">%5$s</a></%1$s>',
	), $post_id, $post_type );

	$args = wp_parse_args( $args, $defaults );

	$nickname   = get_the_author_meta( 'nickname' );
	$avatar     = get_avatar( get_the_author_meta( 'user_email' ), $args['size'], '', esc_attr( $nickname ) );
	$title_attr = the_title_attribute( 'echo=0' );

	$output = sprintf( $args['wrap'],
		$args['container'],
		$args['container_class'],
		get_permalink( $post_id ),
		esc_attr( $title_attr ),
		$avatar
	);

	return $output;
}

/**
 * Gets the first URL from the content, even if it's not wrapped in an <a> tag.
 *
 * @since  4.0.0
 * @param  string $content
 * @return string
 */
function cherry_get_content_url( $content ) {

	// Catch links that are not wrapped in an '<a>' tag.
	preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', make_clickable( $content ), $matches );

	return !empty( $matches[1] ) ? esc_url_raw( $matches[1] ) : '';
}

/**
 * If did not find a URL, check the post content for one. If nothing is found, return the post permalink.
 *
 * @since  4.0.0
 * @param  object $post
 * @return string
 */
function cherry_get_post_format_url( $post = null ) {
	$post        = is_null( $post ) ? get_post() : $post;
	$content_url = cherry_get_content_url( $post->post_content );
	$url         = !empty( $content_url ) ? $content_url : get_permalink( $post->ID );

	return esc_url( $url );
}