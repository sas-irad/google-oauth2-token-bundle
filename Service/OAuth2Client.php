<?php

namespace SAS\IRAD\GoogleOAuth2TokenBundle\Service;

use Google_Auth_Exception;
use Google_Client;


class OAuth2Client {
    
    private $oauth_params;
    private $tokenManager;
    
    public function __construct(OAuth2TokenManager $manager, $oauth_params) {

        // check parameters
        $params = array('client_id', 
                        'client_secret', 
                        'redirect_uri', 
                        'scopes');
        
        foreach ( $params as $param ) {
            if ( !isset($oauth_params[$param]) ) {
                throw new \Exception("Parameter oauth_params.$param must be defined.");
            }
        }
        
        $this->oauth_params = $oauth_params;
        $this->tokenManager = $manager;
        
        $this->client = new Google_Client();
        $this->client->setClientId($oauth_params['client_id']);
        $this->client->setClientSecret($oauth_params['client_secret']);
        $this->client->setRedirectUri($oauth_params['redirect_uri']);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->addScope($oauth_params['scopes']);
        
        $this->client->setAccessToken($this->tokenManager->getAccessToken());
    }
    
    /**
     * Return the google client for direct use in other Google classes
     */
    public function getGoogleClient() {
        return $this->client;
    }
    
    /**
     * Getter for oauth param
     */
    public function getClientId() {
        return $this->oauth_params['client_id'];
    }
    
    /**
     * Getter for oauth param
     */
    public function getClientSecret() {
        return $this->oauth_params['client_secret'];
    }
    
    /**
     * Getter for oauth param
     */
    public function getRedirectUri() {
        return $this->oauth_params['redirect_uri'];
    }
    
    /**
     * Getter for oauth param
     */
    public function getScopes() {
        return $this->oauth_params['scopes'];
    }
    

    /**
     * Wrapper for Google_Client createAuthUrl() method
     * @return url
     */
    public function createAuthUrl() {
        return $this->client->createAuthUrl();
    }    
    
    /**
     * Check to see if access token is valid. Refresh if not.
     */
    public function prepareAccessToken() {
        if ( !$this->isAccessTokenValid() ) {
            $this->refreshAccessToken();
        }
    }
    
    
    /**
     * Wrapper for Google_Client refreshToken() method. Pass arg $required=false if you don't
     * want a refresh failure to throw an exception. E.g., in the token admin pages, an invalid
     * token is okay since we may be generating a new token. But in scripts and web ui calls,
     * a failure should stop everything.
     * @param boolean $required
     * @return url
     */
    public function refreshAccessToken($required=true) {
    
        $tokenInfo = $this->tokenManager->getRefreshToken();
    
        if ( $tokenInfo && isset($tokenInfo['token']) && $tokenInfo['token'] ) {
             
            try {
                $this->client->refreshToken($tokenInfo['token']);
            } catch (Google_Auth_Exception $e) {
                if ( $required ) {
                    throw $e;
                }
            }
            // write new access token to cache file
            $this->tokenManager->saveAccessToken($this->client->getAccessToken());
        }
    }
    
    /**
     * Revoke our current refresh token
     * @return url
     */
    public function revokeRefreshToken() {
    
        $tokenInfo = $this->tokenManager->getRefreshToken();
    
        if ( $tokenInfo && isset($tokenInfo['token']) && $tokenInfo['token'] ) {
            $this->client->revokeToken($tokenInfo['token']);
            $this->tokenManager->deleteRefreshToken();
        }
    }
    
    
    /**
     * Test if an access token is valid (i.e., not timed out or revoked)
     * @return boolean
     */
    public function isAccessTokenValid() {
    
        $accessToken = $this->client->getAccessToken();
    
        if ( !$accessToken ) {
            return false;
        }
    
        if ( $this->client->isAccessTokenExpired() ) {
            return false;
        }
    
        // extract actual token from json string
        $accessToken = json_decode($accessToken, true);
        $token = $accessToken['access_token'];
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,"https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=$token");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
         
        if ( !$tokenInfo = json_decode($response, true) ) {
            throw new \Exception("Unable to decode tokeninfo response. Can't validate token");
        }
    
        // is this our token?
        if ( isset($tokenInfo['issued_to']) && $tokenInfo['issued_to'] == $this->oauth_params['client_id'] ) {
            return true;
        }
    
        return false;
    }
    
    
    /**
     * Validate a token returned from Google in OAuth2 callback. Resulting tokens are cached
     * @param string $code     The authentication code returned by Google OAuth2
     * @param string $username Log who generated this token
     * @throws \Exception
     */
    
    public function authenticate($code, $username) {
    
        try {
            $result = $this->client->authenticate($code);
        } catch (\Google_Auth_Exception $e) {
            throw new \Exception("Invalid code returned after OAuth2 authorization.");
        }
         
        $tokenInfo = json_decode($this->client->getAccessToken());
         
        // store refresh token separately
        $refreshToken = array("token"      => $tokenInfo->refresh_token,
                "created_by" => $username,
                "created_on" => $tokenInfo->created);
    
        $this->tokenManager->saveRefreshToken($refreshToken);
    
        // store remainder of token in token cache
        unset($tokenInfo->refresh_token);
        $this->tokenManager->saveAccessToken(json_encode($tokenInfo));
    }
    
    
}