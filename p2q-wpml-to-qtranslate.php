<?php
/**
 * Plugin Name: P2Q: Polylang to qTranslate
 * Description: Migrate Polylang translations to qTranslate. Goodbye Polylang, hello qTranslate.
 * Version: 0.1
 * Author: Iain MacDonald, Jos Koenis
 * License: GPL2
 */

 /*
 Change history:
 0.1:
	- First version - Based on W2Q: WPML to qTranslate 0.9.3

 */

defined('ABSPATH') or die();


class Polylang_to_qtranslate {

	private $db;
	private $qts = null;

	private $ajax_data = array();

	private $q_config = null;

	public function __construct() {
		add_action('admin_menu', array( &$this, 'action_admin_menu' ));
		add_action('wp_ajax_p2q_execute', array ( &$this, 'ajax_execute' ));
	}

	public function action_admin_menu() {
		add_options_page( 'P2Q: Polylang to qTranslate options', 'P2Q: Polylang to qTranslate', 'manage_options', 'p2q-polylang-to-qtranslate', array( &$this, 'admin_page') );
	}

	function admin_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$this->init_objects();

		$this->q_config = $this->get_q_config();

?>
<style>
	#p2q .p2q-warning { color: red; }
	#p2q .p2q-info { font-style: oblique; }
	#p2q .p2q-error { color: red; font-weight: bold; }
</style>
<div class="wrap" id="p2q">
	<h2>Polylang to qTranslate migration</h2>
	<h3>Step 1: Create a test environment</h3>
	<p>The <a href="https://wordpress.org/plugins/duplicator/" target="_blank">Duplicator</a>-plugin is an excellent tool to create a copy of your wordpress environment.

	<p>If you have the guts te perform the migration on a production environment,
	please make sure you <strong>make a full backup of your database before using Polylang to qTranslate</strong>.

	<h3>Step 2: Disable Polylang</h3>
	<p>Disable the Polylang plugins to prevent it from reassigning translations of posts or taxonomies.

	<?php if ($this->is_polylang_enabled()) {
		echo "<div class='p2q-info p2q-warning'>Note: Polylang is not disabled. Please disable it before proceeding.</strong></div>\n";
	} else {
		echo "<div class='p2q-info'>Polylang disabled: <strong>YES</strong></div>";
	} ?>

	<h3>Step 3: Install and activate qTranslate X</h3>
	<p>Install and activate <a href="https://wordpress.org/plugins/qtranslate-x/" target="_blank">qTranslate X</a> (or any other qTranslate fork) and
	<a href="https://wordpress.org/plugins/qtranslate-slug/" target="_blank">qTranslate Slug</a>. This is important if you want your slug translations to be migrated.
	This is also a good time to configure the languages and permalink settings of qTranslate X.
	<p>Test if your page still works after installation (menus and contents in all languages will be visible, just ignore that)

  <div class='p2q-info'>Languages found in Polylang: <strong><?php echo join(", ", $this->get_polylang_languages()) ?></strong></div>

  <div class='p2q-info'>Default language in Polylang: <strong><?php echo $this->get_polylang_default_language() ?></strong></div>

  <div class='p2q-info'>Number of Polylang posts found: <strong><?php echo $this->get_polylang_post_count() ?></strong></div>

	<div class='p2q-info'>Enabled languages in qTranslate: <strong>
	<?php
		if ( is_array($this->q_config['enabled_languages']) ) {
			echo join(", ", $this->q_config['enabled_languages'] );
		} else {
			echo "<span class='p2q-error'>none</span>";
		}
	?>
	</strong></div>

  <div class='p2q-info'>Default language in qTranslate: <strong><?php echo $this->q_config["default_language"] ?></strong>
	<?php
		if ( $this->q_config['default_language'] != $this->get_polylang_default_language() ) {
			echo "<span class='p2q-error'>Defaults not the same</span>";
		}
	?>
  </div>

	<div class='p2q-info'>qTranslate Slug detected: <strong><?php echo isset($this->qts) ? "YES" : "<span class='p2q-error'>NO</span>"; ?></strong></div>

	<h3>Step 4: Execute the migration process</h3>
	<p><strong>Important:</strong> This plugin will migrate all Polylang translations to qTranslate, therefore <strong>existing Polylang functionality will break</strong>.

