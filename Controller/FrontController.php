<?php

namespace Oxygen\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Oxygen\SecurityBundle\Form\UserType;
use Oxygen\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/account")
 */
class FrontController extends Controller {

  /**
   * @Route("/register", name="register")
   * @Template
   */
  public function registerAction() {
    $user = new User();
    $form = $this->createForm(new UserType('front', 'new'), $user);
    if ($this->getRequest()->getMethod() == 'POST') {
      $form->bindRequest($this->getRequest());
      if ($form->isValid()) {
        $user->setActive(0);
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($password);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $em->flush();
        $message = \Swift_Message::newInstance()
                ->setSubject('Account confirmation')
                ->setTo($user->getEmail())
                        ->setFrom('test@test.com')
                ->setBody($this->renderView('OxygenSecurityBundle:Email:register.html.twig', array('user'=>$user)),'text/html');
        $this->get('mailer')->send($message);
        $this->get('session')->setFlash('notice', 'Thank you, you must now confirm your account');

        return new RedirectResponse($this->generateUrl('register_thankyou'));
      }
    }
    return array('form'=>$form->createView());
  }
  
  /**
   * @Route("/register/thank-you", name="register_thankyou")
   * @Template
   */
  public function thankyouAction(){
    return array();
  }

}
