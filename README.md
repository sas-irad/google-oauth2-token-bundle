## GMail OAuth2 Token Bundle ##

- Add bundle to AppKernel.php
````
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new SAS\IRAD\GmailOAuth2TokenBundle\GmailOAuth2TokenBundle(),
            ...
````


- Enable API access in the Google Admin panel. (Security -> bla bla)

- Create a role / account that will be used for API access and assign the following permissions.
    a. (check what role permissions we need)

- Create an "application" org (e.g., App Developers).

- Create an application user (e.g., application@your.google.domain).

- Enable Google App Engine Admin console for your application org defined above. 
    Google Dashboard -> More Controls (bottom of page) -> Other Google Services
    a. Override
    b. Switch on

- Login to the Google Developers Console and create a new project. (e.g., penn-sas-provisioning)
    https://console.developers.google.com/

- Enable Admin SDK in your project.

- Create client id for *web application*
    a. Update parameters.yml with client id and secret
    b. set oauth redirect url to match your app 
    c. Set path for token cache and refresh token storage in parameters.yml 

-

- Update parameters.yml with your account log database parameters:
````
    oauth_params:
        client_id:        ** client id generated above **
        client_secret:    ** client secret generated above ** 
        redirect_uri:     ** OAuth2 callback page: https://yourhost/path/app_dev.php/admin/token/oauth2callback **

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
    resource: "@OAuth2TokenBundle/Controller"
    type:     annotation
    prefix:   /admin/token
````