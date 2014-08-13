## Google OAuth2 Token Bundle ##

### Google Configuration ###

- Enable API access in the Google Admin panel. (Security -> API Reference)

- Create an admin role and account that will be used for API access and assign the following permissions. You will use
  this account later to authorize the oauth2 token for API access.
  - Create a user defined admin role (e.g., account_manager) and assign Admin API privileges:
    - Organizational Units: Read, Update
    - Users: Create, Read, Update [Delete is optional, good for development environment)
    - Groups: Create, Read, Update
    - You may assign Admin Console Privileges (good idea for development environment), or keep the account restricted to just API access.
  - Create a new admin user (e.g., api_account) and assign the role created above.

- Create an application org and account to "host" the OAuth2 application.
  - Create an "application" org (e.g., App Developers).
  - Enable Google App Engine Admin console for your application org defined above. 
    - Google Dashboard -> More Controls (bottom of page) -> More Google Apps -> Google App Engine Admin Console
    - Click the dropdown menu after "OFF" and select "ON for some organizations".
    - Select the application org created above and click Override under settings (vs. inherited).
    - Toggle the "OFF" switch to "ON" to enable the console for users in that org.
  - Create an application account (e.g., application@your.google.domain) and assign to the application org above.

- Create a new project in the Google Developers Console.
  - Login to the Google Developers Console (https://console.developers.google.com/) with the application account 
    and create a new project. (e.g., penn-sas-provisioning)
  - Enable Admin SDK in your project (APIs & Auth -> APIs) and disable any other SDKs enabled by default.
  - Create a new client id (APIs & Auth -> Credentials ->Create new Client ID)
    - Select "web application" as the application type
    - Enter the authorized redirect uri: https://you-web-host/path/to/app.php/admin/token/oauth2callback
    - Note the generated client id and client secret. You will use these values in the symfony parameters.yml file.


### Symfony Configuration ###

- Add bundle to AppKernel.php
````
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new SAS\IRAD\GoogleOAuth2TokenBundle\GoogleOAuth2TokenBundle(),
            ...
````

- Update parameters.yml with your OAuth2 parameters:
````
    oauth_params:
        client_id:        ** client id generated above **
        client_secret:    ** client secret generated above ** 
        redirect_uri:     ** OAuth2 callback page: https://yourhost/path/app.php/admin/token/oauth2callback **

        refresh_token_file:   ** path for refresh token storage **
        access_token_file:    ** path for access token storage **

        scopes:           
            -                 https://www.googleapis.com/auth/admin.directory.orgunit
            -                 https://www.googleapis.com/auth/admin.directory.user
````

- Also in parameters.yml, update the admin users who may manage the oauth2 token:
````
    admin_users:
        ROLE_TOKEN_ADMIN: [ tokenAdmin1, tokenAdmin2, ... ]
        ROLE_LOG_ADMIN:   [ ... ]
````

- Update app/config/routing.yml for web access to token admin pages:
````
oauth2_token:
    resource: "@GoogleOAuth2TokenBundle/Controller"
    type:     annotation
    prefix:   /admin/token
````

- Navigate to https://yourhost/path/app.php/admin/token/ to generate an OAuth2 token for your application.