	<p>Press this button if you've completed step 1, 2 and 3

	<?php if (WP_DEBUG) {
			echo "<div class='p2q-info p2q-warning'>Note: WP_DEBUG is enabled. If execution fails with a fatal error, please disable WP_DEBUG and try again.</strong></div><br>\n";
	} ?>

	<form id="p2q-form" type="post" action="">
		<input type="hidden" name="action" value="p2q_execute"/>
		<?php wp_nonce_field( 'p2q_execute', 'p2q_nonce' ); ?>
		<div>
			<button id="p2q-submit" class="button-primary" type="submit">Execute</button>
			<span class="spinner"></span>
		</div>
		<div id="p2q-progress"></div>
		<div id="p2q-warnings"><ul></ul></div>
	</form>

	<h3>Step 5: Tweaking your website</h3>
	<p>Ok, now the translation is done, test your website! Some of the problems that you might need to fix:
	<p>See content of all language concatenated? Some hooks might have to be added, like this: <code>add_filter('some_hook', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);</code>.
	<p>Translations missing? The Polylang string translation are not migrated. You might need to alter .po/.mo files of some plugins or your theme.
	<p>By the way, you may disable this plugin when the migration is done. Thanks and goodye! Don't forget to <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=5T9XQBCS2QHRY&amp;lc=NL&amp;item_name=Jos%20Koenis&amp;item_number=wordpress%2dplugin&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" title="Support the development">donate</a>!
  <p>(Donation link belongs to original author of W2Q: WPML to qTranslate plugin)</p>
</div>
<?php

		wp_enqueue_script( 'p2q-ajax', plugin_dir_url(__FILE__) . 'js/p2q-ajax.js', array('jquery') );

	}


