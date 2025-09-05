<?php

namespace Colymba\RESTfulAPI\Tests\Fixtures;

use SilverStripe\ORM\DataObject;



/**
 * RESTfulAPI Test suite DataObjects
 *
 * @author  Thierry Francois @colymba thierry@colymba.com
 * @copyright Copyright (c) 2013, Thierry Francois
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD Simplified
 *
 * @package RESTfulAPI
 * @subpackage Tests
 */
class ApiTestProduct extends DataObject
{
    private static string $table_name = 'ApiTestProduct';

    public static string $rawJSON;

    private static array $db = [
        'Title' => 'Varchar(64)',
        'Soldout' => 'Boolean',
    ];

    private static bool $api_access = true;

    public function onAfterDeserialize(&$payload)
    {
        // don't allow setting `Soldout` via REST API
        unset($payload['Soldout']);
    }

    public function onBeforeDeserialize(&$rawJson)
    {
        self::$rawJSON = $rawJson;
    }
}
