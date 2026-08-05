<?php

namespace Colymba\RESTfulAPI\Tests\Fixtures;

use SilverStripe\ORM\DataObject;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestBook;




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

class ApiTestAuthor extends DataObject
{
    private static string $table_name = 'ApiTestAuthor';

    private static array $db = [
        'Name' => 'Varchar(255)',
        'IsMan' => 'Boolean',
    ];

    private static array $has_many = [
        'Books' => ApiTestBook::class,
    ];
}
