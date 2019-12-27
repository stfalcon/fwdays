<?php

namespace App\DataFixtures\ORM;

use App\Entity\Page;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadPagesData.
 */
class LoadPagesData extends AbstractFixture
{
    private const PAGE_DATA = [
        [
            'title' => 'О Frameworks Days',
            'slug' => 'about',
            'text' => <<<EOF
<p><h3>Наша цель:</h3>
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

<center><iframe width="560" height="315" src="https://www.youtube.com/embed/fZZbCXutR6k" frameborder="0" allowfullscreen></iframe></center><br />
EOF,
        ],
        [
            'title' => 'Контактна інформація',
            'slug' => 'contacts',
            'text' => <<<EOF
<h2 class="h2 contacts__title">Контактна інформація</h2>
            <div class="contact-info">
                <div class="contact-info__item">
                    <div class="contact-info__label">Електронна пошта</div>
                    <a href="mailto:orgs@fwdays.com" class="contact-info__link contact-info__link--blue-light">orgs@fwdays.com</a>
                </div>
                <div class="contact-info__item">
                    <div class="contact-info__label">Адреса</div>
                    <a href="https://maps.google.com/?q=Київ,вул.Виборзька,42а" target="_blank" class="contact-info__link">Київ, вул. Виборзька, 42а</a>
                </div>
                <div class="contact-info__item">
                    <div class="contact-info__label">Facebook</div>
                    <a href="https://www.facebook.com/fwdays/" target="_blank"
                       class="contact-info__link contact-info__link--blue-dark">fwdays</a>
                </div>
            </div>
             <div class="organizers">
            <div class="organizers__label">Організатори:</div>
            <ul class="organizers__items">
                <li class="organizer-card organizers__item">
                    <div class="organizer-card__caption">Питання з безготівкової оплати</div>
                    <img class="organizer-card__photo" src="https://storage.fwdays.com/uploads/images/5c9e3599bfe0c.jpeg" width="160" height="160">
                    <div class="organizer-card__name">Тетяна Буханова</div>
                    <a class="organizer-card__tel" href="tel:tel:+380992159622">+380 99 21-596-22</a>
                    <a class="organizer-card__mail" href="mailto:tanyabukhanova@fwdays.com">tanyabukhanova@fwdays.com </a>
                </li>
                <li class="organizer-card organizers__item">
                    <div class="organizer-card__caption">Питання співпраці та партнерства з Fwdays</div>
                   <img class="organizer-card__photo" src="https://fwdays.com/assets/img/organizers/bojik.png" width="160" height="160" alt=""/>
                    <div class="organizer-card__name">Ірина Божик</div>
                    <a class="organizer-card__tel" href="tel:380679995888">+380 67 999-5-888</a>
                    <a class="organizer-card__mail" href="mailto:iryna.bozhyk@fwdays.com">iryna.bozhyk@fwdays.com</a>
                </li>
                <li class="organizer-card organizers__item">
                    <div class="organizer-card__caption">Питання інфопартнерства, мітапів, майстер-класів</div>
                    <img class="organizer-card__photo" src="https://storage.fwdays.com/uploads/images/5a82b54782e06.jpeg" alt="" width="160" height="160">
                    <div class="organizer-card__name">Ксенія Грабевник</div>
                    <a class="organizer-card__tel" href="tel:380985265606">+380 63 208-03-22</a>
                    <a class="organizer-card__mail" href="mailto:ksenya.grabevnyk@fwdays.com">ksenya.grabevnyk@fwdays.com </a>
                </li>
               <li class="organizer-card organizers__item">
                    <div class="organizer-card__caption">Питання щодо інформації на сайті та майстер-класів</div>
                    <img class="organizer-card__photo" src="https://storage.fwdays.com/uploads/images/5c9e33c11fb6e.jpeg" width="160" height="160" alt=""/>
                    <div class="organizer-card__name">Яна Борисова</div>
                    <a class="organizer-card__tel" href="tel:380953299947">+380 95 329-99-47</a>
                    <a class="organizer-card__mail" href="mailto:yana.borysova@fwdays.com">yana.borysova@fwdays.com</a>
                </li>                 
            </ul>
        </div>
EOF,
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::PAGE_DATA as $pageData) {
            $page = (new Page())
                ->setTitle($pageData['title'])
                ->setSlug($pageData['slug'])
                ->setText($pageData['text'])
            ;
            $manager->persist($page);
        }
        $manager->flush();
    }
}
