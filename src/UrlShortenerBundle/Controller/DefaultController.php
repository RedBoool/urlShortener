<?php

namespace UrlShortenerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use UrlShortenerBundle\ESDocumentRepository\UrlShortenerRepository;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Method({"GET|POST"})
     */
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('url', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            print $data['url'];

            $uniqid = uniqid();
        }

        return $this->render('UrlShortenerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            'uniqid' => empty($uniqid) ?: $uniqid,
        ));
    }

    /**
     * @Route("/r/{redir_slug}", name="redir")
     * @Method({"GET"})
     */
    public function redirAction(Request $request, $redir_slug)
    {
        /** @var UrlShortenerRepository $urlShortenerRepository */
        $urlShortenerRepository = $this->container->get('es_document_repository.url_shortener');

        $resultSet = $urlShortenerRepository->getUrlBySlug($redir_slug, 0, 2);

        $nbResult = $resultSet->count();
        if ($nbResult === 0) {
            throw $this->createNotFoundException('No url found');
        } elseif($nbResult >= 2) {
            throw \Exception('Found more than one url');
        }

        $result = $resultSet->getResults();
        $url = $result[0]->getSource()['url'];

        return $this->redirect($url);
    }
}
