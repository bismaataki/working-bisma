<?php
/**
 * Zip helper for TGMPA bundled plugins.
 *
 * @package Happywood
 */

namespace Happywood;

use ZipArchive;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if class already defined, bail out
if ( class_exists( 'Happywood\Zip_Archive' ) || ! class_exists( 'ZipArchive' ) ) {
	return;
}


/**
 *
 */
class Zip_Archive extends ZipArchive {

	/**
	 * @param $location
	 * @param $name
	 *
	 * @return void
	 *
	 * @noinspection PhpMethodNamingConventionInspection
	 */
	public function addDir( $location, $name ) {
		$this->addEmptyDir( $name );
		$this->addDirDo( $location, $name );
	}


	/**
	 * @param $location
	 * @param $name
	 *
	 * @return void
	 * @noinspection PhpMethodNamingConventionInspection
	 */
	private function addDirDo( $location, $name ) {
		$name     .= '/';
		$location .= '/';
		$dir       = opendir( $location );
		while ( $file = readdir( $dir ) ) {
			if ( $file == '.' || $file == '..' ) {
				continue;
			}
			$do = ( filetype( $location . $file ) == 'dir' ) ? 'addDir' : 'addFile';
			$this->$do( $location . $file, $name . $file );
		}
	}
}
