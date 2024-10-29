<?php

/**
 * Calls the class on the post edit screen.
 */
function call_advert_add_metaboxes_post_page() {
    new advert_add_metaboxes_post_page();
}

if ( is_admin() && current_user_can('edit_others_posts') ) {
    add_action( 'load-post.php', 'call_advert_add_metaboxes_post_page' );
    add_action( 'load-post-new.php', 'call_advert_add_metaboxes_post_page' );
}

/** 
 * The Class.
 */
class advert_add_metaboxes_post_page {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            $post_types = array('post', 'page');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
		add_meta_box(
			'advert_turn_off_advertisement_post_page'
			,__( 'AdVert Advertisements', 'ADVERT_TEXTDOMAIN' )
			,array( $this, 'advert_post_page_metabox' )
			,$post_type
			,'side'
			,'high'
		);
            }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['advert_post_page_nonce'] ) )
			return $post_id;

		$nonce = $_POST['advert_post_page_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'advert_post_page_nonce_metabox' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['advert_turn_off_advertisement_post_page'] );

		// Update the meta field.
		update_post_meta( $post_id, 'advert_post_page_visible', $mydata );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function advert_post_page_metabox( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'advert_post_page_nonce_metabox', 'advert_post_page_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, 'advert_post_page_visible', true );

		// Display the form, using the current value.
		echo '<label for="advert_turn_off_advertisement_post_page">';
		_e( 'Turn off Advertisements for this post/page', 'myplugin_textdomain' );
		echo '</label><br />';
		echo '<p class=""><input type="checkbox" id="advert_turn_off_advertisement_post_page" name="advert_turn_off_advertisement_post_page"';
        echo ' value="1" ' . checked( 1, $value, false ) . ' /></p>';
        echo '<hr><p class="advert-tip"><span class="advert-sm-info">' . __('AdVert Tip: This turns off all advertisements for this page or post.', 'ADVERT_TEXTDOMAIN') .'</span></p>';

	}
}