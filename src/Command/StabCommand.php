<?php

namespace App\Command;

use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class StabCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:stab';
    protected static $defaultDescription = 'This command is Stab for database.';
    private int $numberOfIterations = 0;
    private $errors = [];
    private float $startTime;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;    
    private EntityManagerInterface $entityManager;
    private $names = [
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
    private $surnames = [
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
    private $titles1 = [
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
    private $titles2 = [
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
    private $texts = [
        0  => 'Путешествие - это как попадание в сказку, где всё необычно и не реально. Я люблю путешествовать, узнавать другие страны и города. Залог хорошего путешествия, это грамотная подготовка. Когда я куда-нибудь приезжаю, то стараюсь посмотреть все местные достопримечательности или просто красивые места. Всё это я подготавливаю заранее. Надо знать, что смотреть в первую очередь, как добраться до них, когда они открыты и т.д. Если подготовиться хорошо, то посмотреть и узнать можно гораздо больше и дешевле.',
        1  => 'Путешествовать должны все люди. Без путешествий жизнь становится скучной и серой. Я не понимаю тех людей, кто не хочет и не любит смотреть мир. Я ещё мало где был, но уверен, что успею за свою жизнь посмотреть много красивых стран и городов. Больше всего мне нравится путешествовать на автомобиле. Мы семьёй съездили уже в Крым, Великий Новгород, Псков, Карелию и Ярославль. Сейчас мы собираемся на Онежское озеро. #Россия',
        2  => 'Что может быть лучше путешествия? Я даже не могу себе представить такого. Я очень люблю путешествовать. Без разницы куда ездить, главное познавать не виденные ранее места. Когда я путешествую, то получаю потрясающие эмоции, заряжаюсь энергией, а также борюсь со скукой и рутиной. Кроме этого, путешествия позволяют мне развивать кругозор, узнавать много чего нового. #2022',
        3  => 'В путешествиях я знакомлюсь с новой культурой, обычаями и образом жизни проживающих там людей. Например, я вижу, что в Париже местные жители могут часами сидеть в кафе и пить маленькую чашку кофе, во Вьетнаме все ездят на мотобайках, а в Китае по вечерам много людей выходит в парки, где поют и танцуют. Все эти особенности их жизни очень интересно наблюдать. #людиТакиеРазные',
        4  => 'Также мне нравятся случайные знакомства с людьми со всего мира. В Турции я познакомилась с немцами из Бремена, в Египте с девушкой из Польши, а в Голландии с бабушкой из Канады. Со всеми из них я приятно и интересно провела время, узнала много об их жизни и путешествиях, улучшила свой английский язык. Я до сих пор переписываюсь со всеми этими знакомыми, и мы мечтаем когда-нибудь встретиться в их стране или у меня в России. #до-встречи-друзья',
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
        $this->startTime = microtime(true);
        parent::__construct();
    }

    protected function configure(): void
    {
        // the command help shown when running the command with the "--help" option
        $this->setHelp('This command allows you to create user, post with comments and many other by one touch.');
        $this->addArgument('number', InputArgument::OPTIONAL, 'It is a number of loop iterations', $this->numberOfIterations);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }
        if ((int)$input->getArgument('number') > 0) {
            $this->numberOfIterations = $input->getArgument('number');
        } else {
            $output->writeln([
                'Cannot run a cycle with number of iterations = ' . $input->getArgument('number'),
                'Please enter a valid argument (integer > 0) after "app:stab"'
            ]);
            return Command::INVALID;
        }
        $section1 = $output->section();
        $section2 = $output->section();
        $section3 = $output->section();
        $section4 = $output->section();
        try {
            $flush = false;
            $output->writeln('Number of cycle iterations = ' . $this->numberOfIterations . '. Transaction started.');
            $min = $this->userService->getLastUserId() + 1;
            $counter = 0;
            $counterComments = 0;
            for ($i = $min; $i < $this->numberOfIterations + $min; $i++) {
                $random1 = mt_rand(0, 12);
                $random2 = mt_rand(0, 12);
                $random3 = mt_rand(0, 12);
                $random4 = mt_rand(1, 5);
                $date = time() - mt_rand(100000, 2628000);

                $email = $i . '@' . $i . '.' . $i;
                $fio = $this->names[$random2] . ' ' . $this->surnames[$random3];
                $password = $i;
                $rights = ['ROLE_USER'];
                $user = $this->userService->registerWithoutVerification($email, $fio, $password, $rights, $date, $flush);
                if (!$user) {
                    $this->errors[] = 'User with email = ' . $email . ' not created';
                    continue;
                }
                $section1->overwrite('Created user with email - ' . $email);
                $userId = $user->getId();
                /* Here I add post with info and tags */
                $title = $this->titles1[$random1] . ' ' . $this->titles2[$random2];
                $content = $this->texts[$random3] . ' 
                    ' . $this->texts[$random2]. ' 
                    ' . $this->texts[$random1]
                ;
                $post = $this->postService->create($user, $title, $content, $date, $flush);
                if (!$post) {
                    $this->errors[] = 'Post by user with id = ' . $userId . ' not created';
                    continue;
                }
                $section2->overwrite('Created post by user with id = ' . $userId);
                if (!$this->postService->addRating($user, $post, $random4, $flush)) {
                    $this->errors[] = 'Rating ' . $random4 . ' to post №' . $post->getId() . 
                        ' by user with id = ' . $userId . ' not created';
                    continue;
                }
                $section3->overwrite('Created rating ' . $random4 . ' to post №' . $post->getId() . ' by user with id = ' . $userId);
                $counter++;
                /* Here I add ratings and comments with likes to post */
                for ($m = 0; $m <= $random3; $m++) {
                    // $random4 = mt_rand(0, $numberOfIterations - 1);
                    // $randomUser = $random4.'@gmail.com';
                    // $randomUser = $this->userService->getUserIdByEmail($randomUser);
                    // if (is_null($randomUser)) {
                    //     continue;
                    // }
                    // $this->postService->addRating($userId, $postId, $random5);
                    $random6 = mt_rand(0, 12);
                    $dateOfComment = mt_rand($date, time());
                    $commentContent = $this->texts[$random6];
                    $randomLike = mt_rand(0, 1000);
                    $comment = $this->commentService->create($user, $post, $commentContent, $randomLike, $dateOfComment, $flush);
                    $commentId = $comment->getId();
                    if (!$commentId) {
                        $this->errors[] = 'Comment to post №' . $post->getId() . ' by user with id = ' . $userId . ' not created';
                        continue;
                    }
                    $this->commentService->like($user, $comment, $flush);
                    $counterComments++;
                }
                $section4->overwrite(
                    'Created ' . $random3 + 1 . ' comments with likes to post №' . 
                    $post->getId() . ' by user with id = ' . $userId
                );
            }
            $this->entityManager->flush();
            if ($this->errors) {
                $output->writeln($this->errors);
            }
            if ($this->numberOfIterations > 1) {
                $section1->overwrite('Created ' . $counter . ' users.');
                $section2->overwrite('Created ' . $counter . ' posts by these users.');
                $section3->overwrite('Created ' . $counter . ' ratings by these users to these posts.');
                $section4->overwrite('Created ' . $counterComments . ' comments by these users to these posts with likes.');
            }
            $output->writeln(
                'Cycle with number of iretations = ' . $this->numberOfIterations . ' completed in ' . 
                round(microtime(true) - $this->startTime, 4) . ' seconds'
            );
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $output->writeln($this->errors);

            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}