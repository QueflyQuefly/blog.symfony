<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;

class StabService
{
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;
    private EntityManagerInterface $entityManager;
    private array $errors = [];
    private bool $flush = false;
    private bool $approve = true;
    private bool $checkingForUser = false;
    private int $maxNumberOfComments = 10;
    private bool $commentsWithLike = true;
    private array $names = [
        0  => 'Василий',
        1  => 'Даниил',
        2  => 'Иван',
        3  => 'Павел',
        4  => 'Александр',
        5  => 'Алексей',
        6  => 'Давид', 
        7  => 'Фёдор',
        8  => 'Анатолий',
        9  => 'Вячеслав',
        10 => 'Кирилл',
        11 => 'Григорий',
        12 => 'Георгий'
    ];
    private array $surnames = [
        0  => 'Бродский',
        1  => 'Васильев',
        2  => 'Пугачев',
        3  => 'Иванюк',
        4  => 'Житомирский',
        5  => 'Данилов',
        6  => 'Крупской', 
        7  => 'Павлов',
        8  => 'Анатольев',
        9  => 'Вертеловский',
        10 => 'Кириллов',
        11 => 'Григорьев',
        12 => 'Георгиевский'
    ];
    private array $titles1 = [
        0  => 'Пушкиногорье -',
        1  => 'Полуостров Крым -',
        2  => 'Ночная Россия -',
        3  => 'Пизанская башня -',
        4  => 'Италия и Швейцария -',
        5  => 'Одни впечатления: США -',
        6  => 'Беларусь -',
        7  => 'Я просто обожаю Испанию, ведь Испания -',
        8  => 'Сербия и Черногория -',
        9  => 'Есть и плюсы, и минусы. Франция -',
        10 => 'Мне запомнилась эта страна, ведь Канада -',
        11 => 'Солнечный Белиз -',
        12 => 'Таиланд удивил -',
    ]; 
    private array $titles2 = [
        0  => 'это не только памятник историко-литературный',
        1  => 'это своеобразный ботанический и зоологический сад',
        2  => 'это замечательный памятник природы, хотя львов здесь нет',
        3  => 'хоть глаз выколи! Зачем я вообще туда поехал :(',
        4  => 'хоть бы что, даже посмотреть нечего...',
        5  => 'это было необычайное путешествие. Эмоции льются через край',
        6  => 'это приоритетный пункт назначения. Мой план амбициозен',
        7  => 'это место, где сбываются мечты. Можно ли сравнить с моей родиной?',
        8  => 'это место не только для отдыха, но и мой дом',
        9  => 'это лучшее место, где я когда-либо бывал',
        10 => 'это не только крупнейший заповедник',
        11 => 'это худшее путешествие. Впустую потраченные деньги',
        12 => 'это моя рекомендация. Место обязательно к посещению',
    ];  
    private array $texts = [
        0  => 'Путешествие - это как попадание в сказку, где всё необычно и нереально. Я люблю путешествовать, узнавать другие страны и города. Залог хорошего путешествия, это грамотная подготовка. Когда я куда-нибудь приезжаю, то стараюсь посмотреть все местные достопримечательности или просто красивые места. Всё это я подготавливаю заранее. Надо знать, что смотреть в первую очередь, как добраться до них, когда они открыты и т.д. Если подготовиться хорошо, то посмотреть и узнать можно гораздо больше и дешевле.',
        1  => 'Путешествовать должны все люди. Без путешествий жизнь становится скучной и серой. Я не понимаю тех людей, кто не хочет и не любит смотреть мир. Я ещё мало где был, но уверен, что успею за свою жизнь посмотреть много красивых стран и городов. Больше всего мне нравится путешествовать на автомобиле. Мы семьёй съездили уже в Крым, Великий Новгород, Псков, Карелию и Ярославль. Сейчас мы собираемся на Онежское озеро. #Россия',
        2  => 'Что может быть лучше путешествия? Я даже не могу себе представить такого. Я очень люблю путешествовать. Без разницы куда ездить, главное познавать не виденные ранее места. Когда я путешествую, то получаю потрясающие эмоции, заряжаюсь энергией, а также борюсь со скукой и рутиной. Кроме этого, путешествия позволяют мне развивать кругозор, узнавать много чего нового. #2022',
        3  => 'В путешествиях я знакомлюсь с новой культурой, обычаями и образом жизни проживающих там людей. Например, я вижу, что в Париже местные жители могут часами сидеть в кафе и пить маленькую чашку кофе, во Вьетнаме все ездят на мотобайках, а в Китае по вечерам много людей выходит в парки, где поют и танцуют. Все эти особенности их жизни очень интересно наблюдать. #людиТакиеРазные',
        4  => 'Также мне нравятся случайные знакомства с людьми со всего мира. В Турции я познакомилась с немцами из Бремена, в Египте с девушкой из Польши, а в Голландии с бабушкой из Канады. Со всеми из них я приятно и интересно провела время, узнала много об их жизни и путешествиях, улучшила свой английский язык. Я до сих пор переписываюсь со всеми этими знакомыми, и мы мечтаем когда-нибудь встретиться в их стране или у меня в России. #до_встречи_друзья',
        5  => 'Я побывала уже во многих странах мира, но ещё больше осталось мест, где я не была. Больше всего я очень хочу побывать в Австралии, Японии, США и Кении. В России я хочу посетить Байкал и Камчатку. В этом году я отправляюсь в загадочный Израиль, а также мы с родителями будем отдыхать на Кипре. С большим нетерпением я жду предстоящие путешествия и открытия в новых странах. #timeToTravel',
        6  => 'Я не часто куда-то путешествую, поэтому, когда родители объявили мне, что в июне мы поедем в Москву, я очень обрадовался. Я давно мечтал увидеть столицу нашей родины, посмотреть её главные достопримечательности. И вот на поезде мы прибыли на Ярославский вокзал, откуда на метро добрались до нашей гостиницы. Она находится на окраине города, но рядом со станцией метро, что позволяло нам быстро добираться до нужных мест. #autumn',
        7  => 'Первым делом, мы, конечно, отправились на Красную площадь. Посмотрели Кремль, красивейший собор Василия Блаженного, мавзолей Ленина, могилу неизвестного солдата, нулевой километр, захоронения известных людей и многое другое. Увидеть такие известные места в один день, это очень здорово, просто захватывает дух. Во второй день мы катались на кораблике по Москве-реке, гуляли по парку Зарядье, посмотрели храм Христа Спасителя, съездили посмотреть район Москва-сити. На третий день у нас была куплена экскурсия в музей-заповедник Царицыно. Там мне также очень понравилось, правда, сил ходить уже не было, а там такая огромная территория. На следующий день мы ходили в музей изобразительных искусств имени Пушкина. Я не очень хотел его посещать, но моя мама большая любительница живописи и пропустить такой музей она не могла. #музей',
        8  => 'Но на автомобиле сложно или практически невозможно посмотреть дальние страны. Тут без самолёта не обойтись. Когда я вырасту, я хочу совершить кругосветное путешествие, используя различные виды транспорта. Это моя самая большая #мечта.',
        9  => 'Путешествие одно из самых любимых занятий большинства людей. А многие так любят путешествовать... Все просто, когда человек путешествует, он познает окружающий мир и самого себя. На земле очень много необычных уголков, красивых мест, которые заставляют пережить потрясающие эмоции, чувства. #потрясающе',
        10 => 'Во время путешествия наполняешься энергией, силой, положительными эмоциями. Начинаешь ощущать гармонию и тесную связь человека с природой. Удивительные страны, красивые пейзажи всегда манили романтиков. Многие писатели, музыканты, художники создавали произведения искусства после путешествий, которое наполняли их новыми ощущениями, меняли их взгляды на жизнь. #новыеОщущения',
        11 => 'Когда человек начинает путешествовать, он меняется, ведь на него оказывают влияние новые страны, города, люди, природа. Мир становится более интересным и разнообразным, появляются новые друзья. #winter',
        12 => 'С давних времен люди не зная, что там дальше, отправлялись в путешествие, их манила неизведанность, тайна, любопытство. И это было достаточно опасно, но несмотря на это, открывались новые города, страны, моря, океаны, материки. Сейчас современный человек знает многое, но отправляясь в путешествие, он по-прежнему открывает перед собой удивительный и неповторимый мир. #spring',
    ];

