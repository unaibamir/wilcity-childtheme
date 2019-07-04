<?php
/*if( is_admin() ) {
	return;
}

$post  = get_post(21583);

$t_time = get_the_time( __( 'Y/m/d g:i:s a' ), $post );
$m_time = $post->post_date;
$time   = get_post_time( 'G', true, $post );

$time_diff = time() - $time;

if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
	$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
} else {
	$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
}

$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );

dd($h_time);
*/
add_filter( 'mycred_setup_hooks', 'wilcity_custom_mycred_hooks', 99 );
function wilcity_custom_mycred_hooks( $hooks ) {

	$prefix_hook	= "wilcity_mycred_";
	$prefix_class 	= "WilCity_myCRED_";
	$prefix_title 	= "WilCity - ";

	$hooks[ $prefix_hook . 'featured_image_updated'] = array(
		'title'       => __( $prefix_title . ' Add Featured Image', 'textdomain' ),
		'description' => __( 'When user adds featured image on listing', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Featured_Image' )
	);

	$hooks[ $prefix_hook . 'logo_updated'] = array(
		'title'       => __( $prefix_title . ' Add Logo', 'textdomain' ),
		'description' => __( 'When user adds logo on listing', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Logo' )
	);

	$hooks[ $prefix_hook . 'description_updated'] = array(
		'title'       => __( $prefix_title . ' Add Description', 'textdomain' ),
		'description' => __( 'When user adds description on listing (at least 200 characters) ', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Description' )
	);

	$hooks[ $prefix_hook . 'contact_updated'] = array(
		'title'       => __( $prefix_title . ' Add Contact Info', 'textdomain' ),
		'description' => __( 'When user adds contact info on listing', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Contact_Info' )
	);

	$hooks[ $prefix_hook . 'photos_updated_each'] = array(
		'title'       => __( $prefix_title . ' Add Gallery images', 'textdomain' ),
		'description' => __( 'When user adds gallery images on listing, each photo will be rewarded', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Photo' )
	);

	$hooks[ $prefix_hook . 'videos_updated_each'] = array(
		'title'       => __( $prefix_title . ' Add Videos', 'textdomain' ),
		'description' => __( 'When user adds videos on listing, each video will be rewarded', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Video' )
	);

	$hooks[ $prefix_hook . 'posts_updated_each'] = array(
		'title'       => __( $prefix_title . ' Add Posts Each', 'textdomain' ),
		'description' => __( 'When user adds posts on listing, each post will be rewarded', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Posts' )
	);

	$hooks[ $prefix_hook . 'premium_subscription'] = array(
		'title'       => __( $prefix_title . ' Add Premium Subscription', 'textdomain' ),
		'description' => __( 'When user adds Premium Subscription on listing', 'textdomain' ),
		'callback'    => array( $prefix_class . 'Premium_Subscription' )
	);


	return $hooks;
}


add_filter( "mycred_all_references", "wilcity_custom_mycred_references", 99 );
function wilcity_custom_mycred_references( $references ) {

	$prefix_hook	= "wilcity_mycred_";
	$prefix_title 	= "WilCity - ";

	$references[ $prefix_hook . "featured_image_updated"] 	= __( $prefix_title . "Featured Image Added", "textdomain" );
	$references[ $prefix_hook . "logo_updated"] 			= __( $prefix_title . "Logo Added", "textdomain" );
	$references[ $prefix_hook . "description_updated"] 		= __( $prefix_title . "Description Added", "textdomain" );
	$references[ $prefix_hook . "contact_updated"] 			= __( $prefix_title . "Contact Info Added", "textdomain" );
	$references[ $prefix_hook . "photos_updated_each"] 		= __( $prefix_title . "Gallery images Added - Each", "textdomain" );
	$references[ $prefix_hook . "videos_updated_each"] 		= __( $prefix_title . "Videos Added - Each", "textdomain" );
	$references[ $prefix_hook . "posts_updated_each"] 		= __( $prefix_title . "Posts Added - Each", "textdomain" );
	$references[ $prefix_hook . "premium_subscription"] 	= __( $prefix_title . "Premium Subscription Added - Each", "textdomain" );

	return $references;
}


class WilCity_myCRED_Featured_Image extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_featured_image_updated',
			'defaults' => array(
				'creds'   => 50,
				'limit'   => '1/d',
				'log'     => '%plural% for adding featured image on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_featured_image' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_featured_image( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "_thumbnail_id" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		//if ( is_admin() ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_featured_image_updated', $user_id ) ) {
			if( ! $this->has_entry( 'wilcity_mycred_featured_image_updated', $post_id, $user_id, "add", $this->mycred_type ) ) {
				$this->core->add_creds(
					'wilcity_mycred_featured_image_updated',
					$user_id,
					$this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					'add',
					$this->mycred_type
				);
			}
		}
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}

class WilCity_myCRED_Logo extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_logo_updated',
			'defaults' => array(
				'creds'   => 50,
				'limit'   => '1/d',
				'log'     => '%plural% for adding logo on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_logo_image' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_logo_image( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_logo" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		//if ( is_admin() ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_logo_updated', $user_id ) ) {
			if( ! $this->has_entry( 'wilcity_mycred_logo_updated', $post_id, $user_id, "add", $this->mycred_type ) ) {
				$this->core->add_creds(
					'wilcity_mycred_logo_updated',
					$user_id,
					$this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					'add',
					$this->mycred_type
				);
			}
		}
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}

class WilCity_myCRED_Description extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_description_updated',
			'defaults' => array(
				'creds'   => 30,
				'limit'   => '1/d',
				'log'     => '%plural% for adding logo on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'save_post_listing',  array( $this, 'listing_updated_content_desc' ), 99, 3 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_content_desc( $post_id, $post, $update ) {

		if( !$update ) {
			return;
		}

		if( $post->post_status != 'publish' ) {
			return;
		}

		$passedCsrf = check_ajax_referer( 'wilcity-submit-listing', 'wilcityAddListingCsrf', false);

		// If this is just a revision, don't send the email.
		if ( !$passedCsrf ) {
			return;
        }

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		//remove_action( 'save_post', array( $this, 'listing_updated_content_desc' ), 99 );

		if ( strlen( $post->post_content ) < 200 ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_description_updated', $user_id ) ) {
			if( ! $this->has_entry( 'wilcity_mycred_description_updated', $post_id, $user_id, "add", $this->mycred_type ) ) {
				$this->core->add_creds(
					'wilcity_mycred_description_updated',
					$user_id,
					$this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					'add',
					$this->mycred_type
				);
			}
		}

		//add_action( 'save_post_listing',  array( $this, 'listing_updated_content_desc' ), 99, 3 );
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}

class WilCity_myCRED_Contact_Info extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_contact_updated',
			'defaults' => array(
				'creds'   => 20,
				'limit'   => '1/d',
				'log'     => '%plural% for adding contact info on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_logo_image' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_logo_image( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		$contact_keys = array(
			"wilcity_email",
			"wilcity_website",
			"wilcity_social_networks"
		);

		if( !in_array($meta_key, $contact_keys ) ) {
			return;
		}

		if ( empty( $meta_value ) ) return;

		//if ( is_admin() ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_contact_updated', $user_id ) ) {
			if( ! $this->has_entry( 'wilcity_mycred_contact_updated', $post_id, $user_id, "add", $this->mycred_type ) ) {
				$this->core->add_creds(
					'wilcity_mycred_contact_updated',
					$user_id,
					$this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					'add',
					$this->mycred_type
				);
			}
		}
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}

class WilCity_myCRED_Photo extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_photos_updated_each',
			'defaults' => array(
				'creds'   => 10,
				'limit'   => '1/d',
				'log'     => '%plural% for adding gallery image on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_gallery_image' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_gallery_image( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_gallery" != $meta_key ) return;

		$images  = maybe_unserialize( $meta_value );
		
		if ( empty($images ) || count($images) < 1 || !is_array($images) ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_photos_updated_each', $user_id ) ) {
			foreach ( $images as $image_id => $image_url) {
				if( ! $this->has_entry( 'wilcity_mycred_photos_updated_each', $post_id, $user_id, $image_id, $this->mycred_type ) ) {
					$this->core->add_creds(
						'wilcity_mycred_photos_updated_each',
						$user_id,
						$this->prefs['creds'],
						$this->prefs['log'],
						$post_id,
						$image_id,
						$this->mycred_type
					);
				}
			}
		}
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}

class WilCity_myCRED_Video extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_videos_updated_each',
			'defaults' => array(
				'creds'   => 20,
				'limit'   => '1/d',
				'log'     => '%plural% for adding gallery image on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_video_item' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_video_item( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_video_srcs" != $meta_key ) return;

		$videos  = maybe_unserialize( $meta_value );
		
		if ( empty($videos ) || count($videos) < 1 || !is_array($videos) ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_videos_updated_each', $user_id ) ) {
			foreach ( $videos as $video_id => $video_url) {
				if( ! $this->has_entry( 'wilcity_mycred_videos_updated_each', $post_id, $user_id, $video_id, $this->mycred_type ) ) {
					$this->core->add_creds(
						'wilcity_mycred_videos_updated_each',
						$user_id,
						$this->prefs['creds'],
						$this->prefs['log'],
						$post_id,
						$video_id,
						$this->mycred_type
					);
				}
			}
		}

	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}


class WilCity_myCRED_Posts extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_posts_updated_each',
			'defaults' => array(
				'creds'   => 20,
				'limit'   => '1/d',
				'log'     => '%plural% for adding posts on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_posts' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_posts( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_oldPlanID" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		$plan_title = get_the_title( $meta_value );
		if( $plan_title != "Premium" ) {
			return;
		}

		//if ( is_admin() ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_posts_updated_each', $user_id ) ) {
			if( ! $this->has_entry( 'wilcity_mycred_posts_updated_each', $post_id, $user_id, "add", $this->mycred_type ) ) {
				$this->core->add_creds(
					'wilcity_mycred_posts_updated_each',
					$user_id,
					$this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					'add',
					$this->mycred_type
				);
			}
		}
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}




class WilCity_myCRED_Premium_Subscription extends myCRED_Hook {

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => 'wilcity_mycred_premium_subscription_updated',
			'defaults' => array(
				'creds'   => 50,
				'limit'   => '1/d',
				'log'     => '%plural% for adding premium subscription on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		//add_action( 'added_post_meta',  array( $this, 'listing_updated_premium_subscription' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_premium_subscription( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_logo" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		//if ( is_admin() ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		if ( ! $this->over_hook_limit( '', 'wilcity_mycred_premium_subscription_updated', $user_id ) )
			$this->core->add_creds(
				'wilcity_mycred_premium_subscription_updated',
				$user_id,
				$this->prefs['creds'],
				$this->prefs['log'],
				$post_id,
				'add',
				$this->mycred_type
			);
	}

	/**
	 * Preference for Login Hook
	 * @since 0.1
	 * @version 1.2
	 */
	public function preferences() {

		$prefs = $this->prefs;

		?>
		<div class="hook-instance">
			<div class="row">
				<div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'creds' ); ?>"><?php echo $this->core->plural(); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'creds' ); ?>" id="<?php echo $this->field_id( 'creds' ); ?>" value="<?php echo $this->core->number( $prefs['creds'] ); ?>" class="form-control" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'limit' ); ?>"><?php _e( 'Limit', 'mycred' ); ?></label>
						<?php echo $this->hook_limit_setting( $this->field_name( 'limit' ), $this->field_id( 'limit' ), $prefs['limit'] ); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e( 'Log Template', 'mycred' ); ?></label>
						<input type="text" name="<?php echo $this->field_name( 'log' ); ?>" id="<?php echo $this->field_id( 'log' ); ?>" placeholder="<?php _e( 'required', 'mycred' ); ?>" value="<?php echo esc_attr( $prefs['log'] ); ?>" class="form-control" />
						<span class="description"><?php echo $this->available_template_tags( array( 'general' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 * Sanitise Preferences
	 * @since 1.6
	 * @version 1.0
	 */
	function sanitise_preferences( $data ) {

		if ( isset( $data['limit'] ) && isset( $data['limit_by'] ) ) {
			$limit = sanitize_text_field( $data['limit'] );
			if ( $limit == '' ) $limit = 0;
			$data['limit'] = $limit . '/' . $data['limit_by'];
			unset( $data['limit_by'] );
		}

		return $data;

	}
}







function wilcity_mycred_check_entry( $action = '', $reference = '', $user_id = '', $data = '', $type = '' ) {
	global $wpdb, $mycred_log_table;

	$timestamp = current_time( 'timestamp' );
	$beginOfDay = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($timestamp)->format('Y-m-d 00:00:00'))->getTimestamp();
	$endOfDay = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($timestamp)->format('Y-m-d 23:59:59'))->getTimestamp();


	$sql = "SELECT id FROM {$mycred_log_table} WHERE ref = %s AND user_id = %d AND LIKE %s AND ctype = %s;";
	$wpdb->get_results( $wpdb->prepare( $sql, $action, $user_id, $this->mycred_type ) );
	if ( $wpdb->num_rows > 0 ) return true;

	return false;
}