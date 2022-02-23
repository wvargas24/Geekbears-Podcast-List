<?php
/**
* Plugin Name: Geekbears Podcast List
* Plugin URI: https://www.geekbears.com/
* Description: This is the geekbears Podcast plugin.
* Version: 2.0
* Author: Wuilly Vargas
* Author URI: http://instagram.com/wuilly.vargas
**/

/* ---------------------------------------------------------------------------
 * Function to load script and css files
 * --------------------------------------------------------------------------- */
function load_podcast_file(){
    wp_enqueue_style('podcast-style', plugin_dir_url( __FILE__ ) . '/css/style.css', array(), '1.2.3', 'all');
    wp_enqueue_script( 'podcast-script',  plugin_dir_url( __FILE__ ) . '/js/podcast-ajax.js', array('jquery')); // jQuery will be included automatically
    wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); // setting ajaxurl
}
add_action( 'wp_enqueue_scripts','load_podcast_file' );

/* ---------------------------------------------------------------------------
 * [wv_podcast_load_more_ajax]
 * --------------------------------------------------------------------------- */
if( ! function_exists( 'wv_podcast_load_more_ajax' ) ){
    function wv_podcast_load_more_ajax( $attr, $content = null )
    {
        ob_start();
        global $content_width, $ss_podcasting;		

		// Parse shortcode attributes
		$atts = shortcode_atts(
			array(
				'type'         => 'audio',
				'series'       => '',
				'order'        => 'ASC',
				'orderby'      => 'menu_order ID',
				'include'      => '',
				'exclude'      => '',
				'style'        => 'light',
				'player_style' => 'mini',
				'tracklist'    => true,
				'tracknumbers' => true,
				'images'       => true,
				'limit'        => - 1,
			),
			$params,
			'podcast_playlist'
		);

		// Set up query arguments for fetching podcast episodes
		$query_args = array(
			'post_status'         => 'publish',
			'post_type'           => 'podcast',
			'posts_per_page'      => 6,
			'order'               => $atts['order'],
			'orderby'             => $atts['orderby'],
			'ignore_sticky_posts' => true,
			'post__in'            => $atts['include'],
			'post__not_in'        => $atts['exclude'],
		);

		// Make sure to only fetch episodes that have a media file
		$query_args['meta_query'] = array(
			array(
				'key'     => 'audio_file',
				'compare' => '!=',
				'value'   => '',
			),
		);

		// Limit query to episodes in defined series only
		if ( $atts['series'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'series',
					'field'    => 'slug',
					'terms'    => $atts['series'],
				),
			);
		}

		// Allow dynamic filtering of query args
		$query_args = apply_filters( 'ssp_podcast_playlist_query_args', $query_args );

		// Fetch all episodes for display
		$episodes = get_posts( $query_args );

		if ( empty ( $episodes ) ) {
			return;
		}

		$outer          = 22; // default padding and border of wrapper
		$default_width  = 640;
		$default_height = 360;

		$theme_width  = empty( $content_width ) ? $default_width : ( $content_width - $outer );
		$theme_height = empty( $content_width ) ? $default_height : round( ( $default_height * $theme_width ) / $default_width );

		$data = array(
			'type'         => $atts['type'],
			// don't pass strings to JSON, will be truthy in JS
			'tracklist'    => wp_validate_boolean( $atts['tracklist'] ),
			'tracknumbers' => wp_validate_boolean( $atts['tracknumbers'] ),
			'images'       => wp_validate_boolean( $atts['images'] ),
			'artists'      => false,
		);

		$tracks = array();
		?>
		<div id="podcast-box">
			<div id="container-grid-podcast">
			<?php 
			foreach ( $episodes as $episode ) {

				$url = $ss_podcasting->get_enclosure( $episode->ID );
				if ( get_option( 'permalink_structure' ) ) {
					$url = $ss_podcasting->get_episode_download_link( $episode->ID );
					$url = str_replace( 'podcast-download', 'podcast-player', $url );
				}

				// Get episode file type
				$ftype = wp_check_filetype( $url, wp_get_mime_types() );

				if ( $episode->post_excerpt ) {
					$episode_excerpt = $episode->post_excerpt;
				} else {
					$episode_excerpt = $episode->post_title;
				}

				// Setup episode data for media player
				$track = array(
					'src'         => $url,
					'type'        => $ftype['type'],
					'caption'     => $episode->post_title,
					'title'       => $episode_excerpt,
					'description' => $episode->post_content,
				);

				// We don't need the ID3 meta data here, but still need to set an empty array
				$track['meta'] = array();

				// Set video dimensions for player
				if ( 'video' === $atts['type'] ) {
					$track['dimensions'] = array(
						'original' => compact( $default_width, $default_height ),
						'resized'  => array(
							'width'  => $theme_width,
							'height' => $theme_height,
						)
					);
				}

				// Get episode image
				if ( $atts['images'] ) {
					$thumb_id = get_post_thumbnail_id( $episode->ID );
					if ( ! empty( $thumb_id ) ) {
						list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'full' );
						$track['image'] = compact( 'src', 'width', 'height' );
						list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
						$track['thumb'] = compact( 'src', 'width', 'height' );
					} else {
						$track['image'] = '';
						$track['thumb'] = '';
					}
				}
				
				// Allow dynamic filtering of track data
				$track = apply_filters( 'ssp_podcast_playlist_track_data', $track, $episode );

				$tracks[] = $track;

				$link_media = $track['src'];

				$url = $ss_podcasting->get_enclosure( $episode->ID );

	            if ( get_option( 'permalink_structure' ) ) {
	                $url = $ss_podcasting->get_episode_download_link( $episode->ID );
	                $url = str_replace( 'podcast-download', 'podcast-player', $url );
	            }
	            
	            require 'partials/content-loop.php';  
			}
			?>
			</div>		
			<input type="hidden" id="paged" class="" value="1">
			<div class="text-center load_more_wrapper"><a href="#" class="load_more_item" id="load_more_button"><span>VIEW MORE</span></a></div>
		</div>
 		<?php
        return ob_get_clean();
    }
}
add_shortcode( 'wv_podcast_load_more_ajax', 'wv_podcast_load_more_ajax' );

