<?php

namespace Colymba\RESTfulAPI\Tests\API;

use Colymba\RESTfulAPI\RESTfulAPI;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use Colymba\RESTfulAPI\Tests\RESTfulAPITester;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestBook;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestAuthor;
use Colymba\RESTfulAPI\Tests\Fixtures\ApiTestLibrary;
use Colymba\RESTfulAPI\QueryHandlers\DefaultQueryHandler;




/**
 * RESTfulAPI Test suite
 *
 * @author  Thierry Francois @colymba thierry@colymba.com
 * @copyright Copyright (c) 2013, Thierry Francois
 *
 * @license http://opensource.org/licenses/BSD-3-Clause BSD Simplified
 *
 * @package RESTfulAPI
 * @subpackage Tests
 */
class RESTfulAPITest extends RESTfulAPITester
{
    protected static $extra_dataobjects = array(
        ApiTestAuthor::class,
        ApiTestBook::class,
        ApiTestLibrary::class,
    );

    /* **********************************************************
     * TESTS
     * */

    /**
     * Checks that api access config check works
     */
    public function testDataObjectAPIEnaled()
    {
        Config::modify()->set(RESTfulAPI::class, 'access_control_policy', 'ACL_CHECK_CONFIG_ONLY');
        // ----------------
        // Method Calls

        // Disabled by default
        $enabled = RESTfulAPI::api_access_control(ApiTestAuthor::class);
        $this->assertFalse($enabled, 'Access control should return FALSE by default');

        // Enabled
        Config::modify()->set(ApiTestAuthor::class, 'api_access', true);
        $enabled = RESTfulAPI::api_access_control(ApiTestAuthor::class);
        $this->assertTrue($enabled, 'Access control should return TRUE when api_access is enbaled');

        // Method specific
        Config::modify()->set(ApiTestAuthor::class, 'api_access', 'GET,POST');

        $enabled = RESTfulAPI::api_access_control(ApiTestAuthor::class);
        $this->assertTrue($enabled, 'Access control should return TRUE when api_access is enbaled with default GET method');

        $enabled = RESTfulAPI::api_access_control(ApiTestAuthor::class, 'POST');
        $this->assertTrue($enabled, 'Access control should return TRUE when api_access match HTTP method');

        $enabled = RESTfulAPI::api_access_control(ApiTestAuthor::class, 'PUT');
        $this->assertFalse($enabled, 'Access control should return FALSE when api_access does not match method');

        // ----------------
        // API Calls
        /*
    // Access authorised
    $response = Director::test('api/ApiTestAuthor/1', null, null, 'GET');
    $this->assertEquals(
    $response->getStatusCode(),
    200
    );

    // Access denied
    Config::modify()->set(ApiTestAuthor::class, 'api_access', false);
    $response = Director::test('api/ApiTestAuthor/1', null, null, 'GET');
    $this->assertEquals(
    $response->getStatusCode(),
    403
    );

    // Access denied
    Config::modify()->set(ApiTestAuthor::class, 'api_access', 'POST');
    $response = Director::test('api/ApiTestAuthor/1', null, null, 'GET');
    $this->assertEquals(
    $response->getStatusCode(),
    403
    );
     */
    }

    /* **********************************************************************
     * CORS
     * */

    /**
     * Check that CORS headers aren't set
     * when disabled via config
     *
     * @group CORSPreflight
     */
    public function testCORSDisabled()
    {
        Config::modify()->set(RESTfulAPI::class, 'cors', array(
            'Enabled' => false,
        ));

        $requestHeaders = $this->getOPTIONSHeaders();
        $response = Director::test('api/ApiTestBook/1', null, null, 'OPTIONS', null, $requestHeaders);
        $headers = $response->getHeaders();

        $this->assertFalse(array_key_exists('Access-Control-Allow-Origin', $headers), 'CORS ORIGIN header should not be present');
        $this->assertFalse(array_key_exists('Access-Control-Allow-Headers', $headers), 'CORS HEADER header should not be present');
        $this->assertFalse(array_key_exists('Access-Control-Allow-Methods', $headers), 'CORS METHOD header should not be present');
        $this->assertFalse(array_key_exists('Access-Control-Max-Age', $headers), 'CORS AGE header should not be present');
    }

