<?php

namespace nglasl\APIwesome\Models;

use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;

/**
 *	APIwesome security token used to restrict JSON/XML access.
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class APIwesomeToken extends DataObject
{
    private static $table_name = 'APIwesomeToken';

    private static $db = array(
        'Hash' => 'Text'
    );

    private static $has_one = array(
        'Administrator' => Member::class
    );
}
