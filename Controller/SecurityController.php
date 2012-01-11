<?php

namespace Oxygen\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Oxygen\SecurityBundle\Form\LoginType;

class SecurityController extends Controller {

  /**
   * @Route("/login", name="_login")
   * @Template()
   */
  public function loginAction() {
    $request = $this->getRequest();
    $session = $request->getSession();

    // get the login error if there is one
    if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
      $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    } else {
      $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
    }
    

    $form = $this->createForm(new loginType());
    $form->setData(array('_username'=>$session->get(SecurityContext::LAST_USERNAME)));
    return array(
        'form' => $form->createView(),
        'error' => $error,
    );
  }

  /**
   * @Route("/login_check", name="_login_check")
   */
  public function loginCheckAction() {
    
  }

  /**
   * @Route("/logout", name="_logout")
   */
  public function logoutAction() {
    
  }

}
