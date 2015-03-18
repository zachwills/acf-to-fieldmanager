Advanced Custom Fields (ACF) to Fieldmanager - v0.1
==

ACF_To_Fieldmanager is a small helper / utility class with a goal of saving you time when you have to migrate from ACF to Fieldmanager. You might run into this pretty often if you are working on sites that you need to migrate to WordPress VIP.

If you run into any issues or have questions, open an issue here on GitHub! PR's are welcome!

*It's important to note this is still a work in progress. While it is lacking in features and error reporting, it still works and does speed things up.*

## Getting Started

Using ACF_To_Fieldmanager is easy- just format an associative array of arrays using the `field_name` from your ACF field as the key and passing through the supported paramaters.

### Requirements

This utility library, as you might have guessed, requires [WP_CLI](http://wp-cli.org/). The only piece of it that requires this is the output to the terminal, however. So if you wanted to use it without WP_CLI, you could just comment out the `WP_CLI::` lines that you see. If I get time in the future I might look into this more.

It also requires [Advanced Custom Fields](http://www.advancedcustomfields.com/) and [Fieldmanager](http://fieldmanager.org/), since they determine the format of the initial data & the final migrated data.

### Supported paramaters
* `new_name`
    * This is the new `meta_key` you are using to store these values.
* `value`
    *  `string|array|int|bool`
    * This is the value of your custom field
* `repeating`
    * `true/false`
    * This is for repeater fields. .
* `children`
    * `array`
    *  An array containing an array of the `field_names` of the repeater field.


## Examples

Here are some simple code samples demonstrating use. When I use this class, I use it in tandem with a custom WP_CLI migration command that is made for whatever project I'm working on (hence the `WP_CLI::` lines outputting information in the source).

## Migrating simple fields
````php
$post_id = 98;

$legacy_fields = array(
	'locations' => array(
		'new_name' => 'new_locations',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
	'hotels' => array(
		'new_name' => 'new_hotels',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
);

$field_migration = new ACF_To_Fieldmanager( $post_id );
$field_migration->migrate( $legacy_fields );
````


## Migrating repeating fields
````php
$post_id = 98;

$legacy_fields = array(
	'locations' => array(
		'new_name' => 'new_locations',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
	'hotels' => array(
		'new_name' => 'new_hotels',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
	'group_photos' => array(
		'new_name' => 'new_group_photos',
		'value' => get_post_meta( $post_id, 'group_photos', true ),
		'repeating' => true,
		'children' => array(
			'photo'
		)
	),
);

$field_migration = new ACF_To_Fieldmanager( $post_id );
$field_migration->migrate( $legacy_fields );
````

## Migrating repeating fields within repeating fields
````php
$post_id = 98;

$legacy_fields = array(
	'locations' => array(
		'new_name' => 'new_locations',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
	'hotels' => array(
		'new_name' => 'new_hotels',
		'value' => get_post_meta( $post_id, 'locations', true ),
	),
	'group_photos' => array(
		'new_name' => 'new_group_photos',
		'value' => get_post_meta( $post_id, 'group_photos', true ),
		'repeating' => true,
		'children' => array(
			'sub_field_repeater' => array(
			  'sub_field_name',
			  'sub_field_name_2'
			)
		)
	),
);

$field_migration = new ACF_To_Fieldmanager( $post_id );
$field_migration->migrate( $legacy_fields );
````

## Planned Updates
* Allow a callback function for each field for normalization purposes.
* Better error / success messaging

## Changelog
* v0.1 initial release
