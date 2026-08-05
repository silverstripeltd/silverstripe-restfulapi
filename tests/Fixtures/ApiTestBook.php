<?php

namespace Colymba\RESTfulAPI\Tests\Fixtures;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\ORM\DataObject;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestAuthor;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestLibrary;




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

class ApiTestBook extends DataObject
{
    private static string $table_name = 'ApiTestBook';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Pages' => 'Int',
    ];

    private static array $has_one = [
        'Author' => ApiTestAuthor::class,
    ];

    private static $belongs_many_many = [
        'Libraries' => ApiTestLibrary::class,
    ];

    public function validate(): ValidationResult   {
        if ($this->Pages > 100) {
            $result = ValidationResult::create()->addError('Too many pages');
        } else {
            $result = ValidationResult::create();
        }

        return $result;
    }
}
