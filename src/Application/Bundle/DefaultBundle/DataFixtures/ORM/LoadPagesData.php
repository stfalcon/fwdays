<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Stfalcon\Bundle\EventBundle\Entity\Page;
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
        $page->setTitle('О Frameworks Days');
        $page->setSlug('about');
        $page->setText('<p><h3>Наша цель:</h3>
 проводить качественные мероприятия для разработчиков, менеджеров проектов и всех, кто связан с IT.</p>
 
<p>Под брендом Frameworks Days начиная с 2011 года мы организовываем мероприятия различного масштаба и формата.
 
<p><h3>Мы проводим:</h3>
<ul>
	<li><h4>Ежегодные конференции по разным языкам программирования и фреймворкам:</h4>
		<ul>
			<li>JavaScript, PHP, Java, функциональные языки программирования.</li>
			<li>Angular, React, .NET, Zend etc.</li><br />

				Мы организовываем масштабные конференции в удобных залах Киева, приглашаем лучших украинских экспертов, а также привозим иностранных специалистов в разных областях (авторов фреймворков, core contributors и не только), тщательно отбираем в программу только полезные темы докладов, заботимся о развлекательной и неформальной части конференции и в целом создаем комфортную атмосферу для вас.<br>
				<strong>Формат:</strong> 1-4 потоков докладов, перерывы на общение и перекус между докладами, активности для отдыха. <br>
				<strong>Количество участников:</strong> 200-500 <br><br />

		</ul>
	</li>
	 
	<li><h4>Ежемесячные IT Субботы: небольшие встречи по техническим и не техническим темам:</h4>
		<ul>
			<li>GameDev, Mobile (Android, iOS), Ruby.</li>
			<li>Project Management, Design.</li><br />

			<strong>Формат:</strong> 2-3 доклада, перерывы на чай/кофе, время на общение и обсуждение услышанного.<br>
			<strong>Количество участников:</strong> 30-100 <br>
		</ul>
	</li>
	 
	<li><h4>Однодневные мастер-классы, где участники могут научиться чему-то полезному. <br>
	Темы мастер-классов, которые мы уже провели:</h4>
		<ul>
			<li>ReactJS, EmberJS, Docker</li>
		</ul>
	</li><br />

	 
	<li><h4>Вебинары: 2-3-часовые онлайн воркшопы от экспертов на различные технические и не технические темы.</h4></li>
</ul>
 
<p>Нам очень важно, чтобы каждое мероприятие проходило на высоком уровне и было полезным для вас, поэтому мы уделяем большое внимание качеству во всем, что делаем.</p>
 
<p>За эти 6 лет мы приобрели много хороших друзей среди наших участников. Нам очень радостно, когда вы возвращаетесь повторно и невероятно приятно, когда вы рекомендуете нас своим коллегам и друзьям. Чтобы  выразить нашу благодарность вам, мы предоставляем 20% скидку всем, кто повторно приходит на наши конференции, и придумали <a href="https://frameworksdays.com/referral">программу для друзей</a>.</p>
 
<p>У нас в планах еще <a href="https://frameworksdays.com/events">много интересного</a>. Приходите :)</p>
<center><img src="https://scontent-amt2-1.xx.fbcdn.net/v/t1.0-9/13051526_875933505849851_6993432151530290286_n.jpg?oh=99f5bdaf886331d9a4b502737925f157&oe=57A53B44" alt="" width="700" /></center>

<h3>Смотрите, как проходят наши конференции:</h3> 
<p>Больше видео на нашем канале в <a href="https://www.youtube.com/user/fwdays/videos">YouTube</a>.</p>

<center><iframe width="560" height="315" src="https://www.youtube.com/embed/fZZbCXutR6k" frameborder="0" allowfullscreen></iframe></center><br />');

        $manager->persist($page);
        $manager->flush();
    }
}
