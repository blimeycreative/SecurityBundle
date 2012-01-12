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
 * @Route("/admin/user")
 */
class AdminController extends Controller {

  /**
   * @Route("/new", name="new_user")
   * @Template
   */
  public function newAction() {
    $user = new User();
    $form = $this->createForm(new UserType('admin','new'), $user);
    if ($this->processForm($form, $user, true))
      return new RedirectResponse($this->generateUrl('show_users'));
    return array('form' => $form->createView());
  }

  /**
   * @Route("/edit/{id}", name="edit_user")
   * @Template
   */
  public function editAction($id) {
    $user = $this->getDoctrine()->getRepository('OxygenSecurityBundle:User')->find($id);
    if (!$user)
      throw $this->createNotFoundException('Sorry user not found');
    $form = $this->createForm(new UserType('admin','edit'), $user);
    if ($this->processForm($form, $user))
      return new RedirectResponse($this->generateUrl('show_users'));
    return array(
        'form' => $form->createView(),
        'user' => $user
    );
  }

  /**
   * @Route("/delete/{id}", name="delete_user")
   * @Template
   */
  public function deleteAction($id) {
    $em = $this->getDoctrine()->getEntityManager();
    $user = $em->getRepository('OxygenSecurityBundle:User')->find($this->getRequest()->get('id'));
    if (!$user)
      throw $this->createNotFoundException('Sorry user not found');
    $form = $this->createDeleteForm($this->getRequest()->get('id'));
    $form->bindRequest($this->getRequest());
    if ($form->isValid()) {
      $em->remove($user);
      $em->flush();
      $this->get('session')->setFlash('notice', 'User deleted');
    }
    return new RedirectResponse($this->generateUrl('show_users'));
  }

  /**
   * @Route("/{page}", name="show_users", defaults={"page" = 1})
   * @Template
   */
  public function indexAction() {
    $user_query = $this->getDoctrine()->getRepository('OxygenSecurityBundle:User')->createQueryBuilder('u');
    $pager = $this->container
            ->get('oxygen_pagination.factory')
            ->paginate($user_query, 2,'u')
            ->getPagination();
    $users = $pager->result->getResult();
    foreach ($users as $user) {
      $user->setDeleteForm($this->createDeleteForm($user->getId())->createView());
    }
    return array(
        'users' => $users,
        'data' => $pager->data,
        'pagination' => $pager->template
    );
  }

  private function processForm($form, $user, $new = false) {
    if ($this->getRequest()->getMethod() == 'POST') {
      $form->bindRequest($this->getRequest());
      if ($form->isValid()) {
        if ($new) {
          $factory = $this->get('security.encoder_factory');
          $encoder = $factory->getEncoder($user);
          $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
          $user->setPassword($password);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $em->flush();

        $this->get('session')->setFlash('success', 'User successfully saved');

        return true;
        ;
      }
    }
    return false;
  }

  private function createDeleteForm($id) {
    return $this->createFormBuilder(array('id' => $id))->add('id', 'hidden')->getForm();
  }

}
