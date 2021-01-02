<?php

require_once __DIR__ . '/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem(__DIR__ . '/views');
$twig = new Twig_Environment($loader);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->overload();

$state = 'applicationState';
$request = $_POST;

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($twig, $state, $request) {

        $r->get('/', function () use ($twig) {
            echo $twig->render('index.twig', [
                'authenticated' => isAuthenticated(),
                'profile' => getProfile()
            ]);
        });

        $r->get('/okta/okta-hosted-login/', function () use ($twig) {
            echo $twig->render('index.twig', [
                'authenticated' => isAuthenticated(),
                'profile' => getProfile()
            ]);
        });

        $r->get('/okta/okta-hosted-login/index.php', function () use ($twig) {
            echo $twig->render('index.twig', [
                'authenticated' => isAuthenticated(),
                'profile' => getProfile()
            ]);
        });

        $r->get('/okta/okta-hosted-login/index.php/login', function () use ($state) {
            $query = http_build_query([
                'client_id' => getenv('CLIENT_ID'),
                'response_type' => 'code',
                'response_mode' => 'query',
                'scope' => 'openid profile',
                'redirect_uri' => 'http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback',
                'state' => $state
            ]);

            header('Location: ' . getenv("ISSUER") . '/v1/authorize?' . $query);
        });
        $r->get('/okta/okta-hosted-login/index.php/fblogin', function () use ($state) {

            $query = http_build_query([
                'idp' => getenv('FB_IDP'),
                'client_id' => getenv('CLIENT_ID'),
                'response_type' => 'code',
                'response_mode' => 'query',
                'scope' => 'openid profile',
                'redirect_uri' => 'http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback',
                'state' => $state
            ]);

            header('Location: ' . getenv("ISSUER") . '/v1/authorize?' . $query);
        });
        $r->get('/okta/okta-hosted-login/index.php/googlelogin', function () use ($state) {

            $query = http_build_query([
                'idp' => getenv('GOOGLE_IDP'),
                'client_id' => getenv('CLIENT_ID'),
                'response_type' => 'code',
                'response_mode' => 'query',
                'scope' => 'openid profile',
                'redirect_uri' => 'http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback',
                'state' => $state
            ]);

            header('Location: ' . getenv("ISSUER") . '/v1/authorize?' . $query);
        });
        $r->get('/okta/okta-hosted-login/index.php/linkedinlogin', function () use ($state) {

            $query = http_build_query([
                'idp' => getenv('LINKEDIN_IDP'),
                'client_id' => getenv('CLIENT_ID'),
                'response_type' => 'code',
                'response_mode' => 'query',
                'scope' => 'r_liteprofile r_emailaddress w_member_social',
                'redirect_uri' => 'http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback',
                'state' => $state
            ]);

            header('Location: ' . getenv("ISSUER") . '/v1/authorize?' . $query);
        });

        $r->post('/okta/okta-hosted-login/index.php/register', function () use ($twig, $request) {

            $input = [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'password' => $request['password'],
                'repeat_password' => $request['repeat_password'],
            ];

            // local form validation
            $errorMessage = '';
            $errors = false;

            // validate field lengths
            if (strlen($input['first_name']) > 50) {
                $errorMessage .= "<br>'First Name' is too long (50 characters max)!";
                $errors = true;
            }
            if (strlen($input['last_name']) > 50) {
                $errorMessage .= "<br>'Last Name' is too long (50 characters max)!";
                $errors = true;
            }
            if (strlen($input['email']) > 100) {
                $errorMessage .= "<br>'Email' is too long (100 characters max)!";
                $errors = true;
            }
            if (strlen($input['password']) > 72) {
                $errorMessage .= "<br>'Password' is too long (72 characters max)!";
                $errors = true;
            }
            if (strlen($input['password']) < 8) {
                $errorMessage .= "<br>'Password' is too short (8 characters min)!";
                $errors = true;
            }

            // validate field contents
            if (empty($input['first_name'])) {
                $errorMessage .= "<br>'First Name' is required!";
                $errors = true;
            }
            if (empty($input['last_name'])) {
                $errorMessage .= "<br>'Last Name' is required!";
                $errors = true;
            }
            if (empty($input['email'])) {
                $errorMessage .= "<br>'Email' is required!";
                $errors = true;
            } else if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errorMessage .= "<br>Invalid email!";
                $errors = true;
            }
            if (empty($input['password'])) {
                $errorMessage .= "<br>'Password' is required!";
                $errors = true;
            }
            if (empty($input['repeat_password'])) {
                $errorMessage .= "<br>'Repeat Password' is required!";
                $errors = true;
            }
            if ($input['password'] !== $input['repeat_password']) {
                $errorMessage .= "<br>Passwords do not match!";
                $errors = true;
            }

            if ($errors) {

                $viewData = [
                    'input' => $input,
                    'errors' => $errors,
                    'errorMessage' => $errorMessage
                ];
                echo $twig->render('index.twig', $viewData);
                return true;
            }

            // if local validation passes, attempt to register the user
            // via the Okta API
            $data['profile'] = [
                'firstName' => $input['first_name'],
                'lastName' => $input['last_name'],
                'email' => $input['email'],
                'login' => $input['email']
            ];
            $data['credentials'] = [
                'password' => [
                    'value' => $input['password']
                ]
            ];
            $data = json_encode($data);

            $ch = curl_init(getenv('API_URL_BASE') . 'users');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'Authorization: SSWS ' . getenv('API_TOKEN')
            ]);

            $result = curl_exec($ch);
            $result = json_decode($result, true);
            if (isset($result['errorCode'])) {
                $viewData = [
                    'input' => $input,
                    'errors' => true,
                    'errorMessage' => '(Okta) ' . $result['errorCauses'][0]['errorSummary']
                ];
                echo $twig->render('index.twig', $viewData);
                return true;
            }
            echo $twig->render('index.twig', ['message' => 'You account is created Successfully! Click Login button to login to your account.']);
            return true;
        });

        $r->get('/okta/okta-hosted-login/index.php/authorization-code/callback', function () use ($state) {
            if (array_key_exists('state', $_REQUEST) && $_REQUEST['state'] !== $state) {
                throw new \Exception('State does not match.');
            }

            if (array_key_exists('code', $_REQUEST)) {
                $exchange = exchangeCode($_REQUEST['code']);
                if (!isset($exchange->access_token)) {
                    die('Could not exchange code for an access token');
                }

                if (verifyJwt($exchange->access_token) == false) {
                    die('Verification of JWT failed');
                }

                setcookie("access_token", "$exchange->access_token", time() + $exchange->expires_in, "/", false);
                header('Location: /okta/okta-hosted-login/index.php ');
            }

            die('An error during login has occurred');


        });

        $r->get('/okta/okta-hosted-login/index.php/profile', function () use ($twig) {
            if (!isAuthenticated()) {
                header('Location: /okta/okta-hosted-login/index.php');
            }
            echo $twig->render('profile.twig', [
                'authenticated' => isAuthenticated(),
                'profile' => getProfile()
            ]);
        });

        $r->post('/okta/okta-hosted-login/index.php/logout', function () {

            if(!isAuthenticated()) {
                header('Location: /okta/okta-hosted-login/index.php');
            }
            $profileDetail = getProfile();
            $userId = $profileDetail['uid'];

            $ch = curl_init(getenv('API_USER_URL') . $userId .'/sessions');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: SSWS'. getenv('API_TOKEN'),
            ]);

            $result = curl_exec($ch);
            $result = json_decode($result, true);

            setcookie("access_token", NULL, -1, "/", false);
            header('Location: /okta/okta-hosted-login/index.php');
        });

});

function exchangeCode($code) {
    $authHeaderSecret = base64_encode( getenv('CLIENT_ID') . ':' . getenv('CLIENT_SECRET') );
    $query = http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => 'http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback'
    ]);
    $headers = [
        'Authorization: Basic ' . $authHeaderSecret,
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Connection: close',
        'Content-Length: 0'
    ];
    $url = getenv("ISSUER").'/v1/token?' . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if(curl_error($ch)) {
        $httpcode = 500;
    }
    curl_close($ch);
    return json_decode($output);
}


$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
//var_dump($uri);die;
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        die('Not Found');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        die('Not Allowed');
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        print $handler($vars);
        break;
}