// ===================

	//Warnings to ajax data
	function warn($warning) {
		if (! isset ($this->ajax_data['warnings']) )
			$this->ajax_data['warnings'] = array();

		$this->ajax_data['warnings'][] = $warning;
	}


	//Debug to ajax data
	function debug($debug) {
		if (! isset ($this->ajax_data['debug']) )
			$this->ajax_data['debug'] = array();

		$this->ajax_data['debug'][] = $debug;
	}

	//Count ajax data
	function count($name, $amount = 1) {
		if (! isset ($this->ajax_data[$name]) )
			$this->ajax_data[$name] = 0;

		$this->ajax_data[$name] += $amount;
	}

	//Get prefixed tablename
	function prefix($table_name = "") {
		return $this->db->prefix . $table_name;
	}

	function is_polylang_enabled() {
		return function_exists('pll__');
	}

	function get_q_config() {
		$q_config = array();
		$q_config['language_names'] = get_option('qtranslate_language_names');
		$q_config['enabled_languages'] = get_option('qtranslate_enabled_languages');
		$q_config['default_language'] = get_option('qtranslate_default_language');
		$q_config['term_name'] = get_option('qtranslate_term_name');
		return $q_config;
	}

	function update_q_config( $q_config, $option_name ) {
		update_option( 'qtranslate_' . $option_name, $q_config[ $option_name ] );
	}

  /**
   * Get array with all language codes in term_taxonomy table
   */
  function get_polylang_languages() {
    $query = "SELECT b.slug,b.name,a.description,a.count FROM " . $this->prefix('term_taxonomy') . " AS a LEFT JOIN " . $this->prefix('terms') . " as b ON a.term_id = b.term_id WHERE a.taxonomy = 'language'";
    $rows = $this->db->get_results( $query );

    $retval = array();
    foreach ($rows as $k => $v) {
        $retval[] = $v->slug;
    }
    return $retval;
  }

  function get_polylang_default_language() {
    $option = maybe_unserialize(get_option("polylang"));
    return $option["default_lang"];;
  }

  function get_polylang_post_count() {
    return $this->db->get_var( $this->db->prepare("SELECT count(*) FROM " . $this->prefix('term_taxonomy') . " WHERE taxonomy = 'post_translations'", null ) );
  }

	function init_objects() {
		global $wpdb;
		$this->db = $wpdb;

		//QTS plugin object
		global $qtranslate_slug;
		if (isset($qtranslate_slug)) $this->qts = $qtranslate_slug;

		$this->q_config = $this->get_q_config();

		//QTS Taxonomies
		$this->qts_taxonomies = array();
		$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'object' );
		foreach($taxonomies as $t)
			$this->qts_taxonomies[] = $t->name;
	}

	//Execute the magic
	function ajax_execute() {
		if (!wp_verify_nonce( $_POST['p2q_nonce'], 'p2q_execute' ) || !current_user_can( 'manage_options' ) )
			die (json_encode(array('fatal' => 'Security failure', 'data' => $_POST)));

		if ( $this->is_polylang_enabled() ) {
			die (json_encode(array('fatal'=> 'Polylang is active!')));
		}

		$this->init_objects();

		//Get value from $_POST or default value
		function getPost($key, $default) { return (isset($_POST[$key])) ? $_POST[$key] : $default; }

		$this->ajax_data = array(
			'warnings' => array(),
			'comments_migrated' => getPost('comments_migrated', 0),
			'records_flushed' => getPost('records_flushed', 0)
		);

		$limit = 500; // Not too many items at a time


		$total_counter = getPost('total_counter', 0);
		$start_id = getPost('last_translation_id', 0);

		$query = $this->db->prepare("SELECT * FROM " . $this->prefix('term_taxonomy') . " WHERE taxonomy = 'post_translations' AND term_taxonomy_id > %d LIMIT %d", $start_id, $limit );
		$rows = $this->db->get_results($query);

		$n = 0;
		$begin_time = microtime(true);
		foreach ($rows as $r) {
			//Break out of the loop after 2 seconds
			if (microtime(true) - $begin_time > 2) {
				break;
			}

			$this->translate_row($r);

			$last_translation_id = $r->term_taxonomy_id;
			$n++;
		}

		$todo = $this->db->get_var( $this->db->prepare("SELECT count(*) FROM " . $this->prefix('term_taxonomy') . " WHERE taxonomy LIKE '%_translations' AND term_taxonomy_id > %d LIMIT %d", $last_translation_id, $limit ) );

		$json = $this->ajax_data;

		$json['continue'] = $todo > 0;
		$json['last_translation_id'] = $last_translation_id;
		$json['total_counter'] = $total_counter += $n;
		if ($todo > 0)
			$json['message'] = sprintf("%d translations migrated. %d translations to go.", $total_counter, $todo );
		else
			$json['message'] = sprintf("Done! %d translations were migrated. %d comments migrated. %d records flushed.", $total_counter, $json['comments_migrated'], $json['records_flushed']);

		die ( json_encode($json) );
	}

	function translate_row($r) {
			$_type = preg_split("/_/", $r->taxonomy, 2);
      $type = $_type[0];

			// $translation_map = $this->get_translation_map( $r->term_taxonomy_id );

			switch ($type) {
				case "comment":
					//comments are not translated
					break;
				case "post":
					$this->tr_post($r->term_taxonomy_id, $r);
					break;
				case "term":
          // taxonommies are not translated yet
					// $this->tr_taxonomy($r->element_id, $r->trid, $r->element_type);
					break;
				default:
					$this->warn( sprintf("Unknown element %s[%d], trid %d.", $r->taxonomy, $r->description, $r->term_taxonomy_id) );
			}
	}

	function delete_translations($trid) {
		$query =  $this->db->prepare("DELETE FROM " . $this->prefix('term_taxonomy') . " WHERE term_taxonomy_id = %d", $trid);
		$this->db->query($query);
		$query =  $this->db->prepare("DELETE FROM " . $this->prefix('terms') . " WHERE term_id = %d", $trid);
		$this->db->query($query);
	}

	///Get element_ids for all translations
	///The first item is the source item     <--- I haven't checked this
	/// array ( 'lang' : id, ... )
	function get_translation_map($trid, $exclude_source = false) {
		$query = "SELECT description FROM " . $this->prefix('term_taxonomy') . " WHERE term_taxonomy_id = %d";

		$rows = $this->db->get_results(  $this->db->prepare($query, $trid)  );

    if(count($rows) < 1) return array();

    $map = maybe_unserialize($rows[0]);

    if(!is_array($map)) return array();

		return $map;
	}

	///Get element_ids for all children
	/// array ( 'lang' : id, ... )
	function get_child_translation_map($trid, $parent_table, $parent_id_field, $parent_foreign_id_field, $exclude_source = false) {

		$query = "SELECT language_code, element_id, element_type from " . $this->prefix('icl_translations') . " WHERE trid=%d";
		if ($exclude_source) $query .= " AND source_language_code IS NOT NULL";
		$query .= " ORDER BY source_language_code IS NOT NULL";

		$rows = $this->db->get_results(  $this->db->prepare($query, $trid)  );

		$retval = array();
		foreach ($rows as $row) {
			if ($row->language_code == "") {
				$this->warn( sprintf( "Language_code missing for %s[%d], trid %d.", $row->element_type, $row->element_id, $trid ) );
			} else {
				$query2 = $this->db->prepare( "SELECT $parent_foreign_id_field FROM $parent_table WHERE $parent_id_field = %d", $row->element_id );
				$rows2 = $this->db->get_results(  $query2  );
				foreach ($rows2 as $row2) {
					$retval[ $row->language_code ] = $row2->$parent_foreign_id_field;
				}
			}
		}
		return $retval;
	}


	function get_post($id) {
		$query = $this->db->prepare( "SELECT * FROM $this->db->posts WHERE id=%d", $id );
		return $this->db->get_row(  $query  );
	}

	//Get a jagged array with translations, like:
	// [
	// 'post_content' => [ 'es' => 'hola', 'en' => 'hi' ],
	// 'post_something' => [ 'es' => 'hola', 'en' => 'hi' ],
	// ]
	function get_translations($translation_map, $table, $id_col, $columns) {

		//Make sure it's an array
		if (! is_array( $columns ) )
			$columns = array($columns);

		$translations = array();
		foreach ( $columns as $col )
			$translations[$col] = array();

		foreach($translation_map as $lang => $id) {
			$query = "SELECT * FROM `$table` WHERE `$id_col` = %d";
			$row = $this->db->get_row(  $this->db->prepare( $query, $id )  );

			$texts = array();
			foreach ( $columns as $col) {
				$text = $row->$col; // get value for the given column

				//Ignore if empty
				if  ($text !== null && $text !== "") {
					$tr = P2q::qtranxf_split($text, true, $lang);  // just in case it's already translated, parse as qTranslate
					foreach ($tr as $lng => $txt) {
						$translations[$col][$lng] = $txt;
					}
				}
			}
		}
		return $translations;
	}

	//$columns can be a single column-name or an array.
	function translate_element($element_id, $translation_map, $table, $id_col, $columns) {
		if (count($translation_map) < 1) {
			$this->debug($element_id." nothing to do.");
			return;
		}

		$translated = $this->get_translations($translation_map, $table, $id_col, $columns);

		//arrays met vertalingen omzetten naar 1 qTranslate format string
		foreach($translated as $col => $v) {
			$translated[$col] = P2q::qtranxf_join( $v );
		}

		$this->db->update($table, $translated, array($id_col => $element_id));

		//Delete translated posts (Polylang-style)
		foreach($translation_map as $k => $v) {
			if ($v != $element_id) {
				$this->count('records_flushed', $this->db->delete( $table, array( $id_col => $v ), array( '%d' ) ));
			}
		}
	}

	//$columns can be a single column-name or an array.
	function translate_term($element_id, $translation_map ) {
		if (count($translation_map) < 1) {
			return;
		}

		//translated will be a jagged array, like:
		// [
		// 'post_content' => [ 'es' => 'hola', 'en' => 'hi' ],
		// 'post_something' => [ 'es' => 'hola', 'en' => 'hi' ],
		// ]
		$translations = $this->get_translations($translation_map, $this->db->terms, 'term_id', 'name' );

		$first_item = reset( $translations['name'] );
		$this->q_config['term_name'][$first_item] = $translations['name'];

		$this->update_q_config( $this->q_config, 'term_name' );

		//Delete translated posts (Polylang-style)
		foreach($translation_map as $k => $v) {
			if ($v != $element_id) {
				$this->count('records_flushed', $this->db->delete( $this->db->terms, array( 'term_id' => $v ), array( '%d' ) ));
			}
		}
	}

	//Insert _qts_slug_xx meta values for posts (for qTranslate slug plugin)
	function translate_post_slugs_for_qts($post_id, $translation_map) {
		foreach($translation_map as $lang => $id) {
			$query = "SELECT `post_name` FROM `" . $this->db->posts . "` WHERE `id` = %d";
			$slug = $this->db->get_var(  $this->db->prepare( $query, $id )  );
			if ( isset($slug) ) {
				update_post_meta($post_id, '_qts_slug_' . $lang, $slug );
			}
		}
	}

	//Insert _qts_slug_xx meta values for terms (for qTranslate slug plugin)
	function translate_term_slugs_for_qts($term_id, $translation_map) {
		foreach($translation_map as $lang => $id) {
			$query = "SELECT `slug` FROM `" . $this->db->terms . "` WHERE `term_id` = %d";
			$slug = $this->db->get_var(  $this->db->prepare( $query, $id )  );
			if ( isset($slug) ) {
				update_term_meta($term_id, '_qts_slug_' . $lang, $slug );
			}
		}
	}

	function tr_post($trid, $row) {
    if(is_object($row)){
      $translation_map = maybe_unserialize($row->description);
    }

    if(!is_array($translation_map)) {
	    $translation_map = $this->get_translation_map($trid);
    }

    $default_lang = $this->q_config["default_language"];
    if(isset($translation_map[$default_lang])){
      $source_element_id = $translation_map[$default_lang];
    }
    else {
      // Get first language listed in map and treat that as the default
      $source_element_id = reset($translation_map);
    }

		// //Slugs for qts
		// if (isset($this->qts))
		// 	$this->translate_post_slugs_for_qts($source_element_id, $translation_map);

		$this->translate_element($source_element_id, $translation_map, $this->db->posts, 'id', array('post_content', 'post_title', 'post_excerpt') );
		foreach ($translation_map as $lang => $post_id) {
			if ($post_id != $source_element_id) {
				//Assign comments of the translated posts to the source post
				$this->count('comments_migrated', $this->db->update( $this->db->comments, array( 'comment_post_id' => $source_element_id ), array ( 'comment_post_id' => $post_id ) ));

				//Delete postmeta of the translated posts
				$this->count('records_flushed', $this->db->delete( $this->db->postmeta, array( 'post_id'=> $post_id ), array( '%d' ) ) );
			}
		}
		$this->delete_translations($trid);
	}

	function tr_taxonomy($source_element_id, $trid, $element_type) {
		//Translate terms (children first, because otherwise translations will be lost due to DELETE query)
		$term_translations = $this->get_child_translation_map($trid, $this->db->term_taxonomy, 'term_taxonomy_id', 'term_id');

		//Slugs for qts
		if (isset($this->qts))
			$this->translate_term_slugs_for_qts($source_element_id, $term_translations);

		$this->translate_term(reset($term_translations), $term_translations );

		//Translate taxonomy
		$translation_map = $this->get_translation_map($trid);
		$this->translate_element($source_element_id, $translation_map, $this->db->term_taxonomy, 'term_taxonomy_id', array('description') );
		$this->delete_translations($trid);
	}

}