function load_more_podcast() {
	global $content_width, $ss_podcasting;
    $paged = $_POST['paged'];

    $atts = shortcode_atts(
			array(
				'type'         => 'audio',
				'series'       => '',
				'order'        => 'ASC',
				'orderby'      => 'menu_order ID',
				'include'      => '',
				'exclude'      => '',
				'style'        => 'light',
				'player_style' => 'mini',
				'tracklist'    => true,
				'tracknumbers' => true,
				'images'       => true,
				'limit'        => - 1,
			),
			$params,
			'podcast_playlist'
		);

    // Set up query arguments for fetching podcast episodes
	$args = array(
		'post_status'         => 'publish',
		'post_type'           => 'podcast',
		'posts_per_page'      => 6,
		'order'               => $atts['order'],
		'orderby'             => $atts['orderby'],
		'ignore_sticky_posts' => true,
		'post__in'            => $atts['include'],
		'post__not_in'        => $atts['exclude'],
        'paged'               => $paged
	);

    // Make sure to only fetch episodes that have a media file
	$args['meta_query'] = array(
		array(
			'key'     => 'audio_file',
			'compare' => '!=',
			'value'   => '',
		),
	);

	// Limit query to episodes in defined series only
	if ( $atts['series'] ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'series',
				'field'    => 'slug',
				'terms'    => $atts['series'],
			),
		);
	}

    // Allow dynamic filtering of query args
	$args = apply_filters( 'ssp_podcast_playlist_query_args', $args );

	$outer          = 22; // default padding and border of wrapper
	$default_width  = 640;
	$default_height = 360;

	$theme_width  = empty( $content_width ) ? $default_width : ( $content_width - $outer );
	$theme_height = empty( $content_width ) ? $default_height : round( ( $default_height * $theme_width ) / $default_width );

	$data = array(
		'type'         => $atts['type'],
		// don't pass strings to JSON, will be truthy in JS
		'tracklist'    => wp_validate_boolean( $atts['tracklist'] ),
		'tracknumbers' => wp_validate_boolean( $atts['tracknumbers'] ),
		'images'       => wp_validate_boolean( $atts['images'] ),
		'artists'      => false,
	);

	$tracks = array();

	// Fetch all episodes for display
	$episodes = get_posts( $args );
	foreach ( $episodes as $episode ){
		$url = $ss_podcasting->get_enclosure( $episode->ID );
		if ( get_option( 'permalink_structure' ) ) {
			$url = $ss_podcasting->get_episode_download_link( $episode->ID );
			$url = str_replace( 'podcast-download', 'podcast-player', $url );
		}

		// Get episode file type
		$ftype = wp_check_filetype( $url, wp_get_mime_types() );

		if ( $episode->post_excerpt ) {
			$episode_excerpt = $episode->post_excerpt;
		} else {
			$episode_excerpt = $episode->post_title;
		}

		// Setup episode data for media player
		$track = array(
			'src'         => $url,
			'type'        => $ftype['type'],
			'caption'     => $episode->post_title,
			'title'       => $episode_excerpt,
			'description' => $episode->post_content,
		);

		// We don't need the ID3 meta data here, but still need to set an empty array
		$track['meta'] = array();

		// Get episode image
		if ( $atts['images'] ) {
			$thumb_id = get_post_thumbnail_id( $episode->ID );
			if ( ! empty( $thumb_id ) ) {
				list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'full' );
				$track['image'] = compact( 'src', 'width', 'height' );
				list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
				$track['thumb'] = compact( 'src', 'width', 'height' );
			} else {
				$track['image'] = '';
				$track['thumb'] = '';
			}
		}
		
		// Allow dynamic filtering of track data
		$track = apply_filters( 'ssp_podcast_playlist_track_data', $track, $episode );

		$tracks[] = $track;

		$link_media = $track['src'];

		$url = $ss_podcasting->get_enclosure( $episode->ID );

        if ( get_option( 'permalink_structure' ) ) {
            $url = $ss_podcasting->get_episode_download_link( $episode->ID );
            $url = str_replace( 'podcast-download', 'podcast-player', $url );
        }
		require 'partials/content-loop.php';  
	}

    /*$query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post(); 
            require 'partials/content-loop.php';  
        }
    }*/
    wp_reset_postdata();
    die(); 
}
add_action( 'wp_ajax_load_more_podcast', 'load_more_podcast' );
add_action( 'wp_ajax_nopriv_load_more_podcast', 'load_more_podcast' );

function playBar_podcast() {
	global $content_width, $ss_podcasting;
	$postId = $_POST['postId'];

	$url = $ss_podcasting->get_enclosure( $postId );
	if ( get_option( 'permalink_structure' ) ) {
		$url = $ss_podcasting->get_episode_download_link( $postId );
		$url = str_replace( 'podcast-download', 'podcast-player', $url );
	}
	
	$args = array(
	  'p'         => $postId, // ID of a page, post, or custom type
	  'post_type' => 'podcast'
	);
	$query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post(); 
            require 'partials/content-playbar.php';
        }
    }
	wp_reset_postdata();
    die(); 
}
add_action( 'wp_ajax_playBar_podcast', 'playBar_podcast' );
add_action( 'wp_ajax_nopriv_playBar_podcast', 'playBar_podcast' );
