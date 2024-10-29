<?php
/**
* AdVert copy of Post related Meta Boxes
*
* Display post submit form field
*
* @wp since 2.7.0 
* @advert since 1.0
*
* @param object $post
*/

function advert_post_submit_meta_box($post, $args = array() ) {
	global $action;

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);

    if ( wp_check_post_lock( $post->ID ) ) {
		$locked[] = $post->ID;
		return;
	}

    if(!current_user_can('publish_adverts') && current_user_can('edit_adverts') && $post->post_status == 'advert-archive'){
        return;   
    }

    ?>
    <div class="submitbox" id="submitpost">

    <?php if($can_publish) { ?>
    <div id="advert-admin-url"><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=advert_category&post_type=advert-banner')); ?>">URL</a></div>
    <?php } ?>

    <div id="minor-publishing">

    <?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
    <div style="display:none;">
    <?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
    </div>


    <div id="misc-publishing-actions">

    <?php if( !current_user_can('publish_adverts') && current_user_can('edit_adverts') ){echo '<div class="advert-dummy-div-238378"></div>';} ?>

    <div class="misc-pub-section misc-pub-post-status"><label for="post_status"><?php _e('Status:') ?></label>
    <span id="post-status-display">
    <?php
    switch ( $post->post_status ) {
	    case 'private':
		    _e('Privately Published');
		    break;
	    case 'publish':
		    _e('Published');
		    break;
	    case 'future':
		    _e('Scheduled');
		    break;
	    case 'pending':
		    _e('Pending Review');
		    break;
	    case 'archive':
                if ( $can_publish ){
		    _e( 'Archived', 'ADVERT_TEXTDOMAIN' );
                }
		    break;
	    case 'draft':
	    case 'auto-draft':
		    _e('Draft');
		    break;
    }
    ?>
    </span>
    <?php if ( 'publish' == $post->post_status || $can_publish ) { ?>
    <a href="#post_status" class="edit-post-status hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span></a>

    <div id="post-status-select" class="hide-if-js">
    <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
    <select name='post_status' id='post_status'>
    <?php if ( 'publish' == $post->post_status ) : ?>
    <option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e('Published') ?></option>
    <?php elseif ( 'private' == $post->post_status ) : ?>
    <option<?php selected( $post->post_status, 'private' ); ?> value='publish'><?php _e('Privately Published') ?></option>
    <?php elseif ( 'future' == $post->post_status ) : ?>
    <option<?php selected( $post->post_status, 'future' ); ?> value='future'><?php _e('Scheduled') ?></option>
    <?php endif; ?>
    <option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php _e('Pending Review') ?></option>
    <?php if ( $can_publish ){ ?>
    <option<?php selected( $post->post_status, 'advert-archive' ); ?> value='archive'><?php _e( 'Archive', 'ADVERT_TEXTDOMAIN' ) ?></option>
    <?php } ?>
    <?php if ( 'auto-draft' == $post->post_status ) : ?>
    <option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php _e('Draft') ?></option>
    <?php else : ?>
    <option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
    <?php endif; ?>
    </select>
        <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
        <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
    </div>

    <?php } ?>
    </div><!-- .misc-pub-section -->

    <div class="misc-pub-section misc-pub-visibility" id="visibility">
    <?php _e('Visibility:'); ?> <span id="post-visibility-display"><?php

	    $visibility = 'public';
	    $visibility_trans = __('Public');

    echo esc_html( $visibility_trans ); ?></span>


    </div><!-- .misc-pub-section -->

    <?php
    /* translators: Publish box date format, see http://php.net/date */
    $datef = __( 'M j, Y @ G:i' );
    if ( 0 != $post->ID ) {
	    if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
		    $stamp = __('Scheduled for: <b>%1$s</b>');
	    } else if ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
		    $stamp = __('Published on: <b>%1$s</b>');
	    } else if ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
		    $stamp = __('Publish <b>immediately</b>');
	    } else if ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
		    $stamp = __('Schedule for: <b>%1$s</b>');
	    } else { // draft, 1 or more saves, date specified
		    $stamp = __('Publish on: <b>%1$s</b>');
	    }
	    $date = date_i18n( $datef, strtotime( $post->post_date ) );
    } else { // draft (no saves, and thus no date specified)
	    $stamp = __('Publish <b>immediately</b>');
	    $date = date_i18n( $datef, strtotime( current_time('mysql') ) );
    }

    if ( ! empty( $args['args']['revisions_count'] ) ) :
	    $revisions_to_keep = wp_revisions_to_keep( $post );
    ?>
    <div class="misc-pub-section misc-pub-revisions">
    <?php
	    if ( $revisions_to_keep > 0 && $revisions_to_keep <= $args['args']['revisions_count'] ) {
		    echo '<span title="' . esc_attr( sprintf( __( 'Your site is configured to keep only the last %s revisions.' ),
			    number_format_i18n( $revisions_to_keep ) ) ) . '">';
		    printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '+</b>' );
		    echo '</span>';
	    } else {
		    printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '</b>' );
	    }
    ?>
	    <a class="hide-if-no-js" href="<?php echo esc_url( get_edit_post_link( $args['args']['revision_id'] ) ); ?>"><span aria-hidden="true"><?php _ex( 'Browse', 'revisions' ); ?></span> <span class="screen-reader-text"><?php _e( 'Browse revisions' ); ?></span></a>
    </div>
    <?php endif;

    if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
    <div class="misc-pub-section curtime misc-pub-curtime">
	    <span id="timestamp">
	    <?php printf($stamp, $date); ?></span>
	    <a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit date and time' ); ?></span></a>
	    <div id="timestampdiv" class="hide-if-js"><?php touch_time(($action == 'edit'), 1); ?></div>
    </div><?php // /misc-pub-section ?>
    <?php endif; ?>

    <?php
    /**
        * Fires after the post time/date setting in the Publish meta box.
        *
        * @since 2.9.0
        */
    do_action( 'post_submitbox_misc_actions' );
    ?>
    </div>
    <div class="clear"></div>
    </div>

    <div id="major-publishing-actions">
    <?php
    /**
        * Fires at the beginning of the publishing actions section of the Publish meta box.
        *
        * @since 2.7.0
        */
    do_action( 'post_submitbox_start' );
    ?>
    <div id="delete-action" class="advert-appended-for-draft">
    <?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status && 'advert-archive' != $post->post_status ) { ?>
    <input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
    <?php } ?>
    </div>

    <div id="publishing-action">
    <span class="spinner"></span>
    <?php
    if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	    if ( $can_publish ) :
		    if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
		    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
		    <?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
    <?php	else : ?>
		    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		    <?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
    <?php	endif;
	    else : ?>
		    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
		    <?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
    <?php
	    endif;
    } else { ?>
		    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		    <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
    <?php
    } ?>
    </div>
    <div class="clear"></div>
    </div>
    </div>

    <?php
}