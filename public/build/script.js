'use strict';

let urlForLastPosts       = '/api/post/last/';
let urlForMoreTalkedPosts = '/api/post/talked/';
let outputLastPosts       = document.getElementById('lastPosts');
let outputMoreTalkedPosts = document.getElementById('moreTalkedPosts');

function formatDate(timestamp) {
    let diffInSeconds = Math.floor((new Date() - timestamp) / 1000);

    if (diffInSeconds < 60) {
        return diffInSeconds + ' сек. назад';
    }

    if (diffInSeconds < 3600) {
        let min = Math.floor(diff / 60);

        return min + ' мин. назад';
    }

    let dateOfTimestamp     = new Date(timestamp);
    let intermediateArchive = [
      '0' + dateOfTimestamp.getDate(),
      '0' + (dateOfTimestamp.getMonth() + 1),
      ''  + dateOfTimestamp.getFullYear(),
      '0' + dateOfTimestamp.getHours(),
      '0' + dateOfTimestamp.getMinutes()
    ].map(component => component.slice(-2));

    return intermediateArchive.slice(0, 3).join('.') + ' в ' + intermediateArchive.slice(3).join(':');
}

function convertPostsToString(posts, classOfPost) {
    let stringHtmlOfPosts = '';

    for (let post of posts) {
        stringHtmlOfPosts += `
            <div class='${classOfPost}'>
                <a class='postLink' href='/post/${post.id}'>
                    <div class='posttext'>
                        <p class='posttitle'>${post.title}</p>
                        <p class='postcontent'>${post.content}</p>
                        <p class='postdate'>${formatDate(post.date_time * 1000)} &copy; ${post.user_fio}</p>
                        <p class='postrating'>
                            Рейтинг: ${post.rating}, оценок: ${ post.count_ratings }, комментариев: ${ post.count_comments }
                        </p>
                        <div class='submitunder'>
                            <a href='/post/delete/${post.id}' class='link'>
                                Удалить пост №${post.id}
                            </a>
                        </div>
                    </div>
                    <div class='postimage'>
                        <img src='/images/PostImgId${post.id}.jpg' alt='Картинка'>
                    </div>
                </a>
            </div>
        `;
        classOfPost = 'viewpost';
    }

    return stringHtmlOfPosts;
}

/* function getPosts(url, amount) {
    let request = new XMLHttpRequest();
    let response;

    request.open('GET', url + amount);
    request.responseType = 'text';

    let key = hash(url, amount);
    
    request.onload = function() {
        let response      = JSON.parse(request.response);
        let uncachedValue = convertPostsToString(response);

        localStorage.setItem(key, uncachedValue);
    };
    
    request.send();
} */

function getPosts(url, amount, output, classOfPost) {
    let key = 'getPosts' + hash(url, amount);

    if (localStorage.getItem(key) != null) {
        output.innerHTML = localStorage.getItem(key);
        /* alert( localStorage.getItem(key)); */
    } else {
        fetch(url + amount).then(
            (response) => {
                response.text().then(
                    (text) => {
                        response = JSON.parse(text);
                        output.innerHTML = convertPostsToString(response, classOfPost);
                        localStorage.setItem(key, convertPostsToString(response, classOfPost));
                    }
                );
            }
        )
    }
}

function hash() {
    return [].join.call(arguments);
}

function cachingDecorator(someFunction) {

    return function () {
        let key = someFunction.name + hash(...arguments);

        if (localStorage.getItem(key) !== null) {
            return localStorage.getItem(key);
        }

        let uncachedValue = someFunction.apply(this, arguments);

        localStorage.setItem(key, uncachedValue);

        return uncachedValue;
    };
}

function updateWithTimeout(someFunction, timeoutInSeconds) {
    return function updatedWithInterval() {
        let someValue = someFunction.apply(this, arguments);

        setTimeout(updatedWithInterval, timeoutInSeconds * 1000);

        return someValue;
    };
}

// getPosts = updateWithTimeout(getPosts, 10);

getPosts(urlForLastPosts, 10, outputLastPosts, 'generalpost');

getPosts(urlForMoreTalkedPosts, 3, outputMoreTalkedPosts, 'viewpost');


/* for (let i = 0; i < localStorage.length; i++) {
    let key = localStorage.key(i);
    alert(`${key}: ${localStorage.getItem(key)}`);
} */

/* localStorage.clear(); */

