<?php

namespace nglasl\APIwesome\Forms;

use nglasl\APIwesome\Models\APIwesomeToken;
use SilverStripe\Control\Controller;
use nglasl\APIwesome\Controllers\APIwesomeAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;

/**
 *	APIwesome functionality to display the current security token (allowing regeneration).
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class APIwesomeTokenView implements GridField_HTMLProvider
{

    /**
     *	Render the current security token and button for regeneration.
     * @param GridField $gridField
     * @return array
     */

    public function getHTMLFragments($gridfield)
    {

        $existingTokens = APIwesomeToken::get()->exists();

        // Temporarily retrieve the session value, preventing storage vulnerabilities.
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $currentToken = $session->get(APIwesomeToken::class);

        // Determine the state of the current security token.

        $token = "<div class='token'>";
        if ($currentToken === -1) {
            $status = 'error';
            $token .= strtoupper($status);
        } elseif (!$existingTokens) {
            $status = 'invalid';
            $token .= strtoupper($status);
        } else {
            $status = 'valid';
            $token .= $currentToken ? $currentToken : strtoupper($status);
        }
        $token .= '</div>';

        // Determine the current controller.

        $regenerateURL = 'apiwesome/regenerateToken';
        $controller = Controller::curr();
        if (!($controller instanceof APIwesomeAdmin)) {
            $regenerateURL .= '?from=' . $controller->Link();
        }
        $anchorClass = "regenerate ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary";
        return array(
            'before' => "<div class='apiwesome wrapper'>
				<div class='apiwesome admin {$status}'>
					<div><strong>Security Token</strong></div>
					{$token}
					<a href='{$regenerateURL}' class='{$anchorClass}' data-icon='arrow-circle-double'>Regenerate &raquo;</a>
				</div>
			</div>"
        );
    }
}
