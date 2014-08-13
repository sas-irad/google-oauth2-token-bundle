<?php

namespace SAS\IRAD\GoogleOAuth2TokenBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DefaultController extends Controller {

    
    /**
     * @Route("/", name="tokenIndex")
     * @Template()
     */
    public function indexAction() {
    
        $user = $this->getUser();
        $oauth2Client = $this->get('oauth2_client');
        $tokenManager = $this->get('oauth2_token_manager');

        if ( !$oauth2Client->isAccessTokenValid() ) {
            $oauth2Client->refreshAccessToken(false);
        }
        
        
    	if ( $oauth2Client->isAccessTokenValid() ) {
    	    $token_status = "VALID";
    	    	
    	} else {
    	    $token_status = "INVALID";
    	    $customer_id  = "(set token first)";
    	}

	    // generate URL for user authorization
    	$oauth_auth_url = $oauth2Client->createAuthUrl();
         
         
        return array('oauth_auth_url'   => $oauth_auth_url,
                     'token_status'     => $token_status,
                     'scopes'           => $oauth2Client->getScopes(),
                     'refresh_token'    => $tokenManager->getRefreshToken());
    }    
    
    
    /**
     * @Route("/oauth2callback", name="oauth2callback")
     * @Template()
     */    
    public function oauth2CallbackAction(Request $request) {
    	
        $user = $this->getUser();
    	$code = $request->query->get('code');
    	
        $oauth2Client = $this->get('oauth2_client');
        $oauth2Client->authenticate($code, $user->getUsername());
    	
    	return array();
    }
    
    
    /**
     * @Route("/revokeRefreshToken", name="revokeRefreshToken")
     * @Template()
     */
    public function revokeRefreshTokenAction() {
         
        $oauth2Client = $this->get('oauth2_client');
        $oauth2Client->revokeRefreshToken();
         
        return array();
         
    }    
}