//qTranslate encode/decode functions (thanks! qTranslate and qTranslate X team)
include('p2q-helpers.php');

/**
 * Create the plugin if WooCommerce is active
 **/
$polylang_to_qtranslate = new Polylang_to_qtranslate();

/**
 * Add donate-link to plugin page
 */
if ( ! function_exists( 'polylang_to_qtranslate_plugin_meta' ) ) {
	function polylang_to_qtranslate_plugin_meta( $links, $file ) {
		if ( strpos( $file, 'polylang-to-qtranslate-x.php' ) !== false ) {
			$links = array_merge( $links, array( '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5T9XQBCS2QHRY&lc=NL&item_name=Jos%20Koenis&item_number=wordpress%2dplugin&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" title="Support the development">Donate</a>' ) );
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'polylang_to_qtranslate_plugin_meta', 10, 2 );
}



//Required Filters

//add_filter('bcn_breadcrumb_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage'); //navxt breadcrumb
//add_filter('term_description', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage',0);
// //Onderstaand kan in "qTranslate support for woocommerce"-plugin worden opgelost door hook get_the_terms te vervangen door get_terms
// //Translate product tags in the tag cloud
// add_filter('get_terms', function($terms, $taxonomies) {
	// foreach($terms as $term)
		// $term->name = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($term->name);
	// return $terms;
 // },0,2);
