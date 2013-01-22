<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Stfalcon\Bundle\PageBundle\Entity\Page;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Fixtures for the pages
 */
class LoadPagesData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $page = new Page();
        $page->setSlug('contacts');
        $text = '<!-- Orgs contacts page content -->
                <div class="contacts-page">

                    <div class="contacts-page-email">
                        <h3>Электронная почта:</h3>
                        <a href="mailto:orgs@fwdays.com">orgs@fwdays.com</a>
                        <div class="contacts-page-email-hint">предпочтительный способ связи</div>
                    </div>

                    <ul class="contacts-page-social">
                        <li><a href="http://frameworksdays.com/rss"><img src="/images/img-contacts-page-rss.png" width="92" height="41" alt=""></a></li>
                        <li><a href="http://www.facebook.com/fwdays"><img src="/images/img-contacts-page-facebook.png" width="92" height="41" alt=""></a></li>
                        <li><a href="http://twitter.com/fwdays"><img src="/images/img-contacts-page-twitter.png" width="92" height="41" alt=""></a></li>
                    </ul>

                    <div class="contacts-page-orgs">
                        <h3>Организаторы:</h3>
                        <div class="contacts-page-orgs-hint">Время звонков с 9:00 – 18:00</div>

                        <ul class="contacts-page-orgs-list">
                            <li class="vcard">
                                <div class="photo"><img src="/images/img-contacts-page-amahomet.jpg" width="60" height="60" alt=""></div>
                                <div class="contacts">
                                    <div class="name">Александр Махомет</div>
                                    <div class="phone">+380 68 120-70-35</div>
                                </div>
                            </li>
                            <li class="vcard">
                                <div class="photo"><img src="/images/img-contacts-page-stanasiychuk.jpg" width="60" height="60" alt=""></div>
                                <div class="contacts">
                                    <div class="name">Степан Танасийчук</div>
                                    <div class="phone">+380 97 874-03-42</div>
                                </div>
                            </li>
                            <li class="vcard">
                                <div class="photo"><img src="/images/img-contacts-page-emakedon.jpg" width="60" height="60" alt=""></div>
                                <div class="contacts">
                                    <div class="name">Евгений Македон</div>
                                    <div class="phone">+380 97 785-19-51</div>
                                </div>
                            </li>
                            <li class="vcard">
                                <div class="photo"><img src="/images/img-contacts-page-ibozhyk.jpg" width="60" height="60" alt=""></div>
                                <div class="contacts">
                                    <div class="name">Ирина Божик</div>
                                    <div class="phone">+380 67 999-5-888</div>
                                    <div class="email"><a href="mailto:iryna.bozhyk@fwdays.com">iryna.bozhyk@fwdays.com</a></div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>';
        $page->setText($text);
        $page->setTitle('Контакты');
        $manager->persist($page);

        $manager->flush();
    }
}
