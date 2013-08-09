<?php

/**
 *	An extension to allow each data object JSON/XML preview capability, including some additional visibility fields for output customisation.
 *	@author Nathan Glasl <nathan@silverstripe.com.au>
 */

class DataObjectOutputExtension extends DataExtension {

	// Append an additional visibility field to each data object.

	public static $db = array(
		'APIwesomeVisibility' => 'Text'
	);

}
