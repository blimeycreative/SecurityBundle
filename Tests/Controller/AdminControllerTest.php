<?php

namespace Oxygen\SecurityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use JMS\SecurityExtraBundle\Security\Authentication\Token\RunAsUserToken;

class AdminControllerTest extends WebTestCase {

  private function getAuthenticatedUser() {
    $client = static::createClient();

    $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(session_name(), true));
    $token = new UsernamePasswordToken('user', null, /* the firewall name */'secured_area', array('ROLE_ADMIN'));
    self::$kernel->getContainer()->get('session')->set('_security_secured_area', serialize($token));

    return $client;
  }

  public function testIndex() {
    $client = $this->getAuthenticatedUser();

    $client->request('GET', '/admin/user');
    $crawler = $client->followRedirect(true);

    $this->assertTrue($crawler->filter('html:contains("Manage users")')->count() > 0);
  }

  public function testUserManagement() {
    $client = $this->getAuthenticatedUser();

    $client->request('GET', '/admin/user');
    $crawler = $client->followRedirect(true);
    
    /* ---- Create new user ---- */
    $link = $crawler->selectLink('Create')->link();
    $crawler = $client->click($link);
    $form = $crawler->selectButton('Save user')->form();
    $client->submit($form, array(
        'user[username]' => 'testUsertest',
        'user[password]' => 'test',
        'user[email]' => 'test@test.com',
        'user[active]' => '1',
        'user[role_objects][1]' => '1'
    ));
    $crawler = $client->followRedirect(true);
    $row = $crawler->filter('tr.row_testUsertest');
    $this->assertTrue($row->count() == 1);
    
    /* ---- Edit user ---- */
    $link = $row->selectLink('Edit')->link();
    $crawler = $client->click($link);
    $form = $crawler->selectButton('Save user')->form();
    $client->submit($form, array(
        'user[username]' => 'testUser',
        'user[email]' => 'test@test.com',
        'user[active]' => '1',
        'user[role_objects][1]' => '1'
    ));
    $crawler = $client->followRedirect(true);
    $row = $crawler->filter('tr.row_testUser');
    $this->assertTrue($row->count() == 1);
    
    /* ---- Delete user ---- */
    $form = $row->filter('td.actions_testUser')->selectButton('delete')->form();
    $client->submit($form);
    $crawler = $client->followRedirect(true);
    $this->assertTrue($crawler->filter('tr.row_testUser')->count() == 0);
  }

}
