<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Lazy Loading Attribute
 * Plugin URI:        https://github.com/dmhendricks/wordpress-lazy-load-attribute-plugin/
 * Description:       Adds the lazy loading attribute to images and iframes contained within post content.
 * Version:           0.1.0
 * Author:            Daniel M. Hendricks
 * Requires at least: 4.8
 * Requires PHP:      5.4
 * Tested up to:      5.2.4
 * Author URI:        https://daniel.hn/
 * License:           GPLv2 or later
 * License URI:       https://github.com/dmhendricks/wordpress-lazy-load-attribute-plugin/blob/master/LICENSE
 * Text Domain:       lazy-loading-attribute
 * Domain Path:       /languages
 */

/**
 * Copyright 2019	  Daniel M. Hendricks (https://www.danhendricks.com/)
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace CloudVerve;
defined( 'ABSPATH' ) || die();

class Lazy_Loading_Attribute {

  private static $instance;
  private static $config;
  private static $identifier;
  private static $textdomain;
  
   final public static function init() {


      if ( !isset( self::$instance ) && !( self::$instance instanceof Lazy_Loading_Attribute ) ) {

         self::$instance = new Lazy_Loading_Attribute;
         self::$config = self::get_plugin_data();
         self::$textdomain = self::$config['domain'];
         self::$identifier = implode( '/', array_slice( explode( DIRECTORY_SEPARATOR, __FILE__), -2, 2, true ) );

         // Plugin activation hook
         register_activation_hook( __FILE__, array( self::$instance, 'activate' ) );

         // Load plugin text domain
         add_action( 'init', array( self::$instance, 'plugin_load_textdomain' ) );

         // Add plugin row meta
         add_action( 'plugin_row_meta', array( self::$instance, 'plugin_row_meta' ), 10, 2 );

         // Content filter
         add_filter( 'the_content', array( self::$instance, 'lazy_load_filter' ) );

      }

      return self::$instance;

   }

   /**
    * Plugin activation hook
    *
    * @since 1.0.0
    */
   public static function activate() {

      if( !class_exists( '\DOMDocument' ) || !class_exists( '\DOMXPath' ) ) {

         printf( __( '<strong>%s</strong> activation failed: %s is required', self::$textdomain ), self::$config['name'], '<a href="https://www.php.net/manual/en/book.libxml.php" target="_blank">libxml</a>' );
         die;

      }

   }

   /**
    * Load plugin translations
    *
    * @since 1.0.0
    */
   public static function plugin_load_textdomain() {

      load_plugin_textdomain( self::$textdomain, false, dirname( plugin_basename( __FILE__ ) ) . self::$config['domainpath'] );

   }

   /**
    * Add row meta to plugins page
    *
    * @return array
    * @since 1.0.0
    */
   public static function plugin_row_meta( $links = [], $file ) {

      if ( $file != self::$identifier ) return $links;
      $links[] = sprintf( '<a href="https://caniuse.com/#feat=loading-lazy-attr" target="_blank">%s</a>', __( 'Browser Compatibility', self::$textdomain ) );
      return $links;

   }

   /**
    * Get plugin data
    *
    * @return array
    * @since 1.0.0
    */
   public static function get_plugin_data() {

      return get_file_data( __FILE__, [ 'version' => 'Version', 'name' => 'Plugin Name', 'domain' => 'Text Domain', 'domainpath' => 'Domain Path' ], 'plugin' );

   }

   /**
    * Filter to add lazy loading attribute to images and iframes within post content
    *
    * @return string
    * @since 1.0.0
   */
   public static function lazy_load_filter( $content ) {

      if( !class_exists( '\DOMDocument' ) || !class_exists( '\DOMXPath' ) ) return $content;

      $content  = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
      $document = new \DOMDocument();
     
      libxml_use_internal_errors( true );
      $document->loadHTML( utf8_decode( $content ) );
      $xpath = new \DOMXpath( $document );
   
      $elements = $xpath->query( '//img | //iframe' );
      foreach( $elements as $element ) {
         $element->setAttribute( 'loading', 'lazy' );
      }
   
      return $document->saveHTML();

   }

}

Lazy_Loading_Attribute::init();
