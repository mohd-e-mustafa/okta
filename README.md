# PHP + Okta Hosted Social Login (Facebook, Google, LinkedIn)
This project shows you how to use PHP to login to your application with an Okta Hosted Social Login page.  The login is achieved through the [authorization code flow](https://developer.okta.com/authentication-guide/implementing-authentication/auth-code), where the user is redirected to the Okta-Hosted login page.  After the user authenticates they are redirected back to the application with an access code that is then exchanged for an access token.

![start_page](https://user-images.githubusercontent.com/56065974/103461707-28a4c780-4d42-11eb-83ef-d1656fe0f3bf.png)


## Prerequisites

Before running this sample, you will need the following:

* An Okta Developer Account, you can sign up for one at https://developer.okta.com/signup/.
* An Okta Application, configured for Web mode. This is done from the Okta Developer Console and you can find instructions [here][OIDC WEB Setup Instructions].  When following the wizard, use the default properties.  They are are designed to work with our sample applications.
* Facebook developer account if you do not have you can find instructions [here](https://developer.okta.com/docs/guides/add-an-external-idp/facebook/create-an-app-at-idp/)
* Google Developer account if you do not have you can find instructions [here](https://developer.okta.com/docs/guides/add-an-external-idp/google/create-an-app-at-idp/).
* LinkedIn Developer account if you do not have you can find instructions [here](https://developer.okta.com/docs/guides/add-an-external-idp/linkedin/create-an-app-at-idp/).

## Running This Project

To run this application
Install dependencies:

```bash
cd okta-hosted-login

composer install

cp .env.dist .env
```

You also need to gather the following information from the Okta Developer Console:

- **CLIENT_ID** and **Client Secret** - These can be found on the "General" tab of the Web application that you created earlier in the Okta Developer Console.
- **CLIENT_SECRET** and **Client Secret** - These can be found on the "General" tab of the Web application that you created earlier in the Okta Developer Console.
- **ISSUER** - This is the URL of the authorization server that will perform authentication.  All Developer Accounts have a "default" authorization server.  The issuer is a combination of your Org URL (found in the upper right of the console home page) and `/oauth2/default`. For example, `https://dev-1234.oktapreview.com/oauth2/default`.
- **API_URL_BASE** - This is the API URL used for Registration and Login using OKTA. For example, `https://dev-1234.okta.com/api/v1/`.
- **API_TOKEN** - This is the API token which is generated on the Okta Developer Console.
- **API_USER_URL** - This is the API USER URL which is used for deleting the user sessions.
- **FB_IDP** - This is the ID of the Identity Provider for Facebook. Which is inside Okta developer Console. `User > Social & Identity Providers > fbIdentityProvider`
- **GOOGLE_IDP** - This is the ID of the Identity Provider for Google. Which is inside Okta developer Console. `User > Social & Identity Providers > googleIdentityProvider`.
- **LINKEDIN_IDP** - This is the ID of the Identity Provider for LinkedIn. Which is inside Okta developer Console. `User > Social & Identity Providers > linkedInIdentityProvider`.

Now that you have the information fill the`.env` with the information you gathered.

```bash
CLIENT_ID={yourClientId}
CLIENT_SECRET={yourClientSecret}
ISSUER=https://{yourOktaDomain}.com/oauth2/default


API_URL_BASE=https://{yourOktaDomain}/api/v1/
API_TOKEN=
API_USER_URL=https://{yourOktaDomain}/api/v1/users/

FB_IDP=
GOOGLE_IDP=
LINKEDIN_IDP=

```

Now start the app server:

```
composer server:start
```

Now navigate to http://localhost:8080 in your browser.

If you see a home page that prompts you to login, then things are working! Below should be the output

![start_page](https://user-images.githubusercontent.com/56065974/103461707-28a4c780-4d42-11eb-83ef-d1656fe0f3bf.png)


Clicking the **Log in** button will redirect you to the Okta hosted sign-in page.
Clicking the **Continue with Facebook** button will redirect you to the Facebook sign-in page.
Clicking the **Continue with Google** button will redirect you to the Google sign-in page.
Clicking the **Continue with LinkedIn** button will redirect you to the LinkedIn sign-in page.

**NOTE:** your OKTA app redirect routes should be as below:
- **Login redirect URIs** http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback
- **Loout redirect URIs** http://localhost/okta/okta-hosted-login/index.php
- **Initiate login URI** http://localhost/okta/okta-hosted-login/index.php/authorization-code/callback




[OIDC Web Setup Instructions]: https://developer.okta.com/authentication-guide/implementing-authentication/auth-code#1-setting-up-your-application
