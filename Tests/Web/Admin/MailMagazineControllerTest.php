<?php
/**
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\MailMagazine\Tests\Web\Admin;

use Plugin\MailMagazine\Tests\Web\MailMagazineCommon;

class MailMagazineControllerTest extends MailMagazineCommon
{
    /**
     * Test routing
     *
     */
    public function testRoutingMailMagazine()
    {
        $this->client->request('GET',
            $this->app->url('admin_mail_magazine')
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testMailMagazineSearch()
    {
        $MaiCustomer =  $this->createMailMagazineCustomer();

        $searchForm = $this->createSearchForm($MaiCustomer);
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine'),
            array('mail_magazine' => $searchForm)
        );
        $crawler->filter('#search_form table')->html();
        $this->assertTrue(true);
    }

    public function testSelect()
    {
        $MailTemplate = $this->createMagazineTemplate();

        $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine_select', array('id' => $MailTemplate->getId())),
            array('mail_magazine' => array(
                'template' => $MailTemplate->getId(),
                'subject'  => $MailTemplate->getSubject(),
                'body'     => $MailTemplate->getBody(),
                '_token'   => 'dummy',
            ))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testSelect_NotPost()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\BadRequestHttpException');

        $MailTemplate = $this->createMagazineTemplate();
        $this->client->request(
            'GET',
            $this->app->url('admin_mail_magazine_select', array('id' => $MailTemplate->getId())),
            array('mail_magazine' => array(
                'template' => $MailTemplate->getId(),
                'subject'  => $MailTemplate->getSubject(),
                'body'     => $MailTemplate->getBody(),
                '_token'   => 'dummy',
            ))
        );
    }

    public function testConfirm_InValid()
    {
        $MailTemplate = $this->createMagazineTemplate();

        $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine_confirm', array('id' => $MailTemplate->getId())),
            array('mail_magazine' => array(
                'template' => $MailTemplate->getId(),
                'subject'  => $MailTemplate->getSubject(),
                'body'     => $MailTemplate->getBody(),
                '_token'   => 'dummy',
            ))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testConfirm()
    {
        $MailTemplate = $this->createMagazineTemplate();

        $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine_confirm', array('id' => $MailTemplate->getId())),
            array('mail_magazine' => array(
                'id'       => $MailTemplate->getId(),
                'template' => $MailTemplate->getId(),
                'subject'  => $MailTemplate->getSubject(),
                'body'     => $MailTemplate->getBody(),
                '_token'   => 'dummy',
            ))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testCommit()
    {
        $this->initializeMailCatcher();
        $MailTemplate = $this->createMagazineTemplate();
        $MaiCustomer = $this->createMailMagazineCustomer();
        $searchForm = $this->createSearchForm($MaiCustomer);
        $searchForm['template'] = $MailTemplate->getId();
        $searchForm['subject']  = $MailTemplate->getSubject();
        $searchForm['body']     = $MailTemplate->getBody();

        $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine_commit', array('id' => $MailTemplate->getId())),
            array('mail_magazine' => $searchForm)
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_mail_magazine_history')));

//        $Messages = $this->getMailCatcherMessages();
//        $Message = $this->getMailCatcherMessage($Messages[0]->id);
//
//        $this->expected = $searchForm['subject'];
//        $this->actual = $Message->subject;
//        $this->verify();
        $this->cleanUpMailCatcherMessages();
    }

    public function testPagination()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->createMailMagazineCustomer();
        }
        $searchForm = array(
            '_token' => 'dummy',
            'sex' => array("1"),
            "multi" => "",
            "customer_status" => array(),
            "birth_month" => "",
            "birth_start" => "",
            "birth_end" => "",
            "pref" => "",
            "tel" => array(),
            "create_date_start" => "",
            "create_date_end" => "",
            "update_date_start" => "",
            "update_date_end" => "",
            "buy_total_start" => "",
            "buy_total_end" => "",
            "buy_times_start" => "",
            "buy_times_end" => "",
            "buy_product_code" => "",
            "last_buy_start" => "",
            "last_buy_end" => "",
        );
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_mail_magazine'),
            array('mail_magazine' => $searchForm)

        );
        $pageNumber = $crawler->filter('.box-title strong')->html();
        $this->assertRegexp('/件/', $pageNumber);

        //pagination
        $crawler = $this->client->request(
            'GET',
            $this->app->url('admin_mail_magazine').'?page_no=2'
        );

        //check result
        $pageNumber = $crawler->filter('.box-title strong')->html();
        $this->assertRegexp('/件/', $pageNumber);

        //check search condition
        $sexCheckbox = $crawler->filter('#mail_magazine_sex label')->html();
        $this->assertRegexp('/checked/', $sexCheckbox);
    }
}