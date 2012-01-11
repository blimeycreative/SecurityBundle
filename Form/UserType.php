<?php

namespace Oxygen\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserType extends AbstractType {
  
  public $new;
  
  public function __construct($new = false){
    $this->new = $new;
  }

  public function buildForm(FormBuilder $builder, array $options) {
    $builder->add('username')
            ->add('active')
            ->add('email', 'email')
            ->add('role_objects', 'entity', array(
                'class' => 'OxygenSecurityBundle:Role',
                'expanded' => true,
                'multiple' => true
            ));
    if($this->new)
      $builder->add('password', 'password');
  }

  public function getName() {
    return 'user';
  }

  public function getDefaultOptions(array $options) {
    return array('data_class' => 'Oxygen\SecurityBundle\Entity\User');
  }

}
