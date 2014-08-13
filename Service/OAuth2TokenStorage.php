<?php

namespace SAS\IRAD\GoogleOAuth2TokenBundle\Service;

use SAS\IRAD\FileStorageBundle\Service\EncryptedFileStorageService;


class OAuth2TokenStorage {
    
    private $oauth_params;
    private $refreshTokenStorage;
    private $accessTokenStorage;
    
    /**
     * Constructor
     * @param array $oauth_params defined in app/config/parameters.yml
     */
    public function __construct(EncryptedFileStorageService $fileStorage, $oauth_params) {
        
        $params = array('client_id', 
                        'client_secret', 
                        'redirect_uri', 
                        'refresh_token_file', 
                        'access_token_file',
                        'scopes');
        
        foreach ( $params as $param ) {
            if ( !isset($oauth_params[$param]) ) {
                throw new \Exception("Parameter oauth_params.$param must be defined.");
            }
        }
        
        $this->oauth_params = $oauth_params;
        
        // initialize file storage
        $this->refreshTokenStorage = $fileStorage->init($oauth_params['refresh_token_file']);
        $this->accessTokenStorage  = $fileStorage->init($oauth_params['access_token_file']);
    }

    
    public function getClientId() {
        return $this->oauth_params['client_id'];
    }
    
    public function getClientSecret() {
        return $this->oauth_params['client_secret'];
    }
    
    public function getRedirectUri() {
        return $this->oauth_params['redirect_uri'];
    }
    
    public function getScopes() {
        return $this->oauth_params['scopes'];
    }
    
    
    /**
     * Save the access token as a string (already in json format)
     * @param string $data
     */
    public function saveAccessToken($data) {
        $this->accessTokenStorage->save($data);
    }
    
    /**
     * Encode refresh token data as json for storage
     * @param array $data
     */
    public function saveRefreshToken($data) {
        $this->refreshTokenStorage->save(json_encode($data));
    }

    /**
     * Return the access token json string
     * @return string
     */
    public function getAccessToken() {
        $tokenString = $this->accessTokenStorage->get();
        if ( !$tokenString ) {
            // return an "emtpy" access token
            $tokenString = json_encode(array(
               'access_token' => '',
               'expires_in'   => 0,
               'created'      => 0,
            ));
        }
        return $tokenString;
    }
    
    /**
     * Return array of info for the refresh token
     * @return array
     */
    public function getRefreshToken() {
        return json_decode($this->refreshTokenStorage->get(), true);
    }
    
    public function deleteRefreshToken() {
        $this->refreshTokenStorage->delete();
    }
}