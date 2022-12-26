<?php

include '.tk/RoboFileBase.php';

class RoboFile extends RoboFileBase {
	public function directoriesStructure() {
		return array( 'includes', 'languages' );
	}

	public function fileStructure() {
		return array( 'loader.php', 'composer.json', 'license.txt', 'readme.txt' );
	}

	public function cleanPhpDirectories() {
		return array( 'includes/resources/tgm' );
	}

	public function pluginMainFile() {
		return 'loader';
	}

	public function pluginFreemiusId() {
		return 407;
	}

	public function minifyAssetsDirectories() {
		return array();
	}

	public function minifyImagesDirectories() {
		return array();
	}

	/**
	 * @return array Pair list of sass source directory and css target directory
	 */
	public function sassSourceTarget() {
		return array( array( 'scss/source' => 'assets/css' ) );
	}

	/**
	 * @return string Relative paths from the root folder of the plugin
	 */
	public function sassLibraryDirectory() {
		return 'scss/library';
	}
}