    /**
     * Checks default allow all CORS settings
     *
     * @group CORSPreflight
     */
    public function testCORSAllowAll()
    {
        $corsConfig = Config::inst()->get(RESTfulAPI::class, 'cors');
        $requestHeaders = $this->getOPTIONSHeaders('GET', 'http://google.com');
        $response = Director::test('api/ApiTestBook/1', null, null, 'OPTIONS', null, $requestHeaders);
        $responseHeaders = $response->getHeaders();

        $this->assertEquals(
            $requestHeaders['Origin'],
            $responseHeaders['Access-Control-Allow-Origin'],
            'CORS headers should have same ORIGIN'
        );

        $this->assertEquals(
            $corsConfig['Allow-Methods'],
            $responseHeaders['Access-Control-Allow-Methods'],
            'CORS headers should have same METHOD'
        );

        $this->assertEquals(
            $requestHeaders['Access-Control-Request-Headers'],
            $responseHeaders['Access-Control-Allow-Headers'],
            'CORS headers should have same ALLOWED HEADERS'
        );

        $this->assertEquals(
            $corsConfig['Max-Age'],
            $responseHeaders['Access-Control-Max-Age'],
            'CORS headers should have same MAX AGE'
        );
    }

    /**
     * Checks CORS only allow HTTP methods specify in config
     */
    public function testCORSHTTPMethodFiltering()
    {
        Config::modify()->set(RESTfulAPI::class, 'cors', array(
            'Enabled' => true,
            'Allow-Origin' => '*',
            'Allow-Headers' => '*',
            'Allow-Methods' => 'GET',
            'Max-Age' => 86400,
        ));

        // Seding GET request, GET should be allowed
        $requestHeaders = $this->getRequestHeaders();
        $response = Director::test('api/ApiTestBook/1', null, null, 'GET', null, $requestHeaders);
        $responseHeaders = $response->getHeaders();

        $this->assertEquals(
            'GET',
            $responseHeaders['access-control-allow-methods'],
            'Only HTTP GET method should be allowed in access-control-allow-methods HEADER'
        );

        // Seding POST request, only GET should be allowed
        $response = Director::test('api/ApiTestBook/1', null, null, 'POST', null, $requestHeaders);
        $responseHeaders = $response->getHeaders();

        $this->assertEquals(
            'GET',
            $responseHeaders['access-control-allow-methods'],
            'Only HTTP GET method should be allowed in access-control-allow-methods HEADER'
        );
    }

    /* **********************************************************************
     * API REQUESTS
     * */

    public function testFullBasicAPIRequest()
    {
        Config::modify()->set(RESTfulAPI::class, 'authentication_policy', false);
        Config::modify()->set(RESTfulAPI::class, 'access_control_policy', 'ACL_CHECK_CONFIG_ONLY');
        Config::modify()->set(ApiTestAuthor::class, 'api_access', true);

        Config::modify()->set(RESTfulAPI::class, 'dependencies', [
            'authenticator' => null,
            'authority' => null,
            'queryHandler' => '%$Colymba\RESTfulAPI\QueryHandlers\DefaultQueryHandler',
            'serializer' => '%$Colymba\RESTfulAPI\Serializers\DefaultSerializer',
        ]);
        Config::modify()->set(
            DefaultQueryHandler::class,
            'dependencies',
            ['deSerializer' => '%$Colymba\RESTfulAPI\Serializers\DefaultDeSerializer']
        );
        Config::modify()->set(
            DefaultQueryHandler::class,
            'models',
            [
                'apitestauthor' => 'Colymba\RESTfulAPI\Tests\Fixtures\ApiTestAuthor',
                'apitestbook' => 'Colymba\RESTfulAPI\Tests\Fixtures\ApiTestBook',
            ]
        );

        $response = Director::test('api/apitestauthor/1', null, null, 'GET');

        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "API request for existing record should resolve"
        );

        $json = json_decode($response->getBody());
        $this->assertEquals(
            JSON_ERROR_NONE,
            json_last_error(),
            "API request should return valid JSON"
        );
    }
}
