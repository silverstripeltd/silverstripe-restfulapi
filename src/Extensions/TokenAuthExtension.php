<?php

namespace Colymba\RESTfulAPI\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

/**
 * RESTfulAPI Token authentication data extension
 * Add to any DataObject that will store the authentication token
 * e.g. Member
 *
 * @author  Thierry Francois @colymba thierry@colymba.com
 * @copyright Copyright (c) 2013, Thierry Francois
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD Simplified
 *
 * @package RESTfulAPI
 * @subpackage Authentication
 */
class TokenAuthExtension extends Extension
{

    private static $db = array(
        'ApiToken' => 'Varchar(160)',
        'ApiTokenExpire' => 'Int',
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('ApiToken');
        $fields->removeByName('ApiTokenExpire');
    }
}
