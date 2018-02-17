var readyPromises = [];

var applicationServerKey;

var files = [
    'app/views/misc.html',
    'app/views/member.html',
    'app/views/item.html',
    'app/views/feed.html',
    'app/views/category.html',
    'app/views/author.html',
];

var timezone = new Date();
timezone = -timezone.getTimezoneOffset() / 60;

var lastHistory = false;

var language = navigator.languages ? navigator.languages[0] : (navigator.language || navigator.userLanguage);
if(language) {
    language = language.substr(0, 2);
}

var hostName = window.location.hostname;
var apiUrl = '//' + hostName;
if(window.location.port) {
    apiUrl += ':' + window.location.port;
}
apiUrl += window.location.pathname;

apiUrl = apiUrl.replace('index.html', '');
if(hostName.indexOf('local') !== -1) {
    apiUrl = apiUrl.replace('client/', 'app_dev.php/api');
} else {
    apiUrl = apiUrl.replace('client/', 'api');
}

if('serviceWorker' in navigator && window.location.protocol === 'https:') {
    navigator.serviceWorker.register('serviceworker.js').then(function() {
    }).catch(function() {
    });
}

var connectionData = explainConnection(JSON.parse(localStorage.getItem('connection')));

var snackbarContainer = document.querySelector('.mdl-snackbar');

var languages = ['en', 'fr'];
var languageFinal = 'en';
if(languages.indexOf(language)) {
    languageFinal = language;
}

if(languageFinal !== 'en') {
    readyPromises.push(getMomentLocale(languageFinal));
}

readyPromises.push(getTranslation(languageFinal));

for(var i in files) {
    if(files.hasOwnProperty(i)) {
        readyPromises.push(loadFile(files[i]));
    }
}

if(document.attachEvent ? document.readyState === 'complete' : document.readyState !== 'loading') {
    ready();
} else {
    document.addEventListener('DOMContentLoaded', ready);
}
