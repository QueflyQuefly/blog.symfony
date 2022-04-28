'use strict';

let urlForLastPosts       = '/api/post/last/';
let urlForMoreTalkedPosts = '/api/post/talked/';
let outputLastPosts       = document.querySelector('.lastPosts');
let outputMoreTalkedPosts = document.querySelector('.moreTalkedPosts');

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

function convertPostsToString(posts) {
    let stringHtmlOfPosts = '';
    let classOfPost = 'generalpost';

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

/* 
let request = new XMLHttpRequest();
let response;

request.open('GET', url);
request.responseType = 'text';

request.onload = function() {
    response = JSON.parse(request.response);
    for (let post of response) {
        output.textContent += post.title + '  \n  ';
    }
};

request.send();
*/

function getPosts(url, amount, output) {
    fetch(url + amount).then(
        (response) => {
            response.text().then(
                (text) => {
                    response = JSON.parse(text);
                    output.innerHTML = convertPostsToString(response);
                }
            );
        }
    );
}

function cachingDecorator(someFunction) {
    let cacheMap = new Map();

    function hash(...args) {
        return args.toString();
    }

    return function () {
        let key = hash(arguments);

        if (cacheMap.has(key)) {
            return cacheMap.get(key);
        }

        let uncachedValue = someFunction.apply(this, arguments);
        cacheMap.set(key, uncachedValue);

        return uncachedValue;
    };
}

function updateWithTimeout(someFunction, timeoutInSeconds) {
    return function updatedWithInterval() {
        let someValue = someFunction.apply(arguments);

        setTimeout(updatedWithInterval, timeoutInSeconds * 1000);

        return someValue;
    };
}

// getLastPosts = cachingDecorator(getLastPosts);

// getLastPosts = updateWithTimeout(getLastPosts, 10);

getPosts(urlForLastPosts, 10, outputLastPosts);
getPosts(urlForMoreTalkedPosts, 3, outputMoreTalkedPosts);