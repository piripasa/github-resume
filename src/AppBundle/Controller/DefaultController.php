<?php

namespace AppBundle\Controller;

use AppBundle\Service\ResumeGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $username = $request->get('username', false);
        $theme = $request->get('theme', 1);

        $data = [];

        if ($username) {
            $generator = new ResumeGenerator($username);
            $data['profile'] = $generator->getProfile();

            if (!empty($data['profile'])) {
                $data['repositories'] = $generator->getRepositories();
                $data['languages'] = $generator->getLanguages($data['repositories']);
                $data['organizations'] = $generator->getOrganizations();
                $data['contributions'] = $generator->getContributions($generator->getIssues());
                $data['favourites'] = [];//$generator->getFavourites();

                if ($theme == 2)
                    return $this->render('resume-template.html.twig', $data);
            }
        }

        return $this->render('default/index.html.twig', $data);
    }

}