    public function __construct(
        UserService $userService,
        PostService $postService,
        CommentService $commentService,
        EntityManagerInterface $entityManager
    ) {
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->entityManager = $entityManager;
    }

    public function setConfiguration(
        bool $flush = false,
        bool $approve = true,
        bool $checkingForUser = false,
        int $maxNumberOfComments = 10,
        bool $commentsWithLike = true
    ) {
        $this->flush = $flush;
        $this->approve = $approve;
        $this->checkingForUser = $checkingForUser;
        $this->maxNumberOfComments = $maxNumberOfComments;
        $this->commentsWithLike = $commentsWithLike;
    }

    /**
     * @return User Returns an object of User
     */
    private function createRandomUser(int $number, bool $flush = true)
    {
        $randomName = mt_rand(0, 12);
        $randomSurname = mt_rand(0, 12);
        $time = time() - mt_rand(1000000, 2000000);
        $email = $number . '@' . $number . '.' . $number;
        $fio = $this->names[$randomName] . ' ' . $this->surnames[$randomSurname];
        $password = $number;
        $rights = ['ROLE_USER'];

        return $this->userService->register($email, $fio, $password, $rights, $time, $flush);
    }

    /**
     * @return Post Returns an object of Post
     */
    private function createRandomPost(User $user, bool $approve = false, bool $flush = true)
    {
        $randomText1 = mt_rand(0, 12);
        $randomText2 = mt_rand(0, 12);
        $randomText3 = mt_rand(0, 12);
        $time = time() - mt_rand(100000, 1000000);
        $title = $this->titles1[$randomText1] . ' ' . $this->titles2[$randomText2];
        $content = $this->texts[$randomText3] . ' 
            
            ' . $this->texts[$randomText2]. '
            
            ' . $this->texts[$randomText1]
        ;

        return $this->postService->create($user, $title, $content, $approve, $time, $flush);
    }

