<?php

/**
 * Class Stellar_Places_Nearby_Shortcode
 */
class Stellar_Places_Nearby_Shortcode {

	/**
	 * The shortcode name.
	 *
	 * @var string
	 */
	const NAME = 'stellar_places_nearby';

	/**
	 * Shortcode callback
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function shortcode( $atts ) {

		$atts = shortcode_atts(
			array(
				// Primary location from which to fetch nearby locations
				'place'       => 0, // Should be the post ID of a nearby location
				'distance'    => 25, // How far should we look for nearby locations?
				'units'       => 'miles', // Can be miles or kilometers (mi or km abbreviations will word also).
				// HTML Attributes
				'id'          => '',
				'class'       => '',
				'width'       => '100%',
				'height'      => '400px',
				// WordPress Specific
				'post_id'     => '',
				'post_type'   => '',
				'taxonomy'    => Stellar_Places_Location_Category::TAXONOMY,
				'term'        => '',
				'category'    => '', // Alias for term
				// Google Maps
				'maptypeid'   => 'ROADMAP',
				'scrollwheel' => 'false',
				'zoom'        => 15,
				'maxzoom'     => null,
				'minzoom'     => null,
				// Other
				'infowindows' => 'true',
			),
			$atts
		);

		if ( ! $atts['place'] && current_user_can( 'manage_options' ) ) {
			return '<p style="color:red;">Please provide a location. This should be the post ID of a place. Example: [stellar_places_nearby location="12"]</p>';
		}

		$post  = get_post( $atts['place'] );
		$place = Stellar_Places::get_place_object( $post );

		$width = $atts['width'];
		if ( is_numeric( $width ) ) {
			$width = "{$width}px";
		}

		$height = $atts['height'];
		if ( is_numeric( $height ) ) {
			$height = "{$height}px";
		}

		$query_args = array(
			'post__not_in' => array( $post->ID ),
			'geo_query'    => array(
				'lat'      => $place->latitude,
				'lng'      => $place->longitude,
				'distance' => 1,
			),
		);

		// Post ID
		if ( ! empty( $atts['post_id'] ) ) {
			$query_args['p'] = $atts['post_id'];
		}

		// Post Type
		if ( ! empty( $atts['post_type'] ) ) {
			$query_args['post_type'] = $atts['post_type'];
		}

		// Category (alias for term)
		if ( ! empty( $atts['category'] ) && empty( $atts['term'] ) ) {
			$atts['term'] = $atts['category'];
		}

		// Taxonomy
		if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['term'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $atts['taxonomy'],
					'field'    => 'slug',
					'terms'    => $atts['term'],
				),
			);
		}

		$query = new Stellar_Places_Query( $query_args );

		$map = new Stellar_Places_Google_Map( $place );
		foreach ( $query->posts as $p ) {
			$nearby       = Stellar_Places::get_place_object( $p );
			$nearby->icon = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
			$map->add_location( $nearby );
		}
		$map->autoCenter = false;

		$map->infoWindows = filter_var( $atts['infowindows'], FILTER_VALIDATE_BOOLEAN );

		// Set HTML attributes
		$map->id     = $atts['id'];
		$map->class  = $atts['class'];
		$map->width  = $width;
		$map->height = $height;

		// Set map center coordinates
		$map->latitude  = $place->latitude;
		$map->longitude = $place->longitude;

		// Set Google map options
		$map->mapOptions['mapTypeId']   = strtoupper( $atts['maptypeid'] );
		$map->mapOptions['scrollwheel'] = filter_var( $atts['scrollwheel'], FILTER_VALIDATE_BOOLEAN );
		$map->mapOptions['zoom']        = is_null( $atts['zoom'] ) ? null : absint( $atts['zoom'] );

		// If zoom is set, disable auto zoom functionality
		if ( ! is_null( $atts['zoom'] ) ) {
			$map->autoZoom = false;
		}

		if ( ! is_null( $atts['maxzoom'] ) ) {
			$map->mapOptions['maxZoom'] = absint( $atts['maxzoom'] );
		}

		if ( ! is_null( $atts['minzoom'] ) ) {
			$map->mapOptions['minZoom'] = absint( $atts['minzoom'] );
		}

		return $map->get_html();
	}

}
