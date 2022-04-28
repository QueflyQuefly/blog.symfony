'use strict';

let request = new XMLHttpRequest();
let response;
let urlForLastPosts = '/api/post/last/';
let urlForMoreTalkedPosts = '/api/post/talked/';
let output = '';
let outputLastPosts = document.querySelector('.lastPosts');
let outputMoreTalkedPosts = document.querySelector('.moreTalkedPosts');

function formatDate(timestampInSeconds) {
    let diffInSeconds = (new Date() - timestampInSeconds) / 1000; 
  
    if (diffInSeconds < 3) { 
        return 'Секунду назад';
    }

    if (diffInSeconds < 60) {
        return diffInSeconds + ' сек. назад';
    }

    if (diffInSeconds < 3600) {
        let min = Math.floor(diff / 60);

        return min + ' мин. назад';
    }

    let dateOfTimestamp = new Date(timestampInSeconds);
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
    for (let post of posts) {
        output += `
            <div class='viewpost'>
                <a class='postLink' href='/post/${post.id}'>
                    <div class='posttext'>
                        <p class='posttitle'>${post.title}</p>
                        <p class='postcontent'>${post.content}</p>
                        <p class='postdate'>${formatDate(post.dateTime * 1000)} &copy; ${post.user.fio}</p>
                        <p class='postrating'>Рейтинг: ${post.rating}</p>
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
    }

    return output;
}

/* request.open('GET', url);
request.responseType = 'text';

request.onload = function() {
    response = JSON.parse(request.response);
    for (let post of response) {
        output.textContent += post.title + '  \n  ';
    }
};

request.send(); */

function getLastPosts(numberOfPosts = 10) {
    fetch(urlForLastPosts + numberOfPosts).then(
        (response) => {
            response.text().then(
                (text) => {
                    response = JSON.parse(text);
                    outputLastPosts.innerHTML = convertPostsToString(response);
                }
            );
        }
    );
}

function getMoreTalkedPosts(numberOfPosts = 3) {
    fetch(urlForMoreTalkedPosts + numberOfPosts).then(
        (response) => {
            response.text().then(
                (text) => {
                    response = JSON.parse(text);
                    outputMoreTalkedPosts.innerHTML = convertPostsToString(response);
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

//getLastPosts = cachingDecorator(getLastPosts);

//getLastPosts = updateWithTimeout(getLastPosts, 10);
getLastPosts(10);
getMoreTalkedPosts(3);