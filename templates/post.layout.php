
<?php // needed $class:string, $post:array, $linkToDelete:string ?>
<div class='<?= $class ?>'>
    <a class='postLink' href='/viewpost/<?= $post['post_id'] ?>'>
        <div class='posttext'>
            <p class='posttitle'><?= $post['title'] ?></p>
            <p class='postcontent'><?= $post['content'] ?></p>
            <p class='postdate'><?= $post['date_time']. " &copy; " . $post['author'] ?></p>
            <p class='postrating'><?= $post['rating'] ?></p>
        </div>
        <div class='postimage'>
            <img src='/images/PostImgId<?= $post['post_id'] ?>.jpg' alt='Картинка'>
        </div>
    </a>
    <div class='submitunder'>
        <?= $linkToDelete ?>
    </div>
</div>