    /**
     * @return bool Returns true if rating added, false if not
     */
    private function createRandomRating(
        User $user,
        Post $post,
        bool $checkingForUser = true,
        bool $flush = true
    ) {
        if ($post->getApprove()) {
            $randomRating = mt_rand(1, 5);

            return $this->postService->addRating($user, $post, $randomRating, $checkingForUser, $flush);
        }

        return false;
    }

    /**
     * @return Comment Returns an object of Comment
     */
    private function createRandomComment(
        User $user,
        Post $post,
        bool $approve = false,
        bool $withLike = false,
        bool $checkingForUser = true,
        bool $flush = true
    ) {
        if ($post->getApprove()) {
            $randomText = mt_rand(0, 12);
            $dateOfComment = time() - mt_rand(10000, 100000);
            $commentContent = $this->texts[$randomText];
            $randomLike = mt_rand(0, 1000);

            $comment = $this->commentService->create(
                $user, 
                $post, 
                $commentContent, 
                $approve, 
                $randomLike, 
                $dateOfComment, 
                $flush
            );
            if ($withLike && $approve) {
                $this->commentService->like($user, $comment, $checkingForUser, $flush);
            }

            return $comment;
        }

        return false;
    }

    /**
     * @return bool Returns false if there are errors
     */
    public function toStabDb(int $numberOfIterations)
    {
        if ($numberOfIterations > 0) {
            try {
                if (!$min = $this->userService->getLastUserId() + 1) {
                    throw new \Exception(sprintf('Invalid result of function getLastUserId() = %s', $min));
                }
                $this->approve = (bool) mt_rand(0, 1);
                for ($i = $min; $i < $numberOfIterations + $min; $i++) {

                    $user = $this->createRandomUser($i, $this->flush);
                    if (!$user) {
                        $this->errors[] = sprintf('User with id = %s not created', $i);
                        continue;
                    }

                    $post = $this->createRandomPost($user, $this->approve, $this->flush);
                    if (!$post) {
                        $this->errors[] = sprintf('Post by user with id = %s not created', $i);
                        continue;
                    }

                    if ($this->approve) {
                        if (!$this->createRandomRating($user, $post, $this->checkingForUser, $this->flush)) {
                            $this->errors[] = sprintf('Rating to post by user with id = %s not created', $i);
                            continue;
                        }

                        for ($m = 0; $m < $this->maxNumberOfComments - mt_rand(0, $this->maxNumberOfComments); $m++) {
                            $comment = $this->createRandomComment(
                                $user,
                                $post,
                                $this->approve,
                                $this->commentsWithLike,
                                $this->checkingForUser,
                                $this->flush
                            );
                            if (!$comment) {
                                $this->errors[] = sprintf('Comment to post by user with id = %s not created', $i);
                                break;
                            }
                        }
                    }
                }
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();

                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @return [] Returns an array of errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}