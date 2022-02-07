<?php // needed $startTime:float ?>
    </div>
</div>
<footer>
    <p>Сайт Вячеслава Бельского &copy; <?= "2021-" . date("Y", time()) ?><br> Время загрузки страницы: <?= round(microtime(true) - $startTime, 4) ?> с.</p>
</footer>
</body>
</html>