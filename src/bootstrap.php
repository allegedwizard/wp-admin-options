<?php
/**
 * Newest-copy-wins loader.
 *
 * Every plugin that depends on this package ships its own vendored copy, and
 * WordPress loads them all. Without coordination the first Composer
 * autoloader to resolve a class wins, so a site can end up running an old
 * copy's classes, CSS, and JS next to a newer plugin that expects features
 * the old copy lacks.
 *
 * This file is Composer "files"-autoloaded from every copy at plugin load.
 * Each run registers its copy (src path + version) in a shared registry and
 * makes sure ONE shared autoloader sits ahead of every plugin's Composer
 * loader. That autoloader resolves AllegedWizard\WPAdminOptions\* classes
 * from whichever registered copy has the highest version, so the newest
 * code runs everywhere and, because the Bootstrap class enqueues assets
 * relative to its own file, the newest CSS/JS are served too.
 *
 * Limits: a class that some plugin already loaded before a newer copy
 * registered stays loaded (PHP cannot swap a class), and the autoloader
 * function body is the one from the first copy to run, so it is kept
 * deliberately small and version-agnostic.
 */

namespace AllegedWizard\WPAdminOptions;

$wao_src = __DIR__;
$wao_version = (string) ( file_exists( $wao_src . '/version.php' ) ? require $wao_src . '/version.php' : '0.0.0' );

if ( ! isset( $GLOBALS['alleged_wizard_wp_admin_options_copies'] ) || ! is_array( $GLOBALS['alleged_wizard_wp_admin_options_copies'] ) ) {
    $GLOBALS['alleged_wizard_wp_admin_options_copies'] = [];
}

$GLOBALS['alleged_wizard_wp_admin_options_copies'][ $wao_src ] = $wao_version;

if ( ! function_exists( __NAMESPACE__ . '\\newest_copy' ) ) {
    /**
     * The src directory of the registered copy with the highest version.
     *
     * @return string|null
     */
    function newest_copy() {
        $best_path = null;
        $best_version = null;

        foreach ( (array) ( $GLOBALS['alleged_wizard_wp_admin_options_copies'] ?? [] ) as $path => $version ) {
            if ( null === $best_version || version_compare( (string) $version, (string) $best_version, '>' ) ) {
                $best_path = $path;
                $best_version = $version;
            }
        }

        return $best_path;
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\autoload' ) ) {
    /**
     * PSR-4 resolve an AllegedWizard\WPAdminOptions class from the newest copy.
     *
     * @param string $class
     */
    function autoload( $class ) {
        $prefix = __NAMESPACE__ . '\\';

        if ( 0 !== strpos( $class, $prefix ) ) {
            return;
        }

        $src = newest_copy();

        if ( null === $src ) {
            return;
        }

        $file = $src . '/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';

        if ( is_file( $file ) ) {
            require $file;
        }
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\prepend_autoloader' ) ) {
    /**
     * Put the shared autoloader at the front of the SPL queue, ahead of the
     * per-plugin Composer loaders (which also prepend themselves, so this
     * must run again after every plugin has loaded).
     */
    function prepend_autoloader() {
        spl_autoload_unregister( __NAMESPACE__ . '\\autoload' );
        spl_autoload_register( __NAMESPACE__ . '\\autoload', true, true );
    }
}

prepend_autoloader();

// Plugins that load AFTER this one prepend their own Composer loaders; step
// back in front once everything is loaded, before any hook that renders
// admin screens fires.
if ( function_exists( 'add_action' ) && ! defined( 'ALLEGED_WIZARD_WP_ADMIN_OPTIONS_LOADER_HOOKED' ) ) {
    define( 'ALLEGED_WIZARD_WP_ADMIN_OPTIONS_LOADER_HOOKED', true );
    add_action( 'plugins_loaded', __NAMESPACE__ . '\\prepend_autoloader', PHP_INT_MIN );
    add_action( 'muplugins_loaded', __NAMESPACE__ . '\\prepend_autoloader', PHP_INT_MIN );
}

unset( $wao_src, $wao_version );
