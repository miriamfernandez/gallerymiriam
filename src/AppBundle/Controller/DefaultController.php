<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Item;
use AppBundle\Form\ItemType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/{_locale}/", name="homepage"), defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"}
     */
    public function indexAction()
    {
//        $this->get('translator')->setLocale('es');
        return $this->render('default/index.html.twig', array());
    }

    /**
     * @Route("/{_locale}/contact", name="contact"), defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"}
     */

    public function contactAction(Request $request)
    {
        $form = $this->createFormBuilder()
                     ->add('name', 'text')
                     ->add('email', 'email')
                     ->add('subject', 'text')
                     ->add('message', 'textarea')
                     ->add('save', 'submit', array('label' => 'Send'))
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = \Swift_Message::newInstance()
                                     ->setSubject($form->get('subject')->getData())
                                     ->setFrom($form->get('email')->getData())
                                     ->setTo('mfernandez@summa.es')
                                     ->setContentType("text/plain")
                                     ->setBody(
                                         $form->get('message')->getData()
                                     );

            $this->get('mailer')->send($message);
            $request->getSession()->getFlashBag()->add('success', 'Your email has been sent! Thanks!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/contact.html.twig', array('form' => $form->createView()));
    }
}
