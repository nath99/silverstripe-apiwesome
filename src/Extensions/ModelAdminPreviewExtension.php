<?php

namespace nglasl\APIwesome\Extensions;

use nglasl\APIwesome\Models\DataObjectOutputConfiguration;
use nglasl\APIwesome\Services\APIwesomeService;
use SilverStripe\View\Requirements;
use nglasl\APIwesome\Forms\APIwesomePreviewButton;
use SilverStripe\Core\Extension;



/**
 *	APIwesome extension which allows JSON/XML preview capability for an individual data object type through the CMS interface.
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class ModelAdminPreviewExtension extends Extension
{

    /**
     *	Add the CMS JSON/XML preview buttons.
     */

    public function updateEditForm(&$form)
    {

        $gridfield = $form->fields->items[0];
        if (isset($gridfield) && ($gridfield->name !== DataObjectOutputConfiguration::class)) {

            // Make sure the appropriate JSON/XML exists for this data object type.

            $objects = singleton(APIwesomeService::class)->retrieveValidated($gridfield->name);
            if ($objects) {
                Requirements::css('nglasl/silverstripe-apiwesome:css/apiwesome.css');
                Requirements::javascript('nglasl/silverstripe-apiwesome:javascript/apiwesome.js');
                $configuration = $gridfield->config;
                $configuration->addComponent(new APIwesomePreviewButton());
            }
        }
    }
}
