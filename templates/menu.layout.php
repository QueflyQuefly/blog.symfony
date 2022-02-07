<?php // needed $sessionUserId:int, $isSuperuser:bool?>
        <nav>
            <div id="logo">
                <a class="logo" title="На главную" href='/'>
                    <img id='imglogo' src='/images/logo.jpg' alt='Лого'>
                    <div id='namelogo'>Просто Блог</div>
                </a>
            </div>
            <div id="menu">
                <ul class='menuList'>
                    <?php
                        if (empty($sessionUserId)) {
                            echo "<li><a class='menuLink' href='/login'>Войти</a></li>\n";
                        } else {
                            echo "<li><input type='submit' form='exit' value='Выйти' class='menuLink'>
                            <form id='exit' action='' method='post'>
                                <input type='hidden' value='' name='exit'>
                            </form></li>\n";
                            if (!empty($isSuperuser)) {
                                echo "\t\t\t\t\t\t<li><a class='menuLink' href='/admin'>Админка</a></li>\n";
                            }
                        }
                    ?>
                    <li><a class='menuLink' href='/cabinet'>Мой профиль</a></li>
                    <li><a class='menuLink' href='/search'>Поиск</a></li>
                    <li><a class='menuLink' href='/addpost'>Создать новый пост</a></li>
                </ul>
            </div>
        </nav>
