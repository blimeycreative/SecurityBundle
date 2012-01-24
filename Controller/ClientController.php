<?php

namespace Oxygen\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Oxygen\SecurityBundle\Form\UserType;
use Oxygen\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @Route("/account")
 */
class ClientController extends Controller {

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
        $role = $this->getDoctrine()->getRepository('OxygenSecurityBundle:Role')->findOneByRole('ROLE_USER');
        $user->setActive(0);
        $user->addRole($role);
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
                ->setBody($this->renderView('OxygenSecurityBundle:Email:register.html.twig', array('user' => $user)), 'text/html');
        $this->get('mailer')->send($message);
        $this->get('session')->setFlash('notice', 'Thank you, you must now confirm your account');
        return new RedirectResponse($this->generateUrl('register_thankyou'));
      }
    }
    return array('form' => $form->createView());
  }

  /**
   * @Route("/register/thank-you", name="register_thankyou")
   * @Template
   */
  public function thankyouAction() {
    return array();
  }

  /**
   * @Route("/confirm/{id}/{token}", name="register_confirm")
   * @Template
   */
  public function confirmAction($id, $token) {
    $user = $this->getDoctrine()
            ->getRepository('OxygenSecurityBundle:User')
            ->findOneBy(array('id' => $id, 'token' => $token));
    if (!$user)
      throw $this->createNotFoundException("Sorry this request is not valid, if you followed an email link please contact the site administrator");
    $user->setActive(1);
    $em = $this->getDoctrine()->getEntityManager();
    $em->persist($user);
    $em->flush();
    $token = new UsernamePasswordToken($user, null, $this->container->getParameter('security.firewall.name'), $user->getRoles());
    $this->get('security.context')->setToken($token);
    $this->get('session')->setFlash('notice','Your account has been confirmed and you are now logged in');
    return new RedirectResponse($this->generateUrl('account_show'));
  }

  /**
   * @Route("/", name="account_show")
   * @Secure(roles="ROLE_USER")
   * @Template
   */
  public function showAction() {
    $user = $this->get('security.context')->getToken()->getUser();
    if (!$user)
      throw $this->createNotFoundException('User account not found');
    return array('user' => $user);
  }

}
