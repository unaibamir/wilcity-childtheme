<?php

add_action( "init", function(){
	global $wpdb, $mycred_log_table;
	$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref ='wilcity_mycred_photos_updated_each' AND ref_id = 21584 ");
	if( $logs ) {
		$total_creds = wp_list_pluck( $logs, "creds" );
		$total_creds = array_sum($total_creds);

		$log_ids = wp_list_pluck( $logs, "id" );

		//dd( $log_ids );
	}
	//dd( wp_list_pluck( $logs, "creds" ) );



	/*$post_id = 21584;

	$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref_id = 21584 ");

	if( $logs ) {
		$total_creds = wp_list_pluck( $logs, "creds" );
		$total_creds = array_sum($total_creds);

		dd($total_creds);
	}
	dd($logs);
*/
});

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


//add_filter( "mycred_all_references", "wilcity_custom_mycred_references", 99 );
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
		
		add_action( 'added_post_meta',  	array( $this, 'listing_updated_featured_image_new' ), 99, 4 );
		add_action( 'updated_postmeta',  	array( $this, 'listing_updated_featured_image_update' ), 99, 4 );
		add_action( 'delete_post_meta',  	array( $this, 'listing_updated_featured_image_deleted' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_featured_image_new( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "_thumbnail_id" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		$this->core->add_creds(
			'wilcity_mycred_featured_image_updated',
			$user_id,
			$this->prefs['creds'],
			$this->prefs['log'],
			$post_id,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_featured_image_update( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "_thumbnail_id" != $meta_key ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		if ( empty( $meta_value ) ) {
			$creds = -1 * $this->prefs['creds'];
		} else {
			$creds = $this->prefs['creds'];
		}
		
		$this->core->add_creds(
			'wilcity_mycred_featured_image_updated',
			$user_id,
			$creds,
			$this->prefs['log'],
			$post_id,
			'',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_featured_image_deleted( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "_thumbnail_id" != $meta_key ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		$creds = -1 * $this->prefs['creds'];
		
		$this->core->add_creds(
			'wilcity_mycred_featured_image_removed',
			$user_id,
			$creds,
			'wilcity_mycred_featured_image_removed',
			$post_id,
			'',
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
		
		add_action( 'added_post_meta',  	array( $this, 'listing_updated_logo_image_new' ), 99, 4 );
		add_action( 'updated_postmeta',  	array( $this, 'listing_updated_logo_image_updated' ), 99, 4 );
		add_action( 'delete_post_meta',  	array( $this, 'listing_updated_logo_image_deleted' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_logo_image_new( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_logo" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;


		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		$this->core->add_creds(
			'wilcity_mycred_logo_added',
			$user_id,
			$this->prefs['creds'],
			$this->prefs['log'],
			$post_id,
			'add',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_logo_image_updated( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_logo" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;


		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
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

	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_logo_image_deleted( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating featured image only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_logo" != $meta_key ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		$creds = -1 * $this->prefs['creds'];
		
		$this->core->add_creds(
			'wilcity_mycred_logo_updated_removed',
			$user_id,
			$creds,
			'wilcity_mycred_logo_updated_removed',
			$post_id,
			'',
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
		$this->core->add_creds(
			'wilcity_mycred_description_updated',
			$user_id,
			$this->prefs['creds'],
			$this->prefs['log'],
			$post_id,
			'add',
			$this->mycred_type
		);

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
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_contact_info_new' ), 99, 4 );
		add_action( 'updated_postmeta',  array( $this, 'listing_updated_contact_info_updated' ), 99, 4 );
		add_action( 'delete_post_meta',  array( $this, 'listing_updated_contact_info_deleted' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_contact_info_new( $meta_id, $post_id, $meta_key, $meta_value ) {

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
		
		// Limit & Execute
		if ( $this->core->exclude_user( $user_id ) ) return;

		if ( empty( $meta_value ) ) {
			$creds = -1 * $this->prefs['creds'];
		} else {
			$creds = $this->prefs['creds'];
		}

		
		// Limit & Execute
		$this->core->add_creds(
			'wilcity_mycred_contact_updated',
			$user_id,
			$creds,
			$this->prefs['log'],
			$post_id,
			'contact_info_added',
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_contact_info_updated( $meta_id, $post_id, $meta_key, $meta_value ) {

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

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		if ( empty( $meta_value ) ) {
			$creds = -1 * $this->prefs['creds'];
		} else {
			$creds = $this->prefs['creds'];
		}

		
		// Limit & Execute
		$this->core->add_creds(
			'wilcity_mycred_contact_updated - '.$meta_key,
			$user_id,
			$creds,
			$this->prefs['log'],
			$post_id,
			'contact_info_updated - '.$meta_key,
			$this->mycred_type
		);
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_contact_info_deleted( $meta_id, $post_id, $meta_key, $meta_value ) {

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

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		$creds = -1 * $this->prefs['creds'];

		
		// Limit & Execute
		$this->core->add_creds(
			'wilcity_mycred_contact_removed - '.$meta_key,
			$user_id,
			$creds,
			'contact_info_removed - '.$meta_key,
			$post_id,
			'contact_info_removed - '.$meta_key,
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
		
		add_action( 'added_post_meta',  	array( $this, 'listing_updated_gallery_image' ), 99, 4 );
		add_action( 'updated_postmeta',  	array( $this, 'listing_updated_gallery_image' ), 99, 4 );
		add_action( 'delete_post_meta',  	array( $this, 'listing_updated_gallery_image_deleted' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_gallery_image( $meta_id, $post_id, $meta_key, $meta_value ) {

		global $wpdb, $mycred_log_table;

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_gallery" != $meta_key ) return;

		$images  = maybe_unserialize( $meta_value );

		$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref ='wilcity_mycred_photos_updated_each' AND ref_id = {$post_id} ");
		
		if( $logs ) {
			$total_creds 			= wp_list_pluck( $logs, "creds" );
			$total_creds 			= array_sum($total_creds);
			$total_creds_remove 	= -1 * $total_creds;
			$log_ids 				= wp_list_pluck( $logs, "id" );
			$log_ids 				= implode(",", $log_ids);

			$wpdb->query("DELETE FROM {$mycred_log_table} WHERE id IN({$log_ids}) "); 

			$this->core->update_users_balance( (int) $user_id, $total_creds_remove, $this->mycred_type );

			// Update total balance (if enabled)
			if ( MYCRED_ENABLE_TOTAL_BALANCE && MYCRED_ENABLE_LOGGING && ( $total_creds_remove > 0 || ( $total_creds_remove < 0 && "wilcity_mycred_photos_updated_each" == 'manual' ) ) ) {
				$this->update_users_total_balance( (int) $user_id, $total_creds_remove, $this->mycred_type );
			}
		}

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		foreach ( $images as $image_id => $image_url) {
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

	public function listing_updated_gallery_image_deleted($meta_id, $post_id, $meta_key, $meta_value) {
		global $wpdb, $mycred_log_table;

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_gallery" != $meta_key ) return;


		$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref ='wilcity_mycred_photos_updated_each' AND ref_id = {$post_id} ");
		
		if( $logs ) {
			$total_creds 			= wp_list_pluck( $logs, "creds" );
			$total_creds 			= array_sum($total_creds);
			$total_creds_remove 	= -1 * $total_creds;
			$log_ids 				= wp_list_pluck( $logs, "id" );
			$log_ids 				= implode(",", $log_ids);


			foreach ( $logs as $key => $log ) {
				$this->core->add_creds(
					'wilcity_mycred_photos_removed_each',
					$user_id,
					-1 * $this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					"",
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
		add_action( 'updated_postmeta',  array( $this, 'listing_updated_video_item' ), 99, 4 );
		add_action( 'delete_post_meta',  	array( $this, 'listing_updated_video_item_deleted' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_video_item( $meta_id, $post_id, $meta_key, $meta_value ) {

		global $wpdb, $mycred_log_table;

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_video_srcs" != $meta_key ) return;

		$videos  = maybe_unserialize( $meta_value );

		$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref ='wilcity_mycred_videos_updated_each' AND ref_id = {$post_id} ");
		
		if( $logs ) {
			$total_creds 			= wp_list_pluck( $logs, "creds" );
			$total_creds 			= array_sum($total_creds);
			$total_creds_remove 	= -1 * $total_creds;
			$total_creds_remove     = $this->core->number( $total_creds_remove );
			$total_creds_remove     = $this->core->enforce_max( $user_id, $total_creds_remove );
			$log_ids 				= wp_list_pluck( $logs, "id" );
			$log_ids 				= implode(",", $log_ids);

			$wpdb->query("DELETE FROM {$mycred_log_table} WHERE id IN({$log_ids}) "); 

			$this->core->update_users_balance( (int) $user_id, $total_creds_remove, $this->mycred_type );

			// Update total balance (if enabled)
			if ( MYCRED_ENABLE_TOTAL_BALANCE && MYCRED_ENABLE_LOGGING && ( $total_creds_remove > 0 || ( $total_creds_remove < 0 && "wilcity_mycred_videos_updated_each" == 'manual' ) ) ) {
				$this->update_users_total_balance( (int) $user_id, $total_creds_remove, $this->mycred_type );
			}
		}

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		// Limit & Execute
		foreach ( $videos as $video_id => $video_url) {
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

		/***********************************/

		/*$user_id = get_current_user_id();

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
		}*/
	}

	public function listing_updated_video_item_deleted($meta_id, $post_id, $meta_key, $meta_value) {

		global $wpdb, $mycred_log_table;

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_video_srcs" != $meta_key ) return;


		$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref ='wilcity_mycred_videos_updated_each' AND ref_id = {$post_id} ");
		
		if( $logs ) {
			$total_creds 			= wp_list_pluck( $logs, "creds" );
			$total_creds 			= array_sum($total_creds);
			$total_creds_remove 	= -1 * $total_creds;
			$total_creds_remove     = $this->core->number( $total_creds_remove );
			$total_creds_remove     = $this->core->enforce_max( $user_id, $total_creds_remove );
			$log_ids 				= wp_list_pluck( $logs, "id" );
			$log_ids 				= implode(",", $log_ids);


			foreach ( $logs as $key => $log ) {
				$this->core->add_creds(
					'wilcity_mycred_videos_removed_each',
					$user_id,
					-1 * $this->prefs['creds'],
					$this->prefs['log'],
					$post_id,
					"",
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
		
		/*add_action( 'added_post_meta',  array( $this, 'listing_updated_posts' ), 99, 4 );
		add_action( 'updated_postmeta',  array( $this, 'listing_updated_posts' ), 99, 4 );*/
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

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		$plan_title = get_the_title( $meta_value );

		if( $plan_title == "Premium" ) {
			$this->core->add_creds(
				'wilcity_mycred_posts_updated_each',
				$user_id,
				$this->prefs['creds'],
				$this->prefs['log'],
				$post_id,
				'add',
				$this->mycred_type
			);
		} else {
			$this->core->add_creds(
				'wilcity_mycred_posts_updated_each',
				$user_id,
				-1 * $this->prefs['creds'],
				$this->prefs['log'],
				$post_id,
				'add',
				$this->mycred_type
			);
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
				'creds'   => 1000,
				'limit'   => '1/d',
				'log'     => '%plural% for adding premium subscription on listing'
			)
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		
		add_action( 'added_post_meta',  array( $this, 'listing_updated_premium_subscription' ), 99, 4 );
		add_action( 'updated_postmeta',  array( $this, 'listing_updated_premium_subscription' ), 99, 4 );
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function listing_updated_premium_subscription( $meta_id, $post_id, $meta_key, $meta_value ) {

		$user_id = get_current_user_id();

		// Check if user is updating logo only and of lising only (required)
		if ( "listing" != get_post_type( $post_id ) ) return;

		if ( "wilcity_oldPlanID" != $meta_key ) return;

		if ( empty( $meta_value ) ) return;

		// Check if user is excluded (required)
		if ( $this->core->exclude_user( $user_id ) ) return;

		$plan_title = get_the_title( $meta_value );

		if( $plan_title == "Premium" || $plan_title == "premium" || strpos($plan_title, 'premium') !== false ) {
			$this->core->add_creds(
				'wilcity_mycred_premium_subscription_updated',
				$user_id,
				$this->prefs['creds'],
				$this->prefs['log'],
				$post_id,
				'add',
				$this->mycred_type
			);
		} else {
			$this->core->add_creds(
				'wilcity_mycred_premium_subscription_removed',
				$user_id,
				-1 * $this->prefs['creds'],
				$this->prefs['log'],
				$post_id,
				'add',
				$this->mycred_type
			);
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
		return $data;
	}
}


add_action( "wiloke-listing-tools/passed-preview-step", "wilcity_update_lising_menu_order", 99, 2 );
function wilcity_update_lising_menu_order( $listing_id, $plan_id ) {

	global $wpdb, $mycred_log_table;

	$user_id = get_current_user_id();
	$post_id = $listing_id;

	$logs = $wpdb->get_results( "SELECT * FROM {$mycred_log_table} WHERE ref_id = {$post_id} ");

	if( $logs ) {
		$total_creds = wp_list_pluck( $logs, "creds" );
		$total_creds = array_sum($total_creds);

		$updated = $wpdb->update( 
			$wpdb->prefix . "posts",
			array( 
				"menu_order"	=>	$total_creds
			), 
			array( 'ID' => $post_id )
		);
	}
}
