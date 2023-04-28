<?php

namespace nglasl\APIwesome\Forms;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;

/**
 *	APIwesome button which will provide JSON/XML preview capability for the ModelAdminPreviewExtension.
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class APIwesomePreviewButton implements GridField_HTMLProvider
{

    /**
     *	Render the CMS JSON/XML preview buttons.
     * @param GridField $gridField
     * @return array
     */

    public function getHTMLFragments($gridfield)
    {

        // Print the data object name associated with this gridfield, mainly for readability.

        $object = strtolower(ltrim(preg_replace(array(
            '/([A-Z][a-z]+)/',
            '/([A-Z]{2,})/',
            '/([_.0-9]+)/'
        ), '-$0', $gridfield->name), '-'));
        $object = str_replace('-_-', '_', $object);

        // Retrieve the appropriate JSON/XML output paths.

        $JSON = "apiwesome/retrieve/{$object}/json";
        $XML = "apiwesome/retrieve/{$object}/xml";

        $anchorClasses = "preview disabled ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary";

        return array(
            'before' => "<div class='apiwesome wrapper'>
				<div class='apiwesome admin'>
					<div><strong>Security Token</strong></div>
					<div><input class='preview token' spellcheck='false'/></div>
					<a data-url='{$JSON}' href='{$JSON}' target='_blank' class='{$anchorClasses} json' data-icon='preview'>Preview JSON &raquo;</a>
					<br>
					<a data-url='{$XML}' href='{$XML}' target='_blank' class='{$anchorClasses} xml' data-icon='preview'>Preview XML &raquo;</a>
				</div>
			</div>"
        );
    }
}
