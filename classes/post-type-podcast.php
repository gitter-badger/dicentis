<?php

if( !class_exists( 'Dicentis_Podcast_CPT' ) ) {
	/**
	 * The Podcast Post Type
	 */
	class Dicentis_Podcast_CPT {
		const POST_TYPE = 'podcast';
		private $_meta  = array(
			'_meta_a',
			'_meta_b',
			'_meta_c',
			'_dicentis_podcast_medialink'
		);
		/* push each taxonomy name, which is used in this plugin
		 * into this->_tax array. filter_posts() uses this array
		 * to know which taxonomy is used and display filter options
		 * for that
		 */
		private $_tax = array();

		public function __construct() {
			// register actions
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init() {
			// Initialize Post Type
			$this->register_podcast_post_type();
			$this->register_podcast_taxonomy();
			add_action( 'save_post', array( $this,'save_post' ) );

			// add taxonomy information for posts as new column
			// manage_podcast_posts_columns
			add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_tax_column' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'podcast_custom_column' ), 10, 2 );

			// add additional filter options to podcast site
			add_action( 'restrict_manage_posts', array( $this, 'filter_posts' ) );

			// script & style action with page detection
			add_action( 'admin_print_scripts-post.php', array( $this, 'media_admin_script' ) );
			add_action( 'admin_print_scripts-post-new.php', array( $this, 'media_admin_script' ) );
			add_action( 'admin_print_style-post.php', array( $this, 'media_admin_style' ) );
			add_action( 'admin_print_style-post-new.php', array( $this, 'media_admin_style' ) );
		} // END public function init()

		/**
		 * Create the post type
		 */
		public function register_podcast_post_type() {
			// set up arguments for podcast post type
			$podcast_args = array(
				'labels' => array(
					'name' => __( 'Podcasts', 'dicentis' ),
					'singular_name' => __( 'Podcast', 'dicentis' ),
					'add_new' => __( 'Add New Podcast', 'dicentis' ),
					'add_new_item' => __( 'Add New Podcast', 'dicentis' ),
					'edit_item' => __( 'Edit Podcast', 'dicentis' ),
					'new_item' => __( 'New Podcast', 'dicentis' ),
					'view_item' => __( 'View Podcast', 'dicentis' ),
					'search_items' => __( 'Search Podcast', 'dicentis' ),
					'not_found' => __( 'No Podcasts Found', 'dicentis' ),
					'not_found_in_trash' => __( 'No Podcast Found In Trash', 'dicentis' )
				),
				'public' => true,
				'has_archive' => true,
				'description' => __( 'A podcast plugin which allows to define multipel podcasts with individual feeds', 'dicentis' ),
				'supports' => array(
					'editor',
					'thumbnail',
					'title',
				),
				'menu_icon' => plugins_url( 'dicentis/assets/img/podcast-icon.png' ),
				'can_export' => 'true',
				// 'capabilities' => array(
				// 	'edit_post' => 'edit_podcast',
				// 	'edit_posts' => 'edit_podcasts',
				// 	'edit_other_posts' => 'edit_other_podcasts',
				// 	'publish_post' => 'publish_podcast',
				// 	'read_post' => 'read_podcast',
				// 	'read_private_posts' => 'read_private_podcast',
				// 	'delete_post' => 'delete_podcast',
				// ),
			);

			// Register the dicentis podcast post type
			if ( post_type_exists( self::POST_TYPE ) ) {
				/* @TODO: show admin notice */
				// don't register post type b/c there already exists one
				// with the same name.
			} else {
				register_post_type( self::POST_TYPE, $podcast_args );
			}
		} // END public function register_podcast_post_type()

		/**
		 * creates custom taxonomies for categorizing podcasts
		 * in series
		 */
		public function register_podcast_taxonomy() {
			$podcast_show_args = array(
				'hierarchical' => true,
				'query_var' => 'podcast_show',
				'rewrite' => array(
					'slug' => self::POST_TYPE . '/show',
				),
				'labels' => array(
					'name' => __( 'Podcast Shows', 'dicentis' ),
					'singular_name' => __( 'Podcast Show', 'dicentis' ),
					'edit_item' => __( 'Edit Podcast Show', 'dicentis' ),
					'update_item' => __( 'Update Podcast Show', 'dicentis' ),
					'add_new_item' => __( 'Add New Podcast Show', 'dicentis' ),
					'new_item_name' => __( 'New Podcast Show Name', 'dicentis' ),
					'all_items' => __( 'All Podcast Show', 'dicentis' ),
					'search_items' => __( 'Search Podcast Show', 'dicentis' ),
					'parent_item' => __( 'Parent Podcast Show', 'dicentis' ),
					'parent_item_colon' => __( 'Parent Podcast Show:', 'dicentis' ),
				),
			);

			// Set up the series taxonomy
			$series_args = array(
				'hierarchical' => true,
				'query_var' => 'podcast_series',
				'rewrite' => array(
					'slug' => self::POST_TYPE . '/series',
				),
				'labels' => array(
					'name' => __( 'Series', 'dicentis' ),
					'singular_name' => __( 'Series', 'dicentis' ),
					'edit_item' => __( 'Edit Series', 'dicentis' ),
					'update_item' => __( 'Update Series', 'dicentis' ),
					'add_new_item' => __( 'Add New Series', 'dicentis' ),
					'new_item_name' => __( 'New Series Name', 'dicentis' ),
					'all_items' => __( 'All Series', 'dicentis' ),
					'search_items' => __( 'Search Series', 'dicentis' ),
					'parent_item' => __( 'Parent Series', 'dicentis' ),
					'parent_item_colon' => __( 'Parent Series:', 'dicentis' ),
				),
			);

			// Set up the speaker taxonomy
			$speaker_args = array(
				'hierarchical' => true,
				'query_var' => 'podcast_speaker',
				'rewrite' => array(
					'slug' => self::POST_TYPE . '/speaker',
				),
				'labels' => array(
					'name' => __( 'Speakers', 'dicentis' ),
					'singular_name' => __( 'Speaker', 'dicentis' ),
					'edit_item' => __( 'Edit Speaker', 'dicentis' ),
					'update_item' => __( 'Update Speaker', 'dicentis' ),
					'add_new_item' => __( 'Add New Speaker', 'dicentis' ),
					'new_item_name' => __( 'New Speaker Name', 'dicentis' ),
					'all_items' => __( 'All Speaker', 'dicentis' ),
					'search_items' => __( 'Search Speaker', 'dicentis' ),
					'parent_item' => __( 'Parent Speaker', 'dicentis' ),
					'parent_item_colon' => __( 'Parent Speaker:', 'dicentis' ),
				),
			);

			// register show taxonomy
			if ( taxonomy_exists( 'podcast_show' ) ) {
				/* @TODO: show admin notice */
			} else {
				register_taxonomy( 'podcast_show', array( self::POST_TYPE ), $podcast_show_args );
				// array_push( $this->_tax, 'podcast_show' );
				$the_tax = get_taxonomy( 'podcast_show' );
				$this->_tax['podcast_show'] = $the_tax->labels->name;
			}

			/* push each taxonomy name, which is used in this plugin
			 * into this->_tax array. filter_posts() uses this array
			 * to know which taxonomy is used and display filter options
			 * for that
			 */
			// register series taxonomy
			if ( taxonomy_exists( 'celebration_series' ) ) {
				// avantgarde-celebration plugin is installed and active
				register_taxonomy_for_object_type( 'celebration_series', self::POST_TYPE );
				// array_push( $this->_tax, 'celebration_series' );
				$the_tax = get_taxonomy( 'celebration_series' );
				$this->_tax['celebration_series'] = $the_tax->labels->name;
			} else if ( taxonomy_exists( 'podcast_series' ) ) {
				/* @TODO: show admin notice */
			} else {
				register_taxonomy( 'podcast_series', array( self::POST_TYPE ), $series_args );
				// array_push( $this->_tax, 'podcast_series' );
				$the_tax = get_taxonomy( 'podcast_series' );
				$this->_tax['podcast_series'] = $the_tax->labels->name;
			}

			// register speaker taxonomy
			if ( taxonomy_exists( 'celebration_preachers' ) ) {
				// avantgarde-celebration plugin is installed and active
				register_taxonomy_for_object_type( 'celebration_preachers', self::POST_TYPE );
				// array_push( $this->_tax, 'celebration_preachers' );
				$the_tax = get_taxonomy( 'celebration_preachers' );
				$this->_tax['celebration_preachers'] = $the_tax->labels->name;
			} else if ( taxonomy_exists( 'podcast_speaker' ) ) {
				/* @TODO: show admin notice */
			} else {
				register_taxonomy( 'podcast_speaker', array( self::POST_TYPE ), $speaker_args );
				// array_push( $this->_tax, 'podcast_speaker' );
				$the_tax = get_taxonomy( 'podcast_speaker' );
				$this->_tax['podcast_speaker'] = $the_tax->labels->name;
			}
		} // END public function register_podcast_taxonomy()

		/**
		 * add additional filter options to post type site for each
		 * taxonomy which is used for this plugin
		 */
		public function filter_posts() {
			global $typenow;

			if( 'podcast' == $typenow ){

				/* push each taxonomy name, which is used in this plugin
				 * into this->_tax array. filter_posts() uses this array
				 * to know which taxonomy is used and display filter options
				 * for that
				 */
				foreach ( $this->_tax as $tax_slug => $tax_name ) {
					$tax_obj = get_taxonomy( $tax_slug );
					$tax_name = $tax_obj->labels->name;
					$terms = get_terms($tax_slug);

					echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
					echo "<option value=''>Show All $tax_name</option>";

					foreach ( $terms as $term ) {
						$selected = '';
						if ( isset( $_GET[$tax_slug] ) ) {
							if ( $_GET[$tax_slug] == $term->slug )
								$selected = ' selected="selected"';
						}

						echo '<option value='. $term->slug, $selected,'>' . $term->name .' (' . $term->count .')</option>';
					}

					echo "</select>";
				}
			}
		}

		/**
		 * Save the metaboxes for this custom post type
		 * @param  [type] $post_id [description]
		 * @return [type]          [description]
		 */
		public function save_post( $post_id ) {
			// verify if this is an auto save routine
			// If it is our form has not been submitted, so we don't want to do anything
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			{
				return;
			}

			if( $_POST['post_type'] == self::POST_TYPE && current_user_can( 'edit_post', $post_id ) )
			{
				foreach ($this->_meta as $field_name)
				{
					// update the post's meta field
					if ( strcmp( $field_name, '_dicentis_podcast_medialink' ) == 0 ) {
						update_post_meta( $post_id, $field_name, esc_url_raw( $_POST[ 'dicentis-podcast-medialink' ] ) );
						
					} else {
						update_post_meta( $post_id, $field_name, $_POST[substr($field_name, 1)] );
					}
				}
			}
			else
			{
				return;
			} // if( $_POST['post_type'] == self::POST_TYPE && current_user_can( 'edit_post', $post_id ) )
		} // END public function save_post( $post_id )

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init() {
			// Add metaboxes
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		} // END public function admin_init()

		/**
		 * hook into WP's add_meta_boxes action hook
		 * @param string $value [description]
		 */
		public function add_meta_boxes() {
			// Add this metabox to every selected post
			add_meta_box(
				sprintf( 'dicentis_%s_selection', self::POST_TYPE ),
				sprintf( __( '%s Information', 'dicentis' ), ucwords(str_replace("_", " ", self::POST_TYPE)) ),
				array( $this, 'add_inner_meta_boxes' ),
				self::POST_TYPE
			);
		} // END public function add_meta_boxes()

		/**
		 * called off of the add meta box
		 * @param [type] $post [description]
		 */
		public function add_inner_meta_boxes( $post ) {
			// Render the job order metabox
			include( sprintf('%s/../templates/%s_metabox.php', dirname(__FILE__), self::POST_TYPE) );
		} // END public function add_inner_meta_boxes( $post )

		public function media_admin_script() {
			wp_enqueue_script( 'dicentis-media-upload',
				// plugin_dir_path( __FILE__ ) . '../assets/js/dicentis-medialink.js',
				plugins_url( 'dicentis/assets/js/dicentis-medialink.js' ),
				array( 'jquery', 'media-upload', 'thickbox' )
			);
		}

		public function media_admin_style() {
			wp_enqueue_style( 'thickbox' );
		}

		public function add_tax_column( $columns ) {
			foreach ( $this->_tax as $tax_slug => $tax_name ) {
				$columns[$tax_slug] = __( $tax_name, 'dicentis' );
			}

			return $columns;
		}

		public function podcast_custom_column( $column_name, $post_id ) {
			$taxonomy = $column_name;
			$post_type = get_post_type($post_id);
			$terms = get_the_terms($post_id, $taxonomy);

			if ( !empty($terms) ) {
				foreach ( $terms as $term )
					$post_terms[] = "<a href='edit.php?post_type={$post_type}&{$taxonomy}={$term->slug}'> " . esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'edit')) . "</a>";
				echo join( ', ', $post_terms );
			}
			else echo '<i>No terms.</i>';
		}

		// public function updated_messages( $messages ) {
		// 	global $post, $post_ID;
		// 	$messages['podcast'] = array(
		// 		0 => '',
		// 		1 => sprintf( __('Podcast updated. <a href="%s">View podcast</a>'), esc_url( get_permalink($post_ID) ) ),
		// 		2 => __('Custom field updated.'),
		// 		3 => __('Custom field deleted.'),
		// 		4 => __('Podcast updated.'),
		// 		5 => isset($_GET['revision']) ? sprintf( __('Podcast restored from revision from %s'), wp_post_revision_title_( (int) $_GET['revision'], false ) ) : false,
		// 		6 => sprintf( __('Podcast published. <a href="%s">View podcast</a>'), esc_url( get_permalink($post_ID) ) ),
		// 		7 => __('Podcast saved.'),
		// 		8 => sprintf( __('Podcast submitted. <a target="_blank" href="%s">Preview podcast</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		// 		9 => sprintf( __('Podcast scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview podcast</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		// 		10 => sprintf( __('Podcast draft updated. <a target="_blank" href="%s">Preview podcast</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		// 	);
		// 	return $messages;
		// }
	} // END class Dicentis_Podcast_CPT
} // END if( !class_exists( 'Dicentis_Podcast_CPT' ) )