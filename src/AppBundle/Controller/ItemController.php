<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 13/06/2016
 * Time: 16:15
 */

namespace AppBundle\Controller;

use AppBundle\Event\ItemEvent;
use AppBundle\Event\ItemsListener;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Item;
use AppBundle\Form\ItemType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class ItemController extends Controller
{
    /**
     * @Route("/{_locale}/items/", name="gallery", defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"})
     *
     */
    public function indexAction(Request $request)
    {
        $items = $this->getDoctrine()
                      ->getRepository('AppBundle:Item')
                      ->findAll();

        return $this->render('item/index.html.twig', array('items' => $items));
    }

    /**
     * @Route("/{_locale}/items/list", name="items_list", defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"})
     *
     */
    public function listAction(Request $request)
    {
        $items = $this->getDoctrine()
                      ->getRepository('AppBundle:Item')
                      ->findAll();

        return $this->render('item/list.html.twig', array('items' => $items));
    }

    /**
     * @Route("/{_locale}/items/new", name="item_new", defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"})
     */
    public function newAction(Request $request)
    {
        $item = new Item();
        $form = $form = $this->createForm(new ItemType(), $item);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->get('app.upload_image')->upload($item);
                $this->get('event_dispatcher')->dispatch('item.create', new ItemEvent($item));

                return $this->redirect($this->generateUrl('items_list'));
            }
        }

        return $this->render('item/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{_locale}/items/edit/{item_id}", defaults={"item_id": 1, "_locale" = "es"}, name="item_edit", requirements={"_locale" = "en|es"})
     */
    public function editAction($item_id, Request $request)
    {
        $item = $this->getDoctrine()
                     ->getRepository('AppBundle:Item')
                     ->find($item_id);

        $form = $this->createForm(new ItemType(), $item);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->get('app.upload_image')->upload($item);
                $this->get('event_dispatcher')->dispatch('item.update', new ItemEvent($item));

                return $this->redirect($this->generateUrl('items_list'));
            }
        }

        return $this->render('item/edit.html.twig', array('form' => $form->createView()));
    }

    /**
     *
     * @Route("/{_locale}/items/delete/{item_id}", name="item_delete", defaults={"_locale" = "es"}, requirements={"_locale" = "en|es"})
     */
    public function deleteAction($item_id)
    {
        $item = $this->getDoctrine()->getRepository('AppBundle:Item')->find($item_id);
        if (!$item) {
            throw $this->createNotFoundException('No user found for id ' . $item_id);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($item);

        $this->get('event_dispatcher')->dispatch('item.delete', new ItemEvent($item));

        $em->flush();

        $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findAll();

        return $this->render('item/list.html.twig', array('items' => $items));
    }
}