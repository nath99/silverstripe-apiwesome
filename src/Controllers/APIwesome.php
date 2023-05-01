<?php

namespace nglasl\APIwesome\Controllers;

use SimpleXMLElement;
use nglasl\APIwesome\Models\APIwesomeToken;
use nglasl\APIwesome\Services\APIwesomeService;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 *	Passes the current request over to the APIwesomeService.
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class APIwesome extends Controller
{

    public APIwesomeService $service;

    private static $dependencies = array(
        'service' => '%$' . APIwesomeService::class
    );

    private static $allowed_actions = array(
        'regenerateToken',
        'retrieve'
    );

    /**
     *	Reject a direct APIwesome request.
     */

    public function index()
    {

        return $this->httpError(404);
    }

    /**
     *	Display an error page on invalid request.
     *
     *	@parameter <{ERROR_CODE}> integer
     *	@parameter <{ERROR_MESSAGE}> string
     */

    public function httpError($code, $message = null)
    {

        // Determine the error page for the given status code.

        $errorPages = ErrorPage::get()->filter('ErrorCode', $code);
        $errorPage = ErrorPage::get_content_for_errorcode($code);

        // Allow extension customisation.

        $this->extend('updateErrorPages', $errorPages);

        // Retrieve the error page response.
        if ($errorPage) {
            $response = new HTTPResponse();
            $response->setStatusCode($code);
            $response->setBody($errorPage);
            throw new HTTPResponse_Exception($response, $code);
        } else {
            return parent::httpError($code, $message);
        }
    }

    /**
     *	Attempt to regenerate the current security token.
     */

    public function regenerateToken(HTTPRequest $request)
    {

        // Restrict this functionality to administrators.

        $user = Security::getCurrentUser()->ID;
        if (Permission::checkMember($user, 'ADMIN')) {

            // Attempt to create a random hash.

            $regeneration = $this->service->generateHash();
            if ($regeneration) {

                // Instantiate the new security token.

                $token = APIwesomeToken::create();
                $token->Hash = $regeneration['hash'];
                $token->AdministratorID = $user;
                $token->write();

                // Temporarily use the session to display the new security token key.
                $request->getSession()->set(APIwesomeToken::class, "{$regeneration['key']}:{$regeneration['salt']}");
            } else {

                // Log the failed security token regeneration.

                Injector::inst()->get(LoggerInterface::class)->error('APIwesome security token regeneration failed.');
                $request->getSession()->set(APIwesomeToken::class, -1);
            }

            // Determine where the request came from.

            $from = $this->getRequest()->getVar('from');
            $redirect = $from ? $from : 'admin/json-xml/';
            return $this->redirect($redirect);
        } else {
            return $this->httpError(404);
        }
    }

    /**
     *
     *	Retrieve the appropriate JSON/XML output of a specified data object type, with optional filters parsed from the GET request.
     *
     *	@URLparameter <{DATA_OBJECT_NAME}> string
     *	@URLparameter <{OUTPUT_TYPE}> string
     *	@URLfilter <{LIMIT}> integer
     *	@URLfilter <{SORT}> string
     *	@URLfilters <{FILTERS}> string
     *	@return JSON/XML
     *
     *	EXAMPLE JSON:		<{WEBSITE}>/apiwesome/retrieve/<data-object-name>/json
     *	EXAMPLE XML:		<{WEBSITE}>/apiwesome/retrieve/<data-object-name>/xml
     *	EXAMPLE FILTERS:	<{WEBSITE}>/apiwesome/retrieve/<data-object-name>/xml?limit=5&sort=Attribute,ORDER&filter1=value&filter2=value
     *
     */

    public function retrieve()
    {

        $parameters = $this->getRequest()->allParams();

        // Pass the current request parameters over to the APIwesomeService where valid.

        if ($parameters['ID'] && $parameters['OtherID'] && ($validation = $this->validate($parameters['OtherID']))) {
            if (is_string($validation)) {
                return $validation;
            }

            // Retrieve the specified data object type JSON/XML.

            $filters = $this->getRequest()->getVars();
            unset($filters['url'], $filters['token'], $filters['limit'], $filters['sort']);

            //retrieve($class, $output, $limit = null, $sort = null, $filters = null)

            return $this->service->retrieve(
                str_replace('-', '', $parameters['ID']),
                $parameters['OtherID'],
                $this->getRequest()->getVar('limit'),
                explode(',', $this->getRequest()->getVar('sort')),
                $filters
            );
        } else {
            return $this->httpError(404);
        }
    }

    /**
     *	Determine whether the request token matches the current security token.
     *
     *	@parameter <{OUTPUT_TYPE}> string
     *	@return boolean/JSON/XML
     */

    public function validate($output)
    {

        $validation = $this->service->validateToken($this->getRequest()->getVar('token'));
        switch ($validation) {
            case APIwesomeService::VALID:

                // The token matches the current security token.

                return true;
            case APIwesomeService::INVALID:

                // The token does not match a security token.

                return false;
            case APIwesomeService::EXPIRED:

                // The token matches a previous security token.

                $output = strtoupper($output);
                if ($output === 'JSON') {
                    $this->getResponse()->addHeader('Content-Type', 'application/json');

                    // JSON_PRETTY_PRINT.

                    return json_encode(array(
                        'APIwesome' => array(
                            'Count' => 0,
                            'DataObjects' => array(
                                'Expired' => true
                            )
                        )
                    ), 128);
                } elseif ($output === 'XML') {
                    $this->getResponse()->addHeader('Content-Type', 'application/xml');
                    $XML = new SimpleXMLElement('<APIwesome/>');
                    $XML->addChild('Count', 0);
                    $objectsXML = $XML->addChild('DataObjects');
                    $objectsXML->addChild('Expired', true);
                    return $XML->asXML();
                }
                break;

            default:
                // Something went wrong with validation
                throw new \Error("Invalid APIwesomeService validation response");
        }
    }
}
