<?php

namespace nglasl\APIwesome\Extensions;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Security\Security;
use SilverStripe\Security\Permission;
use SilverStripe\View\Requirements;
use nglasl\APIwesome\Forms\APIwesomeTokenView;
use SilverStripe\Core\Extension;

/**
 *	APIwesome extension which displays the current security token (allowing regeneration for an administrator).
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class APIwesomeTokenExtension extends Extension
{

    /**
     *	Display the current security token (allowing regeneration for an administrator).
     */

    public function updateEditForm(&$form)
    {

        // Determine whether the security section is being used.

        if ($this->owner instanceof SecurityAdmin) {
            $gridfield = null;
            foreach ($form->fields->items[0]->Tabs()->first()->Fields() as $field) {
                if ($field instanceof GridField) {
                    $gridfield = $field;
                    break;
                }
            }
        } else {
            $gridfield = $form->fields->items[0];
        }
        if (isset($gridfield) && ($gridfield instanceof GridField)) {

            // Restrict the security token to administrators.

            $user = Security::getCurrentUser()->ID;
            if (Permission::checkMember($user, 'ADMIN')) {
                Requirements::css('nglasl/silverstripe-apiwesome:css/apiwesome.css');
                Requirements::javascript('nglasl/silverstripe-apiwesome:javascript/apiwesome.js');

                // Display a confirmation message when regenerating the security token.

                $configuration = $gridfield->config;
                $configuration->addComponent(new APIwesomeTokenView());
            }
        }
    }
}
