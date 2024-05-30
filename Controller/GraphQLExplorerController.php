<?php
/**
 * Date: 31.08.16
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\GraphQLBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GraphQLExplorerController extends AbstractController
{
    /**
     * @Route("/graphql/explorer")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function explorerAction()
    {
        $response = $this->render('@GraphQLBundle/Feature/explorer.html.twig', [
            'graphQLUrl' => $this->generateUrl('youshido_graphql_graphql_default'),
            'tokenHeader' => 'access-token'
        ]);

        $date = \DateTime::createFromFormat('U', strtotime('tomorrow'), new \DateTimeZone('UTC'));
        $response->setExpires($date);
        $response->setPublic();

        return $response;
    }
}
