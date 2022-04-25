'use strict';

let request = new XMLHttpRequest();
let response;
let url = '/api/post/';
let output = document.querySelector('p');

/* request.open('GET', url);
request.responseType = 'text';

request.onload = function() {
    response = JSON.parse(request.response);
    for (let object of response) {
        output.textContent += object.title + '  \n  ';
    }
};

request.send(); */


fetch(url).then(function(response) {
    response.text().then(function(text) {
        response = JSON.parse(text);
        
        for (let object of response) {
            output.textContent += object.title + ' ..............  ';
        }
    });
});