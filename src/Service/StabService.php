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

    private bool $flush = false;

    private int $isBanned = 0;

    private array $rights = ['ROLE_USER'];

    private string $password = '1111';

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
        12 => 'Георгий',
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
        12 => 'Георгиевский',
    ];

    private array $titlesFirstPart = [
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

    private array $titlesSecondPart = [
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

    // @codingStandardsIgnoreStart
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
    // @codingStandardsIgnoreStop

    public function __construct(
        UserService $userService,
        PostService $postService,
        CommentService $commentService,
        EntityManagerInterface $entityManager
    ) {
        $this->userService    = $userService;
        $this->postService    = $postService;
        $this->commentService = $commentService;
        $this->entityManager  = $entityManager;
    }

    /**
     * @return this Returns a configured StabService
     */
    public function setConfiguration(
        bool   $flush               = false,
        int    $isBanned            = 0,
        array  $rights              = ['ROLE_USER'],
        string $password            = '1111',
        bool   $approve             = true,
        int    $maxNumberOfComments = 10,
        bool   $commentsWithLike    = true
    ) {
        $this->flush               = $flush;
        $this->isBanned            = $isBanned;
        $this->rights              = $rights;
        $this->password            = $password;
        $this->approve             = $approve;
        $this->maxNumberOfComments = $maxNumberOfComments;
        $this->commentsWithLike    = $commentsWithLike;

        return $this;
    }

    /**
     * Returns an object of User
     */
    public function createRandomUser(int $password, array $rights, int $isBanned = 0, bool $flush = true): User
    {
        $userEmail = $this->getRandomValue('userEmail');
        $userFio   = $this->getRandomValue('userFio');
        $time      = $this->getRandomValue('time');

        return $this
            ->userService
            ->register($userEmail, $userFio, $password, $rights, $time, $isBanned, $flush);
    }

    /**
     * Returns an object of Post
     */
    public function createRandomPost(User $user, bool $approve = false, bool $flush = true): Post
    {
        $postTitle   = $this->getRandomValue('postTitle');
        $postContent = $this->getRandomValue('postContent');
        $time        = $this->getRandomValue('time');

        return $this
            ->postService
            ->create($user, $postTitle, $postContent, $approve, $time, $flush);
    }

    /**
     * Returns true if rating added, false if not
     */
    public function createRandomRating(User $user, Post $post, bool $flush = true): bool 
    {
        if (! $post->getApprove()) {
            return false;
        }
        
        $rating = $this->getRandomValue('postRating');

        return $this
            ->postService
            ->addRating($user, $post, $rating, $flush);
    }

    /**
     * Returns an object of Comment
     */
    public function createRandomComment(
        User $user,
        Post $post,
        bool $approve  = false,
        bool $withLike = false,
        bool $flush    = true
    ): Comment {
        if (! $post->getApprove()) {
            return false;
        }

        $commentContent = $this->getRandomValue('commentContent');
        $commentRating  = $this->getRandomValue('commentRating');
        $commentTime    = $this->getRandomValue('commentTime');
        $comment        = $this
            ->commentService
            ->create(
                $user, 
                $post, 
                $commentContent, 
                $approve, 
                $commentRating, 
                $commentTime, 
                $flush
            );

        if ($withLike && $approve) {
            $this
                ->commentService
                ->addLike($user, $comment, $flush);
        }

        return $comment;
    }

    /**
     * @throws Exception
     */
    public function toStabDb(int $numberOfIterations): void
    {
        if ($numberOfIterations < 0) {
            throw new \Exception(sprintf('Invalid number of iterations = %s', $numberOfIterations));
        }

        for ($i = 0; $i < $numberOfIterations; $i++) {
            /* For test, recommend to delete */
            $this->isBanned = (bool) mt_rand(0, 1);
            $this->approve  = (bool) mt_rand(0, 1);
            $user           = $this->createRandomUser($this->password, $this->rights, $this->isBanned, $this->flush);

            if (! $user) {
                throw new \Exception(sprintf('User with id = %s not created', $i));
            }

            if($this->isBanned) {
                continue;
            }

            $post = $this->createRandomPost($user, $this->approve, $this->flush);

            if (! $post) {
                throw new \Exception(sprintf('Post by user with id = %s not created', $i));
            }

            if (! $this->approve) {
                continue;
            }

            if (! $this->createRandomRating($user, $post, $this->flush)) {
                throw new \Exception(sprintf('Rating to post by user with id = %s not created', $i));
            }

            $numberOfComments = $this->maxNumberOfComments - mt_rand(0, $this->maxNumberOfComments);

            for ($m = 0; $m < $numberOfComments; $m++) {
                /* For test, recommend to delete */
                $this->approve = (bool) mt_rand(0, 1);
                $comment       = $this->createRandomComment(
                    $user,
                    $post,
                    $this->approve,
                    $this->commentsWithLike,
                    $this->flush
                );

                if (! $comment) {
                    throw new \Exception(sprintf('Comment to post by user with id = %s not created', $i));
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @return int|string|array Returns an info for creating random entities
     */
    private function getRandomValue(string $key)
    {
        $randomInt1           = mt_rand(0, 12);
        $randomInt2           = mt_rand(0, 12);
        $randomInt3           = mt_rand(0, 12);
        $randomInt4           = mt_rand(0, 12);
        $randomInt5           = mt_rand(0, 12);
        $randomPostRating     = mt_rand(1, 5);
        $randomCommentRating  = mt_rand(0, 1000);
        $randomTime           = time() - mt_rand(1000000, 2000000);
        $randomCommentTime    = mt_rand(time() - 1000000, time());
        $randomUserEmail      = uniqid('user') . '@blog.symfony';
        $randomUserFio        = $this->names[$randomInt1] . ' ' . $this->surnames[$randomInt2];
        $randomPostTitle      = $this->titlesFirstPart[$randomInt3] . ' ' . $this->titlesSecondPart[$randomInt4];
        $randomCommentContent = $this->texts[$randomInt1];
        // @codingStandardsIgnoreStart
        $randomPostContent    = $this->texts[$randomInt5] . ' 
            
            ' . $this->texts[$randomInt2]. '
            
            ' . $this->texts[$randomInt3]
        ;
        // @codingStandardsIgnoreStop

        switch ($key) {
            case 'time'           : return $randomTime;
            case 'userFio'        : return $randomUserFio;
            case 'userEmail'      : return $randomUserEmail;
            case 'postTitle'      : return $randomPostTitle;
            case 'postContent'    : return $randomPostContent;
            case 'postRating'     : return $randomPostRating;
            case 'commentContent' : return $randomCommentContent;
            case 'commentTime'    : return $randomCommentTime;
            case 'commentRating'  : return $randomCommentRating;
            default               : return $randomInt1;
        }
    }
}