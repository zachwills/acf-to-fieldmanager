<?php
/**
 * ACF_TO_Fieldmanager
 *
 * @author Zach Wills (@zachwills)
 */
class ACF_To_Fieldmanager {
	private $post_id;

	/**
	 * Set the $post_id variable
	 */
	function __construct( $post_id ) {
		if( ! empty( $post_id ) ) {
			$this->post_id = $post_id;
		}
	}

	/**
	 * This function takes a properly formatted array of legacy data and
	 * migrates it to fieldmanager
	 *
	 * @param $legacy_fields array
	 *  array(
	 * 	'divider_text' => array(
	 * 		'new_name' => 'new_name_here',
	 * 		'value' => get_post_meta( $post_id, 'divider_text', true ),
	 * 	),
	 * 	'canyon_photos' => array(
	 *		'new_name' => 'new_name',
	 *		'value' => get_post_meta( $post_id, 'canyon_photos', true ),
	 *		'repeating' => true,
	 *		'children' => array(
	 *			'photo'
	 *		)
	 *	),
	 * );
	 * @param $delete Should we delete the old post meta info
	 * @return WP_CLI output
	 */
	public function migrate( $legacy_fields, $delete = false ) {
		if( ! $legacy_fields ) {
			return false;
		}

		foreach( $legacy_fields as $legacy_name => $legacy_data ) {
			// handle repeating field data differently than single data
			if( ! empty( $legacy_data['repeating'] ) && ! empty( $legacy_data['children'] ) ) {
				$repeating_field_data = $this->handle_repeating_fields( $legacy_name, $legacy_data );
				$migration = update_post_meta( $this->post_id, $legacy_data['new_name'], $repeating_field_data );
			} else {
				$migration = update_post_meta( $this->post_id, $legacy_data['new_name'], $legacy_data['value'] );
				if( true === $delete ) {
					delete_post_meta( $this->post_id, $legacy_name );
				}
			}

			if( ! empty( $migration ) ) {
				WP_CLI::success( 'Success: `' . $legacy_name . '`' );
			} else {
				$something_failed = true;
				WP_CLI::warning( 'Failed: `' . $legacy_name  . '`' );
			}
		}

		if( ! empty( $something_failed ) ) {
			WP_CLI::warning( 'Note: Failure messages can be triggered if the data you are trying to update to equals the data in the database.' );
		}
	}

	/**
	 * Handles dealing with repeating fields
	 *
	 * @param  string $legacy_name    The name of the ACF field containing
	 *                                repeating data
	 * @param  array  $legacy_data    The repeating field data from ACF
	 * @return array  $repeating_data The data formatted for fieldmanager
	 */
	public function handle_repeating_fields( $legacy_name, $legacy_data, $delete = false ) {
		if( ! isset( $legacy_data['children'] ) ) {
			return false;
		}

		// create an empty array to fill up with data
		$repeating_data = array();

		foreach( $legacy_data['children'] as $k => $child ) {
			/**
			 * To determine if we are dealing with a sub field of a repeater ALSO
			 * being a repeater, we check the key of the array.
			 *
			 * If it's a nested repeater:
			 * $legacy_data['children'] = array(
			 * 	'sub_field' => array(
			 * 		'2 repeating fields deep'
			 * 	)
			 * )
			 *
			 * If it's a normal field:
			 * $legacy_data['children'] = array(
			 * 	'sub_field'
			 * )
			 */
			if( 'string' !== gettype( $k ) ) {
				$child_name = $child;
			} elseif( 'integer' !== gettype( $k ) ) {
				return false;
			}

			// Get data from ACF repeating field
			for( $i = 0; $i < $legacy_data['value']; $i++ ) {
				// e.g. 'canyon_photos_1_photo' (ACF stores repeating data this way)
				$repeating_field_name = $legacy_name . '_' . $i . '_' . $k;

				// If this sub repeating field is also a repeating field
				if( isset( $child['children'] ) ) {
					// Format the data for this child
					$child_legacy_data = array (
					  'value' => get_post_meta( $this->post_id, $repeating_field_name, true ),
					  'repeating' => true,
					  'children' => array( 'item' ),
					);

					/**
					 * We are are a repeating field that is a sub field of a repeating
					 * field (say that 5 times fast). So call this function on itself to
					 * handle formatting the data for us.
					 */
					$repeating_data[ $i ][ $k ] = $this->handle_repeating_fields( $repeating_field_name, $child_legacy_data );
				} else {
					// This sub field is not repeating, so we can grab the data right now
					$repeating_data[ $i ][ $k ] = get_post_meta( $this->post_id, $repeating_field_name, true );
				}

				if( true === $delete ) {
					delete_post_meta( $post_id, $repeating_field_name );
				}
			}
		}

		return $repeating_data;
	}

}
