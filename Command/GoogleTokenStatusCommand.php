<?php

namespace SAS\IRAD\GoogleOAuth2TokenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class GoogleTokenStatusCommand extends ContainerAwareCommand {
    
    protected function configure() {
        
        $this->setName('google:token-status')
            ->setDescription('Check the status of the Google OAuth2 token')
            ;
        
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        // okay we have a valid person and their info. get the gmail admin service
        $oauth2Client = $this->getContainer()->get('oauth2_client');
        
        if ( $oauth2Client->isAccessTokenValid() ) {
            $output->writeln("Google OAuth2 Access Token status: VALID");
            return;
        }

        $output->writeln("Google OAuth2 Access Token status: EXPIRED");
        $output->writeln("Requesting new access token...");
            
        $oauth2Client->refreshToken();
        if ( $oauth2Client->isAccessTokenValid() ) {
            $output->writeln("Refreshed Google OAuth2 Access Token status: VALID");
        } else {
            throw new \Exception("Unable to refresh access token");
        }
        
        
    }
    
    
   
}