<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * LoadEventData Class
 */
class LoadEventData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $event = (new Event())
            ->setName('Конференция JavaScript fwdays \'18')
            ->setSlug('javaScript-framework-day-2018')
            ->setBackgroundColor('#1B91CD')
            ->setDescription('JavaScript Frameworks Day 2018 - V международная конференция, посвященная популярным JavaScript фреймворкам.')
            ->setLogoFile($this->_generateUploadedFile('JS_big.svg'))
            ->setSmallLogoFile($this->_generateUploadedFile('JS_small.svg'))
            ->setLogo('JS_big.svg')
            ->setSmallLogo('JS_small.svg')
            ->setEmailBackgroundFile($this->_generateUploadedFile('bg-blue.png'))
            ->setPdfBackgroundFile($this->_generateUploadedFile('left-element.png'))
            ->setCity('Киев')
            ->setPlace('отель "Казацкий"')
            ->setAbout('<h3>Как прошла конференция</h3>
<iframe width="560" height="315" src="https://www.youtube.com/embed/0cNRmWrq_hw" frameborder="0" allowfullscreen></iframe>
<br />

<br /><br />


<table class="event-details">
   <tbody><tr>
       <td colspan="2">
           <div class="event-details-h">Место проведения:</div>
           <p>г. Киев, Конференц-центр отеля Ramada Encore, Столичное шоссе, 103<br />
               
                </p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Время:</div>
           <p>17 апреля 2016 года, 09:00 - 20:00</p>
       </td>
       <td>
           <div class="event-details-h">Длительность:</div>
           <p>1 полный день</p>
       </td>
   </tr>
 
<tr>
       <td class="price" colspan="2">
           <p><b>Стоимость участия:</b><br/>
Все билеты проданы.
<!--- <del>1 500 грн. (~$55) – первые 50 билетов (только через сайт)</del><br/>
<del>1 800 грн. (~$65) – следующие 100 билетов</del><br/>
2 000 грн. (~$73) – оставшиеся билеты
</p><br />
	 <p>20% cкидка для участников предыдущих конференций</p> --->
       </td>
   </tr>
     <tr>
       <td>
           <div class="event-details-h">Формат:</div>
           <p>Доклады в 3 потока, 1 поток воршопов и обсуждения в перерывах</p>
       </td>
       <td>
           <div class="event-details-h">Языки докладов:</div>
           <p>Русский, Украинский, Английский</p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Аудитория:</div>
           <p>Разработчики, архитекторы, менеджеры проектов из Украины и других стран</p>
       </td>
       <td>
           <div class="event-details-h">Потоки:</div>
           <p>4 потока</p>
       </td>
   </tr>
</tbody></table>

<br />
<br />
<br />')
            ->setDate(new \DateTime('2018-04-19 11:10', new \DateTimeZone('Europe/Kiev')))
            ->setDateEnd(new \DateTime('2018-04-19 20:15', new \DateTimeZone('Europe/Kiev')))
            ->setReceivePayments(true)
            ->setCost(100);
        $manager->persist($event);
        $this->addReference('event-jsday2018', $event);
        $manager->flush();

        $event = (new Event())
            ->setName('PHP Frameworks Day')
            ->setSlug('php-frameworks-day-2018')
            ->setBackgroundColor('#7586D2')
            ->setDescription('PHP frameworks day это конференция по современным PHP фреймворкам (Zend Framework 2, Symfony 2, Silex, Lithium и др.)')
            ->setLogoFile($this->_generateUploadedFile('PHP_big.svg'))
            ->setSmallLogoFile($this->_generateUploadedFile('PHP_small.svg'))
            ->setLogo('PHP_big.svg')
            ->setSmallLogo('PHP_small.svg')
            ->setEmailBackgroundFile($this->_generateUploadedFile('event-2.png'))
            ->setPdfBackgroundFile($this->_generateUploadedFile('left-element.png'))
            ->setCity('Киев')
            ->setPlace('Пока неизвестно')
            ->setAbout('<h3>Панельная дискуссия</h3>

<iframe width="560" height="315" src="https://www.youtube.com/embed/E2APEz7CSZY" frameborder="0" allowfullscreen></iframe><br />
<br />




<p>Традиционно, мы выбрали для вас актуальные и полезные темы докладов от продвинутых иностранных и украинских экспертов с большим практическим опытом работы. Вас ждут доклады в 2 потока, общение со спикерами, перерывы на вкусные кофе-брейки и питательный обед. В конце дня мы разыграем ценные призы от наших постоянных партнеров, а также пригласим всех на веселую афтепати, где сможем расслабиться, еще больше подружиться и пообщаться в неформальной обстановке.</p>

<p>PHP Frameworks Day уже в четвертый раз проводится в Киеве и собирает множество впечатлений и <a href="http://frameworksdays.com/event/php-frameworks-day-2014/page/feedback">отзывов</a> от участников.</p>

<br /><br />


<table class="event-details">
   <tbody><tr>
       <td colspan="2">
           <div class="event-details-h">Место проведения:</div>
           <p> г. Киев, Конгресс-Холл «Космополит», ул. Вадима Гетьмана, 6 (М Шулявская)<br />
               
                </p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Время:</div>
           <p>3 сентября, 2016 года, 09:00 - 19:00</p>
       </td>
       <td>
           <div class="event-details-h">Длительность:</div>
           <p>1 полный день</p>
       </td>
   </tr>
 
<tr>
       <td class="price" colspan="2">
           <p><b>Стоимость участия:</b><br/>

<p>Все билеты проданы, будет доступна бесплатная онлайн-трансляция.</p>
       </td>


   </tr>
     <tr>
       <td>
           <div class="event-details-h">Формат:</div>
           <p>Доклады в 2 потока, обсуждения в перерывах</p>
       </td>
       <td>
           <div class="event-details-h">Языки докладов:</div>
           <p>Русский, Украинский, Английский</p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Аудитория:</div>
           <p>Разработчики, архитекторы, менеджеры проектов из Украины и других стран</p>
       </td>
       <td>
           <div class="event-details-h">Потоки:</div>
           <p>2 потока</p>
       </td>
   </tr>
</tbody></table>

<br />
<br />
<br />')
            ->setActive(true)
            ->setDate((new \DateTime('now', new \DateTimeZone('Europe/Kiev')))->add(new \DateInterval('P1M')))
            ->setCost(100);
        $manager->persist($event);
        $this->addReference('event-phpday2018', $event);

        $event = (new Event())
            ->setName('Not Active Frameworks Day')
            ->setSlug('not-active-frameworks-day')
            ->setDescription('Это событие тестовое, но должно быть неактивным')
            ->setLogoFile($this->_generateUploadedFile('phpel_big.svg'))
            ->setSmallLogoFile($this->_generateUploadedFile('phpel_small.svg'))
            ->setLogo('phpel_big.svg')
            ->setSmallLogo('phpel_small.svg')
            ->setEmailBackgroundFile($this->_generateUploadedFile('bg-blue.png'))
            ->setPdfBackgroundFile($this->_generateUploadedFile('left-element.png'))
            ->setCity('Где-то там')
            ->setPlace('Пока неизвестно')
            ->setAbout('Описание события')
            ->setActive(false)
            ->setDate(new \DateTime('2017-04-02 10:30', new \DateTimeZone('Europe/Kiev')))
            ->setDateEnd(new \DateTime('2017-04-03 20:15', new \DateTimeZone('Europe/Kiev')))
            ->setCost(100);
        $manager->persist($event);
        $this->addReference('event-not-active', $event);
        $manager->flush();

        $event = (new Event())
            ->setName('Конференция Highload fwdays \'17')
            ->setSlug('Highload-frameworks-day-2017')
            ->setBackgroundColor('#00776F')
            ->setDescription('Конференция Highload fwdays \'17')
            ->setLogoFile($this->_generateUploadedFile('highload_big.svg'))
            ->setLogo('highload_big.svg')
            ->setSmallLogoFile($this->_generateUploadedFile('highload_small.svg'))
            ->setSmallLogo('highload_small.svg')
            ->setEmailBackgroundFile($this->_generateUploadedFile('bg-blue.png'))
            ->setPdfBackgroundFile($this->_generateUploadedFile('left-element.png'))
            ->setCity('Киев')
            ->setPlace('отель "Казацкий"')
            ->setAbout('
<p>Highload fwdays’17 - это конференция, посвященная разработке высоконагруженных технологичных проектов, а также работе с архитектурой и микросервисами, базами данных, машинному обучению, Big Data и не только.</p>

<p>Будут представлены доклады по направлениям:</p>
<ul>
<li>Architecture (Backend architecture (scalability), Microservices, Frontend, Testing)</li> 
<li>Data Science (Big Data, Machine Learning, AI)</li>
<li>DevOps</li>
<li>Databases (SQL, NoSQL, Storage Systems)</li>
</ul>


<table class="event-details">
   <tbody><tr>
       <td colspan="2">
           <div class="event-details-h">Место проведения:</div>
           <p>г. Киев, Конгресс-Холл «Космополит», ул. Вадима Гетьмана, 6 (М Шулявская)<br />
               
                </p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Время:</div>
           <p>14 октября 2017 года</p>
       </td>
       <td>
           <div class="event-details-h">Длительность:</div>
           <p>1 полный день</p>
       </td>
   </tr>
 
<tr>
       <td class="price" colspan="2">
           <p><b>Стоимость участия:</b><br/>
<del>2 600 грн. (~$100) – первые 50 билетов (только через сайт)</del><br/>
2 900 грн. (~$112) – следующие 300 билетов</del><br/>
3 500 грн. (~$134) – оставшиеся билеты 
</p><br />
	 <p>20% cкидка для участников предыдущих конференций</p> 
       </td>
   </tr>
     <tr>
       <td>
           <div class="event-details-h">Формат:</div>
           <p>Доклады в несколько потоков и обсуждения в перерывах</p>
       </td>
       <td>
           <div class="event-details-h">Языки докладов:</div>
           <p>Русский, Украинский, Английский</p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Аудитория:</div>
           <p>Разработчики, архитекторы, менеджеры проектов из Украины и других стран</p>
       </td>
       <td>
           <div class="event-details-h">Потоки:</div>
           <p>4</p>
       </td>
   </tr>
</tbody></table>


<!--<p>Сейчас мы формируем программу и ищем спикеров.</p><br />
<p>Если вы хотите выступить у нас, заполняйте заявку, мы сразу оповестим вас, что получили её и сколько нам нужно времени на рассмотрение.</p>
<center><h3><a href="https://docs.google.com/forms/d/e/1FAIpQLScpfK2kcV8wyglJFyAe5tUKC4LWdzNN7K06HzBRz2hqSjqVAQ/viewform">Сall for papers</a></h3></center>


<p>С нетерпение ждем ваших интересных докладов!</p><br />-->

<p> Присоединяйтесь к нам в <a href="https://www.facebook.com/events/486873021658319">Facebook</a> и <a href="https://t.me/highload_fwdays">Telegram</a>.</p>



<br /><br />')
            ->setDate(new \DateTime('2018-03-02', new \DateTimeZone('Europe/Kiev')))
            ->setCost(100);
        $manager->persist($event);
        $this->addReference('event-highload-day', $event);
        $manager->flush();

        $event = (new Event())
            ->setName('PHP Day')
            ->setSlug('php-day-2017')
            ->setBackgroundColor('#7586D2')
            ->setDescription('test description')
            ->setLogoFile($this->_generateUploadedFile('PHP_big.svg'))
            ->setSmallLogoFile($this->_generateUploadedFile('PHP_small.svg'))
            ->setLogo('PHP_big.svg')
            ->setSmallLogo('PHP_small.svg')
            ->setEmailBackgroundFile($this->_generateUploadedFile('bg-blue.png'))
            ->setPdfBackgroundFile($this->_generateUploadedFile('left-element.png'))
            ->setCity('Киев')
            ->setPlace('отель "Казацкий"')
            ->setAbout('<h2>Панельная дискуссия</h2>
<iframe width="560" height="315" src="https://www.youtube.com/embed/5CdSEyZmLbc" frameborder="0" allowfullscreen></iframe><br />

<p>Конференция PHP Frameworks Day — это актуальные и доступные  доклады от самых продвинутых php-разработчиков, возможность легко и  быстро разобраться с функционалом фреймворков, чтобы впоследствии  максимально эффективно использовать их в проектах. Это непринужденное общение, талантливые и перспективные в IT-сфере участники, это отличный повод завести новые контакты или обновить старые знакомства.</p>
<p><strong>PHP Frameworks Day проводится уже в третий раз. </strong> 
</p>


<br /><br />

<table class="event-details">
   <tbody><tr>
       <td colspan="2">
           <div class="event-details-h">Место проведения:</div>
           <p>г. Киев, Конференц-центр отеля Ramada Encore, Столичное шоссе, 103<br />
                        </p>
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Время:</div>
           <p>17 октября 2015, 09:00 - 19:00</p>
       </td>
       <td>
           <div class="event-details-h">Длительность:</div>
           <p>1 полный день</p>
       </td>
   </tr>
 <tr>
       <td class="price" colspan="2">
           <p><b>Стоимость участия:</b><br/>
<!--- <del>1 500 грн. (~$68) – первые 50 билетов (только через сайт)</del><br/>
1 800 грн. (~$82) – следующие 150 билетов<br/>
2 000 грн. (~$90) – оставшиеся билеты ---> 
</p><br /> 
	 <p>20% cкидка для участников предыдущих конференций</p>
       </td>
   </tr>
     <tr>
       <td>
           <div class="event-details-h">Формат:</div>
           <p>Доклады в 2 потока и обсуждения в перерывах</p>
       </td>
       <td>
           <div class="event-details-h">Языки докладов:</div>
<p>Украинский, Русский, Английский</p>           
       </td>
   </tr>
   <tr>
       <td>
           <div class="event-details-h">Аудитория:</div>
           <p>Разработчики, архитекторы, менеджеры проектов из Украины и стран ближнего зарубежья</p>
       </td>
       <td>
           <div class="event-details-h">Потоки:</div>
           <p>2 потока</p>
       </td>
   </tr>
</tbody></table>

<br />
<br />
<br />')
            ->setDate(new \DateTime('2017-12-19', new \DateTimeZone('Europe/Kiev')))
            ->setDate(new \DateTime('2017-12-20', new \DateTimeZone('Europe/Kiev')))
            ->setReceivePayments(true)
            ->setCost(1000);
        $manager->persist($event);
        $this->addReference('event-phpday2017', $event);

        $manager->flush();
    }

    /**
     * Generate UploadedFile object from local file. For VichUploader
     *
     * @param string $filename
     *
     * @return UploadedFile
     */
    private function _generateUploadedFile($filename)
    {
        $fullPath = realpath($this->getKernelDir().'/../web/assets/img/events/' . $filename);
        if ($fullPath) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'event');
            copy($fullPath, $tmpFile);
            return new UploadedFile($tmpFile,
                $filename, null, null, null, true
            );
        } else {
            return null;
        }
    }

    private function getKernelDir()
    {
        return $this->container->get('kernel')->getRootDir();
    }
}
